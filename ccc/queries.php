<?php

/**
 * queries.php
 * 
 * Handles all the possible queries in the transactions table that have been given from us.
 */

include 'inc/header.php';
require 'db/config.php';
require 'inc/utils.php';

// open db connection
$db = (new DB())->get_db_connection();
?>

<body>

    <!-- Main Container -->
    <div class="container" id="queries_container">

        <!-- page title -->
        <center>
            <h2><b><i class="fa fa-question-circle"></i> Queries</b></h2>
        </center>
        <br>

        <!-- queries form  -->
        <form class="well form-horizontal" action="queries.php" method="post" id="queries_form">

            <!-- amount -->
            <div class="form-group">
                <label class="col-md-4 control-label">From Amount:</label>
                <div class="col-md-4 inputGroupContainer">
                    <div class="input-group">
                        <span class="input-group-addon"></span>
                        <input type="number" step="0.01" name="from_amount" id="from_amount" placeholder="Amount" class="form-control" value="0">
                    </div>
                </div>
            </div>

            <!-- client id -->
            <div class="form-group" id="client_id_div">
                <label class="col-md-4 control-label">Client</label>
                <div class="col-md-4 selectContainer">
                    <div class="input-group">
                        <span class="input-group-addon"></span>
                        <select name="client_id" id="client_id" class="form-control selectpicker">
                            <option value="" disabled selected>Select Client</option>
                            <option value="none" style='font-weight:bold;'>NONE</option>
                            <?php
                            try {
                                $sql = "SELECT u.accId, u.name, c.client_type FROM ccc_users u, clients c WHERE c.client_accId = u.accId;";

                                // list with all clients
                                $result = $db->query($sql);
                                $rows = mysqli_num_rows($result);
                                $clients = $result->fetch_all(MYSQLI_BOTH);

                                if ($rows) {
                                    for ($i = 0; $i < $rows; $i++) {
                                        echo "<option style='font-weight:bold;' value='" . $clients[$i]["accId"] . "'>" . $clients[$i]["accId"] . " - " . $clients[$i]["name"] . " (" . $clients[$i]["client_type"] . ")</option>";
                                    }
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

            <!-- seller id -->
            <div class="form-group" id="seller_id_div">
                <label class="col-md-4 control-label">Seller</label>
                <div class="col-md-4 selectContainer">
                    <div class="input-group">
                        <span class="input-group-addon"></span>
                        <select name="seller_id" id="seller_id" class="form-control selectpicker">
                            <option value="" disabled selected>Select Seller</option>
                            <option value="none" style='font-weight:bold;'>NONE</option>
                            <?php
                            try {
                                $sql = "SELECT u.accId, u.name FROM ccc_users u, sellers s WHERE u.accId = s.seller_accId;";
                                $result = $db->query($sql);
                                $rows = mysqli_num_rows($result);
                                $sellers = $result->fetch_all(MYSQLI_BOTH);

                                for ($i = 0; $i < $rows; $i++) {
                                    echo "<option style='font-weight:bold;' value='" . $sellers[$i]["accId"] . "'>" . $sellers[$i]["accId"] . " - " . $sellers[$i]["name"] . "</option>";
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

            <!-- from date -->
            <div class="form-group">
                <label class="col-md-4 control-label">From Date</label>
                <div class="col-md-4 inputGroupContainer">
                    <div class="input-group">
                        <span class="input-group-addon"></span>
                        <input type="date" name="from_date" id="from_date" class="form-control" value="">
                    </div>
                </div>
            </div>

            <!-- to date -->
            <div class="form-group">
                <label class="col-md-4 control-label">To Date</label>
                <div class="col-md-4 inputGroupContainer">
                    <div class="input-group">
                        <span class="input-group-addon"></span>
                        <input type="date" name="to_date" id="to_date" class="form-control" value="">
                    </div>
                </div>
            </div>

            <!-- transaction type -->
            <div class="form-group" id="transaction_type_div">
                <label class="col-md-4 control-label">Transaction Type</label>
                <div class="col-md-4 selectContainer">
                    <div class="input-group">
                        <span class="input-group-addon"></span>
                        <select name="transaction_type" id="transaction_type" class="form-control selectpicker">
                            <option value="" disabled selected>Transaction Type</option>
                            <option value="none" style='font-weight:bold;'>NONE</option>
                            <option value="debit" style='font-weight:bold;'>DEBIT</option>
                            <option value="credit" style='font-weight:bold;'>CREDIT</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- employee id -->
            <div class="form-group" id="select_employee_div">
                <label class="col-md-4 control-label">Company's Employee(s)</label>
                <div class="col-md-4 selectContainer">
                    <div class="input-group">
                        <span class="input-group-addon"></span>
                        <select name="employee_id" id="employee_id" class="form-control selectpicker">
                        </select>
                    </div>
                </div>
            </div>

            <!-- submit button -->
            <div class="form-group search_btn_div">
                <div class="col-md-4">
                    <button type="submit" class="btn btn-success">Search <span class="fa fa-search"></span></button>
                </div>
            </div>
        </form>

        <!-- redirect to home button -->
        <div class="col-md-4">
            <a href="index.php"><button type="button" class="btn btn-primary">Return to Home <span class="fa fa-arrow-left"></span></button></a>
        </div>

        <!-- Request process -->
        <?php
        $result = null;
        $status = null;
        $select_args = "TID, trans_type AS 'Type', trans_date AS 'Transaction Date', amount AS 'Amount', client_name AS 'Client', seller_name AS 'Seller', emp_name AS 'Employee'";
        try {
            if ($_SERVER["REQUEST_METHOD"] == "POST") {

                $sql = "SELECT $select_args FROM trans_data WHERE 1=1 ";

                foreach ($_POST as $id => $value) {
                    if (isset($id) && $id != "clientType" && $value != "" && $value != "none") {
                        //echo $id . ", ";
                        switch ($id) {
                            case "from_amount":
                                $sql .= "AND amount >= $value ";
                                break;
                            case "client_id":
                                $sql .= "AND client_accId = $value ";
                                break;
                            case "seller_id":
                                $sql .= "AND seller_accId = $value ";
                                break;
                            case "from_date":
                                $sql .= "AND DATE(trans_date) > '$value' ";
                                break;
                            case "to_date":
                                $sql .= "AND DATE(trans_date) < '$value' ";
                                break;
                            case "transaction_type":
                                $sql .= "AND trans_type = '$value' ";
                                break;
                            case "employee_id":
                                $sql .= "AND empId = $value ";
                                break;
                        }

                    }
                }

                $sql .= "ORDER BY trans_date ASC;";

                $result = $db->query($sql);

                if (mysqli_num_rows($result) == 0)
                    $status = array("error" => "not_found_trans");
                else
                    $status = "success";
            }
        } catch (mysqli_sql_exception $e) {
            $db->close();
            die($e);
        }
        ?>

        <!-- show results -->
        <?php include 'error.php'; ?>

        <?php
        if (isset($result))
            echo "<br><div class='col-md-15'>" . display_data($result) . "</div>";
        else
            echo "<br><div class='col-md-15'>" . display_data($db->query("SELECT $select_args FROM trans_data;")) . "</div>";
        ?>

    </div>

    <?php
    $db->close();
    include 'inc/footer.php';
    ?>