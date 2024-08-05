<?php
/**
 * error.php
 * 
 * To show status message after a completion of a procedure
 */
?>

<br>
<!-- Status Message area -->
<?php if (isset($status) && isset($status["error"])) { ?>
    <!-- Error message -->
    <div class="form-group alert_form_div">
        <div class="col-md-4">
            <div class="alert alert-danger" role="alert" id="error_message">
                <b>
                    <?php
                    switch ($status["error"]) {
                        case "sql_error":
                            echo "SQL Error Occured!";
                            break;
                        case "empty_fields":
                            echo "Empty Fields!";
                            break;
                        case "wrong_dates":
                            echo "Wrong Dates!";
                            break;
                        case "insertion_error":
                            echo "SQL Insertion Error!";
                            break;
                        case "zero_amount":
                            echo "Zero Amount!";
                            break;
                        case "insufficient_amount":
                            echo "Insufficient Amount for purchase!";
                            break;
                        case "large_amount":
                            echo "Large Amount for purchase!";
                            break;
                        case "key_exists":
                            echo "Refundment already committed!";
                            break;
                        case "not_found_trans":
                            echo "Transactions Not Found!";
                            break;
                        case "wrong_balance_debt":
                            echo "The Credit Card Limit must be the sum of the Balance and Debt!";
                            break;
                    }
                    ?>
                </b>
                <i class="fa fa-thumbs-down"></i>
            </div>
        </div>
    </div>
<?php } elseif (isset($status)) { ?>
    <!-- Success message -->
    <div class="form-group alert_form_div">
        <div class="col-md-4">
            <div class="alert alert-success" role="alert" id="success_message">
                <b>
                    Successful
                    <?php
                    if (isset($_GET["register"])) {
                        echo "Insertion! ";
                    } elseif (isset($_GET["close"])) {
                        echo "Deletion! ";
                    } elseif (isset($_GET["buy"])) {
                        echo "Purchase! ";
                    } elseif (isset($_GET["refund"])) {
                        echo "Refundment! ";
                    } elseif (isset($_GET["payoff"])) {
                        echo "Payoff! ";
                    }
                    ?>
                </b>
                <i class="fa fa-thumbs-up"></i>
            </div>
        </div>
    </div>
<?php } ?>