<?php
/**
 * transactions_ajax.php
 * 
 * Handles ajax requests.
 * Return html code as response and fill in specific divs (dropdown menus)
 */

require 'db/config.php';
require 'inc/utils.php';

// open db connection
$db = (new DB())->get_db_connection();
?>

<!-- REQUEST PROCESS -->
<?php if ($_SERVER["REQUEST_METHOD"] == "POST") { ?>
    <!-- IF THE TRANSACTION PROCEDURE IS BUY -->
    <?php if (isset($_POST["trans_procedure"]) && $_POST["trans_procedure"] == "buy") { ?>
        <!-- RETURN CLIENTS DATA TO FILL THE SPECIFIC DROPDOWN MENU LIST -->
        <?php if (isset($_POST["select_type_client_to_buy"])) { ?>
            <option value="" disabled selected>Client</option>
            <?php
            try {
                if ($_POST["select_type_client_to_buy"] == "individual") {
                    $clients = "SELECT u.accId, u.name, c.credit_balance 
                                    FROM ccc_users u, clients c 
                                    WHERE c.client_accId = u.accId
                                    AND c.client_type = 'INDIVIDUAL';";

                    $result = $db->query($clients);
                    $rows = mysqli_num_rows($result);
                    $accs = $result->fetch_all(MYSQLI_BOTH);

                    for ($i = 0; $i < $rows; $i++) {
                        $disable = null;
                        if ($accs[$i]["credit_balance"] == 0)
                            $disable = 'disabled';
                        else
                            $disable = "style='font-weight:bold;'";

                        echo "<option value='" . $accs[$i]["accId"] . "' $disable>" . $accs[$i]["accId"] . " - " . $accs[$i]["name"] . " (balance: " . $accs[$i]["credit_balance"] . ")</option>";
                    }
                } elseif ($_POST["select_type_client_to_buy"] == "company_employee") {
                    $employees = "SELECT ce.empId, ce.emp_name, u.name, c.credit_balance
                                    FROM ccc_users u, clients c, company_employees ce
                                    WHERE ce.client_accId = c.client_accId
                                    AND c.client_accId = u.accId;";

                    $result = $db->query($employees);
                    $rows = mysqli_num_rows($result);
                    $emps = $result->fetch_all(MYSQLI_BOTH);

                    for ($i = 0; $i < $rows; $i++) {
                        $disable = null;
                        if ($emps[$i]["credit_balance"] == 0) $disable = 'disabled';
                        else $disable = "style='font-weight:bold;'";

                        echo "<option value='" . $emps[$i]['empId'] . "' $disable>" . $emps[$i]["empId"] . " - " . $emps[$i]["emp_name"] . " - " . $emps[$i]["name"] ." (balance = " . $emps[$i]["credit_balance"] . ")</option>";
                    }
                }
            } catch (mysqli_sql_exception $e) {
                $db->close();
                die($e);
            }
            ?>
        <?php } ?>

    <?php } ?>

<?php }
$db->close();
?>