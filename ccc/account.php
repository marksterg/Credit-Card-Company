<?php
/**
 * account.php
 * 
 * Show registration form and handles all the POST requests (after submissions)
 */

include 'inc/header.php';
require 'db/config.php';
require 'inc/utils.php';

// open db connection 
$db = (new DB())->get_db_connection();

$status = null;
try {
    // REQUEST PROCESS
    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        // checking for account procedure
        if (isset($_GET["register"])) {

            if ($_POST["user_type"] == "client") {

                if ($_POST["client_type"] == "individual" || $_POST["client_type"] == "company") {

                    $required_fields = array("user_type", "client_type", "client_name", "expire_date", "credit_limit", "credit_debt", "credit_balance");

                    if (!check_fields($required_fields)) {
                        $status = array("error" => "empty_fields");
                    } else {

                        $credit_limit = filter($_POST["credit_limit"]);
                        $credit_debt = filter($_POST["credit_debt"]);
                        $credit_balance = filter($_POST["credit_balance"]);

                        if ($credit_limit == ($credit_balance + $credit_debt)) {
                            $user_type = $_POST["user_type"];
                            $client_type = strtoupper($_POST["client_type"]);
                            $client_name = filter($_POST["client_name"]);
                            $expire_date = $_POST["expire_date"];

                            $user_insert = "INSERT INTO ccc_users (name) VALUES ('$client_name');";
                            $db->query($user_insert);
                            $last_id = $db->insert_id;

                            $client_insert = "INSERT INTO clients (client_accId, exp_date, credit_limit, credit_debt, credit_balance, client_type)
                            VALUES ($last_id, '$expire_date', $credit_limit, $credit_debt, $credit_balance, '$client_type');";
                            $db->query($client_insert);

                            // saving session data
                            $row_num = get_rownum_from_result($db->query("SELECT u.accId AS 'id' FROM ccc_users u, clients c WHERE u.accId = c.client_accId;"), $last_id);
                            $_SESSION["register_rownum"] = $row_num;

                            $status = "success";
                        } else {
                            $status = array("error" => "wrong_balance_debt");
                        }
                    }
                } elseif ($_POST["client_type"] == "company_employee") {

                    $required_fields = array("emp_name", "company_id");

                    if (!check_fields($required_fields)) {
                        $status = array("error" => "empty_fields");
                    } else {
                        $emp_name = filter($_POST["emp_name"]);
                        $company_id = $_POST["company_id"];

                        $comp_employees = "SELECT ce.empId 
                        FROM company_employees ce, clients c 
                        WHERE ce.client_accId = c.client_accId 
                        AND ce.client_accId = $company_id ORDER BY ce.empId;";

                        $result = $db->query($comp_employees);
                        $rows = mysqli_num_rows($result);

                        $employee_id = "";
                        if ($rows == 0) {
                            // be the first company employee id (concatenate compId and empId)
                            $employee_id = $company_id . "1";
                        } else {
                            // else, increase the id for the new company employee
                            $e = $result->fetch_all(MYSQLI_NUM);
                            $employee_id = $e[$rows - 1][0] + 1;
                        }

                        $employee_insert = "INSERT INTO company_employees (empId, emp_name, client_accId) VALUES ($employee_id, '$emp_name', $company_id);";
                        $db->query($employee_insert);

                        $row_num = get_rownum_from_result($db->query("SELECT empId AS 'id' FROM company_employees;"), $employee_id);
                        $_SESSION["register_rownum"] = $row_num;

                        $status = "success";
                    }
                }
            } elseif ($_POST["user_type"] == "seller") {

                $required_fields = array("seller_name", "total_profit", "debt");

                if (!check_fields($required_fields)) {
                    $status = array("error" => "empty_fields");
                } else {
                    $seller_name = filter($_POST["seller_name"]);
                    $commission = filter($_POST["commission"]);
                    $total_profit = filter($_POST["total_profit"]);
                    $debt = filter($_POST["debt"]);

                    $user_insert = "INSERT INTO ccc_users (name) VALUES ('$seller_name');";
                    $db->query($user_insert);
                    $last_id = $db->insert_id;

                    $seller_insert = "INSERT INTO sellers (seller_accId, commission, total_profit, debt)
                    VALUES ($last_id, $commission, $total_profit, $debt);";
                    $db->query($seller_insert);

                    // saving session data
                    $row_num = get_rownum_from_result($db->query("SELECT u.accId AS 'id' FROM ccc_users u, sellers s WHERE u.accId = s.seller_accId;"), $last_id);
                    $_SESSION["register_rownum"] = $row_num;

                    $status = "success";
                }
            }
        } elseif (isset($_GET["close"])) {
            $account_to_close = $_POST["account_to_close"];

            // check if the acccount for deletion is a company. If true then delete the employees FIRST
            $company_check = "SELECT * FROM clients c WHERE c.client_accId = $account_to_close AND c.client_type = 'COMPANY';";
            $rows = mysqli_num_rows($db->query($company_check));

            if ($rows) {
                $emp_to_delete = "DELETE FROM company_employees WHERE client_accId = $account_to_close;";
                $db->query($emp_to_delete);
            }

            $acc_to_delete = "DELETE FROM ccc_users WHERE accId = $account_to_close;";
            $db->query($acc_to_delete);

            $status = "success";
        }
    }
} catch (mysqli_sql_exception $e) {
    $db->close();
    die($e);
}
?>

<body>

    <!-- Main Container -->
    <div class="container" id="account_container">

        <?php if (isset($_GET["register"])) { ?>
            <!-- Account Registration form -->
            <form class="well form-horizontal" action="account.php?register" method="post" id="register_account_form">

                <!-- form title -->
                <center>
                    <h2><b>Account Registration</b></h2>
                </center>
                <br>

                <!-- dropdown menu input for user type selection -->
                <div class="form-group" id="user_type_div">
                    <label class="col-md-4 control-label">User Type</label>
                    <div class="col-md-4 selectContainer">
                        <div class="input-group">
                            <span class="input-group-addon"></span>
                            <select name="user_type" id="user_type" class="form-control selectpicker" required>
                                <option value="" disabled selected>User Type</option>
                                <option value="client" style='font-weight:bold;'>CLIENT</option>
                                <option value="seller" style='font-weight:bold;'>SELLER</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- empty divs for drawing later with js -->
                <div id="client_div"></div>
                <div id="seller_div"></div>
                <div id="individual_div"></div>
                <div id="company_div"></div>
                <div id="company_employee_div"></div>

            </form>
        <?php } elseif (isset($_GET["close"])) { ?>

            <!-- Close Account form -->
            <form class="well form-horizontal" action="account.php?close" method="post" id="close_account_form">

                <!-- form title -->
                <center>
                    <h2><b><i class="fa fa-ban"></i> Close Account</b></h2>
                </center>
                <br>

                <!-- dropdown menu input for account (to delete) selection -->
                <div class="form-group">
                    <label class="col-md-4 control-label">Select Account to delete</label>
                    <div class="col-md-4 selectContainer">
                        <div class="input-group">
                            <span class="input-group-addon"></span>
                            <select name="account_to_close" id="account_to_close" class="form-control selectpicker" required>
                                <option value="" disabled selected>Select Account</option>
                                <?php
                                try {
                                    // fetch all accounts to show in the dropdown menu
                                    $result = $db->query("SELECT * FROM user_debts;");
                                    $rows = mysqli_num_rows($result);
                                    $accs = $result->fetch_all(MYSQLI_BOTH);

                                    for ($i = 0; $i < $rows; $i++) {

                                        $disable = null;
                                        if ($accs[$i]["debt"] != 0)
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

                <!-- submit button -->
                <div class="form-group submit_btn_div">
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-danger">Delete <span class="fa fa-ban"></span></button>
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
        $result = null;
        if (isset($_GET["register"])) {

            // check the client type registration
            if (isset($_POST["client_type"])) {

                if ($_POST["client_type"] == "company_employee") {
                    $result = $db->query("SELECT ce.empId AS 'EmployeeID', ce.emp_name AS 'EmployeeName', u.name AS 'CompanyName' 
                        FROM company_employees ce, clients c, ccc_users u
                        WHERE ce.client_accId = c.client_accId
                        AND c.client_accId = u.accId;");
                } else {
                    $result = $db->query("SELECT u.accId AS 'AccountID', u.name AS 'ClientName', c.client_type AS 'ClientType', c.credit_limit AS 'CreditLimit', c.credit_debt AS 'CreditDebt', c.credit_balance AS 'CreditBalance', c.exp_date AS 'ExpireDate'
                    FROM ccc_users u, clients c
                    WHERE u.accId = c.client_accId;");
                }

            } elseif (isset($_POST["user_type"])) {
                $result = $db->query("SELECT u.accId AS 'AccountID', u.name AS 'SellerName', s.commission AS 'Commission', s.total_profit AS 'Profit', s.debt AS 'Debt'
                    FROM ccc_users u, sellers s
                    WHERE u.accId = s.seller_accId;");
            }

            // in case of a successful procedure draw the specific row in table in which the modification was made
            if (isset($status) && $status == "success")
                echo "<br><div class='col-md-15'>" . display_data($result, $color_row = $_SESSION["register_rownum"]) . "</div>";
            else
                echo "<br><div class='col-md-15'>" . display_data($result) . "</div>";
                
        } elseif (isset($_GET["close"])) {
            $result = $db->query("SELECT accId AS 'AccountID', name AS 'Name' FROM ccc_users;");
            echo "<br><div class='col-md-15'>" . display_data($result) . "</div>";
        }
        ?>

    </div>

    <?php
    $db->close();
    include 'inc/footer.php';
    ?>