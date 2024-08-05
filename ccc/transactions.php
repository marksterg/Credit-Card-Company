<?php
/**
 * transactions.php
 * 
 * Handles all the procedures related to transactions (exept) payoff procedure which is not considered as transaction.
 */

include 'inc/header.php';
require 'db/config.php';
require 'inc/utils.php';

// open db connection
$db = (new DB())->get_db_connection();

$status = null;
try {
    // request process
    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        if (isset($_GET["buy"])) {
            $required_fields = array("select_type_client_to_buy", "client_buy", "seller_buy", "amount_to_transfer");

            if (!check_fields($required_fields)) {
                $status = array("error" => "empty_fields");
            } else {
                $client_type = $_POST["select_type_client_to_buy"];
                $client_id_buy = $_POST["client_buy"];
                $seller_id_buy = $_POST["seller_buy"];
                $amount = filter($_POST["amount_to_transfer"]);

                // insert into transactions the new purchase
                if ($amount > 0) {

                    // default NULL value for individual client
                    $empId = NULL;
                    if ($client_type == "company_employee") {
                        // the client that is selected is corporate client
                        $empId = $client_id_buy;
                        // find the company id for given employee
                        $temp = "SELECT c.client_accId FROM company_employees ce, clients c WHERE ce.client_accId = c.client_accId AND ce.empId = $empId;";
                        $result = $db->query($temp)->fetch_all(MYSQLI_BOTH);
                        // company id
                        $client_id_buy = $result[0]["client_accId"];
                    }

                    // CHECK IF CLIENT CAN BUY FROM THE SELLER WITH THIS AMOUNT
                    $check_amount = "SELECT c.client_accId FROM clients c WHERE c.client_accId = $client_id_buy AND c.credit_balance >= $amount;";
                    $rows = mysqli_num_rows($db->query($check_amount));

                    if ($rows) {
                        // decrease the available balance and increase debt from the client's account
                        $decrease_client = "UPDATE clients SET credit_balance = credit_balance - $amount, credit_debt = credit_debt + $amount WHERE client_accId = $client_id_buy;";
                        $db->query($decrease_client);

                        // update debt and total_profit for seller (based on commission)
                        $update_seller = "UPDATE sellers 
                                SET total_profit = total_profit + ($amount - commission*$amount), debt = debt + (commission*$amount) 
                                WHERE seller_accId = $seller_id_buy;";
                        $db->query($update_seller);

                        if (!isset($empId))
                            $empId = 0;

                        // enter names in transaction
                        $client_name = $db->query("SELECT name FROM ccc_users WHERE accId = $client_id_buy;")->fetch_all(MYSQLI_BOTH)[0]["name"];
                        $seller_name = $db->query("SELECT name FROM ccc_users WHERE accId = $seller_id_buy;")->fetch_all(MYSQLI_BOTH)[0]["name"];

                        // insert the new transaction in the transactions table
                        $insert_trans = "INSERT INTO transactions (amount, trans_type, client_name, seller_name, client_accId, seller_accId, empId)
                                VALUES ($amount, 'DEBIT', '$client_name', '$seller_name', $client_id_buy, $seller_id_buy, $empId)";
                        $final = $db->query($insert_trans);
                        $trans_last_id = $db->insert_id;

                        // saving SESSION data to show specific changes after the submission
                        $_SESSION["trans_rownum"] = get_rownum_from_result($db->query("SELECT TID AS 'id' FROM trans_data;"), $id = $trans_last_id);
                        $_SESSION["client_rownum"] = get_rownum_from_result($db->query("SELECT AccountID AS 'id' FROM trans_clients_info;"), $id = $client_id_buy);
                        $_SESSION["seller_rownum"] = get_rownum_from_result($db->query("SELECT AccountID AS 'id' FROM trans_sellers_info;"), $id = $seller_id_buy);

                        $status = "success";
                    } else {
                        $status = array("error" => "insufficient_amount");
                    }
                } else {
                    $status = array("error" => "zero_amount");
                }
            }
        } elseif (isset($_GET["refund"])) {
            $required_fields = array("transaction_refund");

            if (!check_fields($required_fields)) {
                $status = array("error" => "empty_fields");
            } else {
                $tid = $_POST["transaction_refund"];

                // get transaction id, amount
                $get_amount_sql = "SELECT TID, amount FROM transactions WHERE TID = $tid AND trans_type = 'DEBIT';";
                $TID = $db->query($get_amount_sql)->fetch_all(MYSQLI_BOTH)[0]["TID"];
                $trans_amount = $db->query($get_amount_sql)->fetch_all(MYSQLI_BOTH)[0]["amount"];

                // CHECK if the primary key (TID, trans_type) of transactions table already exists. If so do not insert.
                $check_primary = "SELECT * FROM transactions WHERE TID = $TID AND trans_type = 'CREDIT';";
                $rows = mysqli_num_rows($db->query($check_primary));

                if (!$rows) {
                    // --- GET INFO ---

                    // get client id who made the transaction
                    $check_client_sql = "SELECT client_accId, empId FROM transactions WHERE TID = $tid;";
                    $cid = $db->query($check_client_sql)->fetch_all(MYSQLI_BOTH)[0]["client_accId"];
                    $eid = $db->query($check_client_sql)->fetch_all(MYSQLI_BOTH)[0]["empId"];

                    // get seller id who participated in the transaction
                    $get_seller_sql = "SELECT seller_accId FROM transactions WHERE TID = $tid;";
                    $sid = $db->query($get_seller_sql)->fetch_all(MYSQLI_BOTH)[0]["seller_accId"];

                    // get credit balance and debt from client
                    $get_client_credit = "SELECT credit_limit, credit_balance, credit_debt FROM clients WHERE client_accId = $cid;";
                    $limit = $db->query($get_client_credit)->fetch_all(MYSQLI_BOTH)[0]["credit_limit"];
                    $balance = $db->query($get_client_credit)->fetch_all(MYSQLI_BOTH)[0]["credit_balance"];
                    $debt = $db->query($get_client_credit)->fetch_all(MYSQLI_BOTH)[0]["credit_debt"];

                    // --- UPDATES ---

                    // calculate debt
                    if ($trans_amount < $debt)
                        $debt = $debt - $trans_amount;
                    else
                        $debt = 0;

                    // calculate balance
                    if (($trans_amount + $balance) < $limit)
                        $balance = $balance + $trans_amount;
                    else
                        $balance = $limit;

                    // update balance and debt of the client
                    $update_client = "UPDATE clients
                            SET credit_balance = $balance, credit_debt = $debt
                            WHERE client_accId = $cid;";
                    $db->query($update_client);

                    // update profit and debt of the seller
                    $update_seller = "UPDATE sellers 
                            SET total_profit = total_profit - ($trans_amount - (commission * $trans_amount)), debt = debt - (commission*$trans_amount) 
                            WHERE seller_accId = $sid;";
                    $db->query($update_seller);

                    // if not an employee transaction
                    if (!isset($eid))
                        $eid = 0;

                    // enter names in transaction
                    $client_name = $db->query("SELECT name FROM ccc_users WHERE accId = $cid;")->fetch_all(MYSQLI_BOTH)[0]["name"];
                    $seller_name = $db->query("SELECT name FROM ccc_users WHERE accId = $sid;")->fetch_all(MYSQLI_BOTH)[0]["name"];

                    // insert the transaction in transaction's table
                    $insert_transaction = "INSERT INTO transactions (TID, amount, trans_type, client_name, seller_name, client_accId, seller_accId, empId)
                            VALUES ($TID, $trans_amount, 'CREDIT', '$client_name', '$seller_name', $cid, $sid, $eid);";
                    $db->query($insert_transaction);

                    // saving SESSION data to show specific changes after the submission
                    $_SESSION["trans_rownum"] = get_rownum_from_result($db->query("SELECT t.TID AS 'id', t.trans_type AS 'type' FROM trans_data t;"), $id = array($TID, 'CREDIT'));
                    $_SESSION["client_rownum"] = get_rownum_from_result($db->query("SELECT AccountID AS 'id' FROM trans_clients_info;"), $id = $cid);
                    $_SESSION["seller_rownum"] = get_rownum_from_result($db->query("SELECT AccountID AS 'id' FROM trans_sellers_info;"), $id = $sid);

                    $status = "success";
                } else {
                    // primary key already exists, show an error message
                    $status = array("error" => "key_exists");
                }
            }
        } elseif (isset($_GET["payoff"])) {
            $required_fields = array("account_to_payoff", "amount_to_payoff");

            if (!check_fields($required_fields)) {
                $status = array("error" => "empty_fields");
            } else {
                $account_to_payoff = $_POST["account_to_payoff"];
                $amount = filter($_POST["amount_to_payoff"]);

                if ($amount > 0) {
                    $check_amount = "SELECT * FROM user_debts WHERE accId = $account_to_payoff AND debt >= $amount;";
                    $check_amount_rows = mysqli_num_rows($db->query($check_amount));

                    if ($check_amount_rows) {
                        // check if it is client or seller
                        $check_user = "SELECT ud.accId FROM user_debts ud, clients c WHERE ud.accId = c.client_accId AND ud.accId = $account_to_payoff;";
                        $check_user_rows = mysqli_num_rows($db->query($check_user));

                        if ($check_user_rows) { // it is CLIENT
                            // decrease the debt from the client's account
                            $decrease_client_debt = "UPDATE clients SET credit_debt = credit_debt - $amount WHERE client_accId = $account_to_payoff;";
                            $db->query($decrease_client_debt);

                            // increase the balance because the client pays off the debt
                            $increase_client_balance = "UPDATE clients SET credit_balance = credit_balance + $amount WHERE client_accId = $account_to_payoff;";
                            $db->query($increase_client_balance);
                        } else { // else it is SELLER
                            // decrease the debt from the seller's account
                            $decrease_seller_debt = "UPDATE sellers SET debt = debt - $amount WHERE seller_accId = $account_to_payoff;";
                            $db->query($decrease_seller_debt);
                        }
                        
                        // saving SESSION data to show specific changes after the submission
                        $_SESSION["user_payoff_rownum"] = get_rownum_from_result($db->query("SELECT accId AS 'id' FROM user_debts;"), $id = $account_to_payoff);

                        $status = "success";
                    } else {
                        $status = array("error" => "large_amount");
                    }
                } else {
                    $status = array("error" => "zero_amount");
                }
            }
        }
    }
} catch (mysqli_sql_exception $e) {
    $db->close();
    die($e);
}
?>

<body>
    <!-- main container -->
    <div class="container" id="transactions_container">

        <!-- show content -->
        <?php if (isset($_GET["buy"])) { ?>
            <form class="well form-horizontal" action="transactions.php?buy" method="post" id="buy_product_form">
                <center>
                    <h2><b><i class="fa fa-credit-card-alt"></i> Buy</b></h2>
                </center>
                <br>

                <!-- client type selection to buy -->
                <div class="form-group" id="select_client_div">
                    <label class="col-md-4 control-label">Select Client:</label>
                    <div class="col-md-4 selectContainer">
                        <div class="input-group">
                            <span class="input-group-addon"></span>
                            <select name="select_type_client_to_buy" id="select_type_client_to_buy" class="form-control selectpicker" required>
                                <option value="" disabled selected>Client</option>
                                <option value="individual" style='font-weight:bold;'>INDIVIDUAL</option>
                                <option value="company_employee" style='font-weight:bold;'>COMPANY EMPLOYEE</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- client selection to buy -->
                <div class="form-group" id="client_buy_div">
                    <label class="col-md-4 control-label">Pick a Client to Buy:</label>
                    <div class="col-md-4 selectContainer">
                        <div class="input-group">
                            <span class="input-group-addon"></span>
                            <select name="client_buy" id="client_buy" class="form-control selectpicker" required>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- seller selection for purchase -->
                <div class="form-group" id="seller_buy_div">
                    <label class="col-md-4 control-label">Pick a Seller to Buy from:</label>
                    <div class="col-md-4 selectContainer">
                        <div class="input-group">
                            <span class="input-group-addon"></span>
                            <select name="seller_buy" id="seller_buy" class="form-control selectpicker" required>
                                <option value="" disabled selected>Seller</option>
                                <?php
                                try {
                                    $sellers = "SELECT u.accId, u.name, s.total_profit 
                                    FROM ccc_users u, sellers s 
                                    WHERE s.seller_accId = u.accId;";

                                    $result = $db->query($sellers);
                                    $rows = mysqli_num_rows($result);
                                    $accs = $result->fetch_all(MYSQLI_BOTH);

                                    for ($i = 0; $i < $rows; $i++) {
                                        echo "<option value='" . $accs[$i]["accId"] . "' style='font-weight:bold;'>" . $accs[$i]["accId"] . " - " . $accs[$i]["name"] . " (profit: " . $accs[$i]["total_profit"] . ")</option>";
                                    }
                                } catch (mysqli_sql_exception $e) {
                                    $db->close();
                                    die($e);
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-4 control-label">Amount to transfer:</label>
                    <div class="col-md-4 inputGroupContainer">
                        <div class="input-group">
                            <span class="input-group-addon"></span>
                            <input type="number" step="0.01" name="amount_to_transfer" id="amount_to_transfer" placeholder="Amount" class="form-control" value="0" required>
                        </div>
                    </div>
                </div>

                <!-- submit button -->
                <div class="form-group submit_btn_div">
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-success">Buy <span class="fa fa-send"></span></button>
                    </div>
                </div>

            </form>
        <?php } elseif (isset($_GET["refund"])) { ?>
            <form class="well form-horizontal" action="transactions.php?refund" method="post" id="return_money_form">
                <center>
                    <h2><b><i class="fa fa-money"></i> Refund</b></h2>
                </center>
                <br>

                <!-- dropdown list with clients to refund -->
                <div class="form-group">
                    <label class="col-md-4 control-label">Select Client (Transaction) to Refund</label>
                    <div class="col-md-4 selectContainer">
                        <div class="input-group">
                            <span class="input-group-addon"></span>
                            <select name="transaction_refund" id="account_to_refund" class="form-control selectpicker" required>
                                <option value="" disabled selected>Select Client</option>
                                <?php
                                try {
                                    // show client for refund
                                    $sql = "SELECT t.TID, u.accId, u.name, c.client_type, ce.emp_name
                                    FROM ccc_users u, clients c, transactions t
                                    LEFT JOIN company_employees ce ON ce.empId = t.empId
                                    WHERE t.trans_type = 'DEBIT'
                                    AND t.client_accId = c.client_accId
                                    AND c.client_accId = u.accId;";

                                    $result = $db->query($sql);
                                    $rows = mysqli_num_rows($result);
                                    $clients = $result->fetch_all(MYSQLI_BOTH);

                                    for ($i = 0; $i < $rows; $i++) {
                                        if (!isset($clients[$i]["emp_name"]))
                                            echo "<option style='font-weight:bold;' value='" . $clients[$i]["TID"] . "'>" . "TID: " . $clients[$i]["TID"] . " - " . $clients[$i]["name"] . " (" . $clients[$i]["client_type"] . ")" . "</option>";
                                        else
                                            echo "<option style='font-weight:bold;' value='" . $clients[$i]["TID"] . "'>" . "TID: " . $clients[$i]["TID"] . " - " . $clients[$i]["emp_name"] . " (" . $clients[$i]["client_type"] . ")" . "</option>";
                                    }
                                } catch (mysqli_sql_exception $e) {
                                    $db->close();
                                    die($e);
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- submit button -->
                <div class="form-group submit_btn_div">
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-success">Refund <span class="fa fa-send"></span></button>
                    </div>
                </div>

            </form>
        <?php } elseif (isset($_GET["payoff"])) { ?>
            <form class="well form-horizontal" action="transactions.php?payoff" method="post" id="payoff_form">
                <center>
                    <h2><b><i class="fa fa-check-circle"></i> Payoff</b></h2>
                </center>
                <br>

                <!-- dropdown list with acounts with non-zero debts, to payoff -->
                <div class="form-group">
                    <label class="col-md-4 control-label">Select Account Pay debt</label>
                    <div class="col-md-4 selectContainer">
                        <div class="input-group">
                            <span class="input-group-addon"></span>
                            <select name="account_to_payoff" id="account_to_payoff" class="form-control selectpicker" required>
                                <option value="" disabled selected>Select Account</option>
                                <?php
                                try {
                                    // fetch all accounts to show in the dropdown menu
                                    $accIds = "SELECT * FROM user_debts;";

                                    $result = $db->query($accIds);
                                    $rows = mysqli_num_rows($result);
                                    $accs = $result->fetch_all(MYSQLI_BOTH);

                                    for ($i = 0; $i < $rows; $i++) {

                                        $disable = null;
                                        if ($accs[$i]["debt"] == 0)
                                            $disable = "disabled";
                                        else
                                            $disable = "style='font-weight:bold;'";

                                        echo "<option value='" . $accs[$i]["accId"] . "' $disable>" . $accs[$i]["accId"] . " - " . $accs[$i]["name"] . " (debt = " . $accs[$i]["debt"] . ")</option>";
                                    }
                                } catch (mysqli_sql_exception $e) {
                                    $db->close();
                                    die($e);
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- input field for amount to payoff -->
                <div class="form-group">
                    <label class="col-md-4 control-label">Amount to pay:</label>
                    <div class="col-md-4 inputGroupContainer">
                        <div class="input-group">
                            <span class="input-group-addon"></span>
                            <input type="number" step="0.01" name="amount_to_payoff" id="amount_to_payoff" placeholder="Enter amount" class="form-control" value="0" required>
                        </div>
                    </div>
                </div>

                <!-- submit button -->
                <div class="form-group submit_btn_div">
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-success">Pay <span class="fa fa-send"></span></button>
                    </div>
                </div>
            </form>

        <?php } ?>

        <div class="col-md-4">
            <a href="index.php"><button type="button" class="btn btn-primary">Return to Home <span class="fa fa-arrow-left"></span></button></a>
        </div>

        <!-- show status message -->
        <?php include 'error.php'; ?>

        <!-- show results -->
        <?php
        if (isset($_GET["buy"]) || isset($_GET["refund"])) {

            // show transaction data
            $result = $db->query("SELECT TID, trans_type AS 'Type', trans_date AS 'Transaction Date', amount AS 'Amount', client_name AS 'Client', seller_name AS 'Seller', emp_name AS 'Employee' FROM trans_data;");
            if (isset($status) && $status == "success")
                echo "<br><div class='col-md-15'>" . display_data($result, $color_row = $_SESSION["trans_rownum"]) . "</div>";
            else
                echo "<br><div class='col-md-15'>" . display_data($result) . "</div>";

            // show transaction clients info
            $result = $db->query("SELECT * FROM trans_clients_info;");
            if (isset($status) && $status == "success")
                echo "<br><div class='col-md-15'>" . display_data($result, $color_row = $_SESSION["client_rownum"]) . "</div>";
            else
                echo "<br><div class='col-md-15'>" . display_data($result) . "</div>";

            // show transaction sellers info
            $result = $db->query("SELECT * FROM trans_sellers_info;");
            if (isset($status) && $status == "success")
                echo "<br><div class='col-md-15'>" . display_data($result, $color_row = $_SESSION["seller_rownum"]) . "</div>";
            else
                echo "<br><div class='col-md-15'>" . display_data($result) . "</div>";
        } elseif (isset($_GET["payoff"])) {
            $query = "SELECT accId AS 'AccountID', name AS 'Name', debt AS 'AvailableDebt' FROM user_debts;";
            $result = $db->query($query);
            if (isset($status) && $status == "success")
                echo "<br><div class='col-md-15'>" . display_data($result, $color_row = $_SESSION["user_payoff_rownum"]) . "</div>";
            else
                echo "<br><div class='col-md-15'>" . display_data($result) . "</div>";
        }
        ?>

    </div>

    <?php
    $db->close();
    include 'inc/footer.php';
    ?>