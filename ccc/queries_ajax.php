<?php
/**
 * queries_ajax.php
 * 
 * Handles ajax requests.
 * Return html code as response and fill in specific divs (dropdown menus)
 */

require 'db/config.php';
require 'inc/utils.php';

// open db connection
$db = (new DB())->get_db_connection();

// REQUEST PROCESS
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // IF THE SELECTED CLIENT IS COMPANY THEN RETURN EMPLOYEE DATA TO FILL THE SPECIFIC DROPDOWN MENU LIST
    if (isset($_POST["company_selection"])) {
        // selected company id
        $company_id = $_POST["company_selection"];
        try {
            $sql = "SELECT ce.empId, ce.emp_name, u.name
                FROM company_employees ce, clients c, ccc_users u
                WHERE ce.client_accId = $company_id
                AND ce.client_accId = c.client_accId
                AND c.client_accId = u.accId
                AND c.client_type = 'COMPANY';";

            // list with all employees
            $result = $db->query($sql);
            $rows = mysqli_num_rows($result);
            $company_emp = $result->fetch_all(MYSQLI_BOTH);

            if ($rows) {

                echo "<option value='' disabled selected>Select Employee(s)</option>
                <option value='none' style='font-weight:bold;'>NONE</option>";

                for ($i = 0; $i < $rows; $i++) {
                    echo "<option style='font-weight:bold;' value='" . $company_emp[$i]["empId"] . "'>" . $company_emp[$i]["empId"] . " - " . $company_emp[$i]["emp_name"] . "</option>";
                }
            }
        } catch (mysqli_sql_exception $e) {
            $db->close();
            die($e);
        }
    }
}
$db->close();
