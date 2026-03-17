<?php
/**
 * account_ajax.php
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
    <!-- CHECKS THE SELECTED USER TYPE -->
    <?php if (isset($_POST["user_type"])) { ?>
        <!-- IF THE USER TYPE IS CLIENT THEN RETURN THE DROPDOWN MENU TO CHOOSE BETWEEN THE DIFFERENT CLIENT TYPES -->
        <?php if ($_POST["user_type"] == "client") { ?>
            <!-- client type div -->
            <div class="form-group" id="client_type_div">
                <label class="col-md-4 control-label">Client Type</label>
                <div class="col-md-4 selectContainer">
                    <div class="input-group">
                        <span class="input-group-addon"></span>
                        <select name="client_type" id="client_type" class="form-control selectpicker" required>
                            <option disabled selected>Client Type</option>
                            <option value="individual" style='font-weight:bold;'>INDIVIDUAL</option>
                            <option value="company" style='font-weight:bold;'>COMPANY</option>
                            <option value="company_employee" style='font-weight:bold;'>COMPANY EMPLOYEE</option>
                        </select>
                    </div>
                </div>
            </div>
            <!-- IF THE USER TYPE IS SELLER THEN RETURN THE ALL THE REQUIRED INPUT FIELDS -->
        <?php } elseif ($_POST["user_type"] == "seller") { ?>
            <!-- Seller registration fields -->
            <fieldset id="seller_register_fields">

                <div class="form-group">
                    <label class="col-md-4 control-label">Seller's Name</label>
                    <div class="col-md-4 inputGroupContainer">
                        <div class="input-group">
                            <span class="input-group-addon"></span>
                            <input type="text" name="seller_name" id="seller_name" placeholder="Seller's Name" class="form-control" required>
                        </div>
                    </div>
                </div>

                <div class="form-group" id="select_commission_div">
                    <label class="col-md-4 control-label">Commission</label>
                    <div class="col-md-4 selectContainer">
                        <div class="input-group">
                            <span class="input-group-addon"></span>
                            <select name="commission" id="commision" class="form-control selectpicker" required>
                                <option value="">Select Commission</option>
                                <option value="0.001">0.1%</option>
                                <option value="0.005">0.5%</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-4 control-label">Debt</label>
                    <div class="col-md-4 inputGroupContainer">
                        <div class="input-group">
                            <span class="input-group-addon"></span>
                            <input type="number" name="debt" id="debt" placeholder="Debt" class="form-control" value="0" required>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-4 control-label">Total Profit</label>
                    <div class="col-md-4 inputGroupContainer">
                        <div class="input-group">
                            <span class="input-group-addon"></span>
                            <input type="number" name="total_profit" id="total_profit" placeholder="Total Profit" class="form-control" value="0" required>
                        </div>
                    </div>
                </div>

            </fieldset>

            <!-- submit button -->
            <div class="form-group submit_btn_div">
                <div class="col-md-4">
                    <button type="submit" class="btn btn-success">Register <span class="fa fa-send"></span></button>
                </div>
            </div>
        <?php } ?>
        <!-- CHECKS THE SELECTED CLIENT TYPE -->
    <?php } elseif (isset($_POST["client_type"])) { ?>
        <!-- IF THE CLIENT TYPE IS INDIVIDUAL OR A COMPANY THEN RETURN THE SAME REQUIRED INPUT FIELDS  -->
        <?php if ($_POST["client_type"] == "individual" || $_POST["client_type"] == "company") { ?>
            <!-- Client Individual registration fields -->
            <fieldset id="client_register_fields">

                <div class="form-group">
                    <label class="col-md-4 control-label">Client's Name</label>
                    <div class="col-md-4 inputGroupContainer">
                        <div class="input-group">
                            <span class="input-group-addon"></span>
                            <input type="text" name="client_name" id="client_name" placeholder="Client's Name" class="form-control" required>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-4 control-label">Credit Limit</label>
                    <div class="col-md-4 selectContainer">
                        <div class="input-group">
                            <span class="input-group-addon"></span>
                            <select name="credit_limit" id="credit_limit" class="form-control selectpicker" required>
                                <option value="" disabled selected>Credit Limit</option>
                                <option value="1000">1000 €</option>
                                <option value="2000">2000 €</option>
                                <option value="5000">5000 €</option>
                                <option value="10000">10000 €</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-4 control-label">Credit Balance</label>
                    <div class="col-md-4 inputGroupContainer">
                        <div class="input-group">
                            <span class="input-group-addon"></span>
                            <input type="number" step="0.01" name="credit_balance" id="credit_balance" placeholder="Credit Balance" class="form-control" value="0" required>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-4 control-label">Credit Debt</label>
                    <div class="col-md-4 inputGroupContainer">
                        <div class="input-group">
                            <span class="input-group-addon"></span>
                            <input type="number" step="0.01" name="credit_debt" id="credit_debt" placeholder="Credit Debt" class="form-control" value="0" required>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-4 control-label">Expire Date</label>
                    <div class="col-md-4 inputGroupContainer">
                        <div class="input-group">
                            <span class="input-group-addon"></span>
                            <input type="date" name="expire_date" id="expire_date" placeholder="Expire Date" class="form-control" value="2025-01-01" min="2025-01-01" required>
                        </div>
                    </div>
                </div>

            </fieldset>
            <!-- IF THE CLIENT TYPE IS COMPANY EMPLOYEE THEN RETURN DIFFERENT REQUIRED INPUT FIELDS -->
        <?php } elseif ($_POST["client_type"] == "company_employee") { ?>
            <!-- Client Company Employee registration fields -->
            <fieldset id="employee_register_fields">
                <div class="form-group" id="select_company_div">
                    <label class="col-md-4 control-label">Company's Account</label>
                    <div class="col-md-4 selectContainer">
                        <div class="input-group">
                            <span class="input-group-addon"></span>
                            <select name="company_id" id="company_id" class="form-control selectpicker" required>
                                <option value="">Select Company</option>
                                <?php
                                try {
                                    $sql = "SELECT u.accId, u.name 
                                            FROM ccc_users u, clients c 
                                            WHERE c.client_accId = u.accId 
                                            AND c.client_type = 'COMPANY';";

                                    // list with all companies
                                    $result = $db->query($sql);
                                    $rows = mysqli_num_rows($result);
                                    $companies = $result->fetch_all(MYSQLI_BOTH);

                                    for ($i = 0; $i < $rows; $i++) {
                                        echo "<option style='font-weight:bold;' value='" . $companies[$i]["accId"] . "'>" . $companies[$i]["accId"] . " - " . $companies[$i]["name"] . "</option>";
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

                <div class="form-group" id="emp_name_div">
                    <label class="col-md-4 control-label">Employee's Name</label>
                    <div class="col-md-4 inputGroupContainer">
                        <div class="input-group">
                            <span class="input-group-addon"></span>
                            <input type="text" name="emp_name" id="emp_name" placeholder="Employee's Name" class="form-control" required>
                        </div>
                    </div>
                </div>
            </fieldset>
        <?php } ?>
        <!-- submit button -->
        <div class="form-group submit_btn_div">
            <div class="col-md-4">
                <button type="submit" class="btn btn-success">Register <span class="fa fa-send"></span></button>
            </div>
        </div>
    <?php } ?>
<?php }
$db->close();
?>