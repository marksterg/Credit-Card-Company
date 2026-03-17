<?php

/**
 * utils.php
 * some useful functions that we use in different files in this project
 */

/**
 * function returns the row number of an SQL Query given a specific id
 * @param mysqli_result $result SQL query result object (mysqli_result object)
 * @param mixed $id specific id (key)
 * @return array returns the row number given a specific id
 */
function get_rownum_from_result($result, $id)
{

    foreach ($result as $row => $col) {
        if (is_array($id)) {
            // SPECIAL CASE: for transaction type CREDIT we need the whole primary key (TID,trans_type)
            if (($col['id'] == $id[0]) && ($col['type'] == $id[1]))
                return $row;
        } else {
            // client, seller, user
            if ($col['id'] == $id)
                return $row;
        }
    }

    return NULL;
}

/**
 * fucntion that filter the input that user gives
 * @param string $data any input data given by the user
 * @return mixed returns filtered data
 */
function filter($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 *  function that shows the data inside a table element
 * @param mysqli_result $data sql query result object (mysqli_result object)
 * @param array $color_row optional argument which defines when to change the color of the row that has been modified
 * @return string returns a table which has depicted the final results
 */
function display_data($data, $color_row = null, $som_array = null)
{
    $output = "<table class='table compact cell-border hover'>";

    if (is_a($data, 'mysqli_result')) {
        // columns
        $output .= "<thead><tr>";
        $cols = mysqli_fetch_fields($data);
        for ($i = 0; $i < count($cols); $i++)
            $output .= "<th><center><strong>" . $cols[$i]->name . "</strong></center></th>";
        $output .= "</tr></thead><tbody>";

        // data
        foreach ($data as $row => $col) {

            if (isset($color_row) && $color_row == $row) $output .= "<tr style='background-color: #64cc4a;'>";
            else $output .= "<tr>";

            foreach ($col as $c => $d)
                $output .= "<td><center>" . $d . "</center></td>";
            $output .= "</tr>";
        }
    } elseif (isset($som_array) && is_array($som_array)) {
        // SPECIAL CASE: display data of seller of the month

        // columns
        $output .= "<thead><tr>";
        $cols = $som_array["cols"];
        foreach ($cols as $key => $value)
            $output .= "<th><center><strong>" . $value->name . "</strong></center></th>";
        $output .= "</tr></thead><tbody>";

        // data
        $data = $som_array["data"];
        $output .= "<tr>";
        foreach ($data as $row) {
            foreach ($row as $key => $val) {
                if (!is_string($key))
                    $output .= "<td><center>" . $row[$key] . "</center></td>";
            }
        }
        $output .= "</tr>";
    }

    $output .= "</tbody></table>";

    return $output;
}

/**
 * function for validation check for the form fields in post requests
 * @param array $required the IDs of the required fields in form of an array 
 * @return bool returns a boolean value if the required fields are empty or not
 */
function check_fields($required)
{
    foreach ($required as $field) {
        if (!isset($_POST[$field]) || $_POST[$field] == "") {
            return false;
        }
    }
    return true;
}
