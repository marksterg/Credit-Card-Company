<?php
/**
 * config.php
 * used for database connection
 */

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

class DB {
    private $DB_HOST; // host's name
    private $DB_USERNAME; // connection username
    private $DB_PASSWORD; // connection password
    private $DB_NAME; // database name

    private $conn;
    private $db;

    /**
     * override main constructor
     */
    function __construct() {
        $this->DB_HOST = "localhost";
        $this->DB_USERNAME = "root";
        $this->DB_PASSWORD = "";
        $this->DB_NAME = "hy360";
    }

    /** 
     * Server's (host) name
    */
    function get_db_host() {
        return $this->DB_HOST;
    }

    /**
     * Database connection username
     */
    function get_db_username() {
        return $this->DB_USERNAME;
    }

    /**
     * Database connection password
     */
    function get_db_password() {
        return $this->DB_PASSWORD;
    }

    /** 
     * Database name
    */
    function get_db_name(){
        return $this->DB_NAME;
    }

    /** 
     * getting connection with the server for DB initialization
    */
    function get_server_connection() {
        try {
            $this->conn = mysqli_connect($this->DB_HOST, $this->DB_USERNAME, $this->DB_PASSWORD);
        } catch (mysqli_sql_exception $e) {
            return NULL;
        }
        return $this->conn;
    }

    /** 
     * getting connection with a specific DB
    */
    function get_db_connection() {
        try {
            $this->db = mysqli_connect($this->DB_HOST, $this->DB_USERNAME, $this->DB_PASSWORD, $this->DB_NAME);
        } catch (mysqli_sql_exception $e) {
            return NULL;
        }
        return $this->db;
    }

}

?>