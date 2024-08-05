<?php
/**
 * dropDB.php
 * used for deleting the database
 */

require '../db/config.php';

// start the (already existed) session data
session_start();
// delete the (already existed) session data
session_destroy();

$dbo = new DB();

$conn = $dbo->get_server_connection();

if ($conn == NULL) {
    die("Connection failed");
}

if ($conn->query("DROP DATABASE IF EXISTS " . $dbo->get_db_name()) === FALSE)
    die("Error deleting database: " . $conn->connect_error);

$conn->close();

header("location: ../index.php");
exit;
?>