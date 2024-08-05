<?php
/**
 * info.php
 * 
 * To show the states of good clients, bad clients and seller of the month
 */

include 'inc/header.php';
require 'db/config.php';
require 'inc/utils.php';

// open db connection
$db = (new DB())->get_db_connection();

// start the session to retrieve the data for the seller of the month
session_start();
?>

<body>

    <!-- Main container -->
    <div class="container" id="info_container">

        <?php if (isset($_GET["good_clients"])) { ?>
            <div class="row text-center">
                <div class="col">
                    <h1 class="w3-large"><i class="fa fa-user-plus"></i> GOOD CLIENTS </h1>
                </div>
            </div>
            <div class="col-md">
                <?php
                try {
                    $sql = "SELECT u.accId AS 'AccountID', u.name AS 'Client Name', c.exp_date AS 'Expire Date', c.credit_limit AS 'Credit Limit', c.credit_balance AS 'Credit Balance', c.credit_debt AS 'Credit Debt'
                    FROM ccc_users u, clients c
                    WHERE u.accId = c.client_accId
                    AND c.credit_debt = 0
                    ORDER BY u.accId;";

                    $good_clients = $db->query($sql);

                    echo display_data($good_clients);
                } catch (mysqli_sql_exception $e) {
                    $db->close();
                    die($e);
                }
                ?>
            </div>
        <?php } elseif (isset($_GET["bad_clients"])) { ?>
            <div class="row text-center">
                <div class="col">
                    <h1 class="w3-large"><i class="fa fa-user-times"></i> BAD CLIENTS </h1>
                </div>
            </div>
            <div class="col-md">
                <?php
                try {
                    $sql = "SELECT u.accId AS 'AccountID', u.name AS 'Client Name', c.exp_date AS 'Expire Date', c.credit_limit AS 'Credit Limit', c.credit_balance AS 'Credit Balance', c.credit_debt AS 'Credit Debt'
                    FROM ccc_users u, clients c
                    WHERE u.accId = c.client_accId
                    AND c.credit_debt <> 0
                    ORDER BY c.credit_debt DESC;";

                    $bad_clients = $db->query($sql);

                    echo display_data($bad_clients);
                } catch (mysqli_sql_exception $e) {
                    $db->close();
                    die($e);
                }
                ?>
            </div>
        <?php } elseif (isset($_GET["seller_of_the_month"])) { ?>
            <div class="row text-center">
                <div class="col">
                    <h1 class="w3-large"><i class="fa fa-user-circle"></i> SELLER OF THE MONTH </h1>
                </div>
            </div>

            <div class="col-md">
                <?php
                try {
                    // show updated info of the seller of the month (we need this in all situations)
                    $updated_seller_of_month = "SELECT * FROM seller_of_month;";
                    $display_result = $db->query($updated_seller_of_month);

                    if (mysqli_num_rows($db->query($updated_seller_of_month))) {

                        $array = $display_result->fetch_all(MYSQLI_BOTH);

                        // initial state (if there is no session data initialized yet)
                        if (!isset($_SESSION["seller_of_month"])) {

                            // initialize session data to hold the (previous) seller of the month id
                            $_SESSION["seller_of_month"] = $array[0][0];
                            $_SESSION["saved_result"] = array("cols" => mysqli_fetch_fields($display_result), "data" => $array);

                            // update seller's debt
                            $update_seller = "UPDATE sellers SET debt = debt - (debt*0.05), total_profit = total_profit + (debt*0.05) WHERE seller_accId = " . $array[0][0] . ";";
                            $db->query($update_seller);

                            echo display_data($display_result);

                            // state: the session data exists, now check if the saved seller id is the same with the updated on or not
                        } else {
                            /* 
                            checks if the current (calculated) seller of the month is different (new one) than the one we hold in session data
                            if they are different change the session id to hold the new seller and update the debt of him
                            IMPORTANT: seller of the month is calculated automatically in view seller_of_month every month for the PREVIOUS month.
                            */
                            if ($array[0][0] != $_SESSION["seller_of_month"]) {

                                // update the session data to hold the new seller id
                                $_SESSION["seller_of_month"] = $array[0][0];
                                $_SESSION["saved_result"] = array("cols" => mysqli_fetch_fields($display_result), "data" => $array);

                                // update debt of the new seller of the month
                                $update_seller = "UPDATE sellers SET debt = debt - (debt*0.05), total_profit = total_profit + (debt*0.05) WHERE seller_accId = " . $array[0][0] . ";";
                                $db->query($update_seller);

                                echo display_data($display_result);
                            } else {
                                /*
                                if the seller is the same then just print the same table as the last time we calculate it (which is held in sessin data)
                                */
                                
                                echo display_data(null, null, $_SESSION["saved_result"]);
                            }
                        }
                    } else {
                        echo display_data($display_result);
                    }
                } catch (mysqli_sql_exception $e) {
                    $db->close();
                    die($e);
                }
                ?>
            </div>
        <?php } ?>

        <!-- redirecting to home page -->
        <div class="col-md-4">
            <a href="index.php"><button type="button" class="btn btn-primary">Return to Home <span class="fa fa-arrow-left"></span></button></a>
        </div>

    </div>

    <?php
    include 'inc/footer.php';
    ?>