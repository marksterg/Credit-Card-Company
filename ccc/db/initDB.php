<?php
/**
 * initDB.php
 * used for creating and initializing the database
 * inserting at least 5 records on each table
 */

require '../db/config.php';

// database object
$dbo = new DB();

// first connection with the database server
$conn = $dbo->get_server_connection();
if ($conn === NULL)
    die("Connection failed.");

// if the database exists drop it and recreate it
$conn->query("DROP DATABASE IF EXISTS " . $dbo->get_db_name());

// creation of the database
if ($conn->query("CREATE DATABASE IF NOT EXISTS " . $dbo->get_db_name()) === FALSE) {
    die("Error creating database: " . $conn->connect_error);
    $conn->close();
}

// select the database
$db = $dbo->get_db_connection();
if ($db == NULL)
    die("Connection with DB failed.");

try {

    /* ------------------------ CREATE TABLES ------------------------ */

    /* CCC_USERS */
    $db->query("CREATE TABLE CCC_USERS (
        accId INT AUTO_INCREMENT NOT NULL,
        name VARCHAR(30) NOT NULL,
        PRIMARY KEY (accId)
    )");

    /* CLIENTS */
    $db->query("CREATE TABLE CLIENTS (
        client_accId INT NOT NULL,
        exp_date DATE NOT NULL,
        credit_limit DECIMAL(10, 2) NOT NULL,
        credit_debt DECIMAL(10, 2) NOT NULL,
        credit_balance DECIMAL(10, 2) NOT NULL,
        client_type VARCHAR(10) NOT NULL,
        PRIMARY KEY (client_accId),
        FOREIGN KEY (client_accId) REFERENCES CCC_USERS(accId) ON UPDATE CASCADE ON DELETE CASCADE
    )");

    /* SELLERS */
    $db->query("CREATE TABLE SELLERS (
        seller_accId INT NOT NULL,
        commission DECIMAL(10,3) NOT NULL,
        total_profit DECIMAL(10,2) NOT NULL,
        debt DECIMAL(10,2) NOT NULL,
        PRIMARY KEY (seller_accId),
        FOREIGN KEY (seller_accId) REFERENCES CCC_USERS(accId) ON UPDATE CASCADE ON DELETE CASCADE
    )");

    /* COMPANY_EMPLOYEES */
    $db->query("CREATE TABLE COMPANY_EMPLOYEES (
        empId INT NOT NULL,
        emp_name VARCHAR(30) NOT NULL,
        client_accId INT NOT NULL,
        PRIMARY KEY (empId, client_accId),
        FOREIGN KEY (client_accId) REFERENCES CLIENTS(client_accId) ON UPDATE CASCADE ON DELETE CASCADE
    )");

    /* TRANSACTIONS */
    $db->query("CREATE TABLE TRANSACTIONS (
        TID INT AUTO_INCREMENT NOT NULL,
        amount DECIMAL(10, 2) NOT NULL,
        trans_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        trans_type VARCHAR(6) NOT NULL,
        client_name VARCHAR(30) NOT NULL,
        seller_name VARCHAR(30) NOT NULL,
        client_accId INT NOT NULL,
        seller_accId INT NOT NULL,
        empId INT,
        PRIMARY KEY (TID, trans_type)
    )");

    /* ----------------------- INSERT DATA ----------------------- */

    /* insert CCC_USERS */
    $db->query("INSERT INTO CCC_USERS (name) 
    VALUES
    ('individual1'),
    ('individual2'),
    ('individual3'),
    ('individual4'),
    ('individual5'),
    ('company1'),
    ('company2'),
    ('company3'),
    ('company4'),
    ('company5'),
    ('seller1'),
    ('seller2'),
    ('seller3'),
    ('seller4'),
    ('seller5')
    ");

    /* insert CLIENTS */
    $db->query("INSERT INTO CLIENTS (client_accId, exp_date, credit_limit, credit_debt, credit_balance, client_type)
    VALUES
    (1,  '2022-11-21',   1000, 200,   800, 'INDIVIDUAL'),
    (2,  '2022-05-14',   1000, 600,   400, 'INDIVIDUAL'),
    (3,  '2022-05-29',   1000,   0,  1000, 'INDIVIDUAL'),
    (4,  '2022-10-14',   1000,   0,  1000, 'INDIVIDUAL'),
    (5,  '2021-05-12',   1000,   0,  1000, 'INDIVIDUAL'),
    (6,  '2022-11-21',  10000,   0,  1000,    'COMPANY'),
    (7,  '2022-05-14',  10000, 600,  9400,    'COMPANY'),
    (8,  '2022-05-29',  10000,   0, 10000,    'COMPANY'),
    (9,  '2022-10-14',  10000, 800,  9200,    'COMPANY'),
    (10, '2022-05-12',  10000,   0, 10000,    'COMPANY')
    ");

    /* insert SELLERS */
    $db->query("INSERT INTO SELLERS (seller_accId, commission, total_profit, debt)
    VALUES
    (11, 0.005,  398,  2),
    (12, 0.005,    0,  0),
    (13, 0.005,  199,  1),
    (14, 0.005, 1592,  8),
    (15, 0.005,    0,  0)
    ");

    /* insert COMPANY_EMPLOYEES */
    $db->query("INSERT INTO COMPANY_EMPLOYEES (empId, emp_name, client_accId)
    VALUES
    (61,  'employee1',  6),
    (62,  'employee2',  6),
    (63,  'employee3',  6),
    (71,  'employee1',  7),
    (72,  'employee2',  7),
    (73,  'employee3',  7),
    (81,  'employee1',  8),
    (82,  'employee2',  8),
    (83,  'employee3',  8),
    (91,  'employee1',  9),
    (92,  'employee2',  9),
    (93,  'employee3',  9),
    (101, 'employee1', 10),
    (102, 'employee2', 10),
    (103, 'employee3', 10)
    ");

    /* insert TRANSACTIONS */
    $db->query("INSERT INTO TRANSACTIONS (amount, trans_date, trans_type, client_name, seller_name, client_accId, seller_accId, empId)
    VALUES
    (200, '2021-12-15 16:05:00', 'DEBIT', 'individual1', 'seller4', 1, 14, 0),
    (600, '2021-12-15 16:10:00', 'DEBIT', 'individual2', 'seller4', 2, 14, 0),
    (200, '2021-12-15 16:15:00', 'DEBIT', 'company2',    'seller3', 7, 13, 71),
    (800, '2022-01-15 18:20:00', 'DEBIT', 'company4',    'seller4', 9, 14, 93),
    (400, '2022-01-15 18:25:00', 'DEBIT', 'company2',    'seller1', 7, 11, 71)
    ");

    /* ----------------------- VIEWS ----------------------- */

    /* trans_data view */
    $db->query("CREATE VIEW trans_data AS
    SELECT t.TID, t.trans_type, t.trans_date, t.amount, t.client_accId, t.client_name, t.seller_accId, t.seller_name, ce.empId, ce.emp_name
    FROM transactions t
    LEFT JOIN company_employees ce ON ce.empId = t.empId
    ORDER BY t.trans_date ASC;");

    /* user_debts view */
    $db->query("CREATE VIEW user_debts AS 
    SELECT * FROM 
    ((SELECT u.accId, u.name, c.credit_debt AS 'debt'
    FROM ccc_users u, clients c
    WHERE u.accId = c.client_accId)
    UNION
    (SELECT u.accId, u.name, s.debt AS 'debt'
    FROM ccc_users u, sellers s
    WHERE u.accId = s.seller_accId)) t;");

    /* seller_of_month view */
    $db->query("CREATE VIEW seller_of_month AS
    SELECT u.accId AS 'AccountID', u.name AS 'Seller Name', m.purch AS 'Purchases', s1.total_profit AS 'Previous Profit', ROUND(s2.total_profit + (s2.debt*0.05),2) AS 'New Profit', s1.debt AS 'Previous Debt', ROUND((s2.debt-(s2.debt*0.05)),2) AS 'New Debt (-5%)'
    FROM ccc_users u, sellers s1, sellers s2,
        (SELECT p.sId AS 'sId', p.purch AS 'purch' FROM
            (SELECT t.seller_accId AS 'sId', COUNT(*) AS 'purch'
            FROM transactions t
            WHERE MONTH(t.trans_date) = MONTH(CURRENT_DATE - INTERVAL 1 MONTH)
            GROUP BY t.seller_accId 
            ORDER BY purch DESC 
            LIMIT 1) p) m
    WHERE u.accId = s1.seller_accId AND s1.seller_accId = m.sId AND s1.seller_accId = s2.seller_accId;");

    /* transaction_clients_info view */
    $db->query("CREATE VIEW trans_clients_info AS
    SELECT DISTINCT uc.accId AS 'AccountID', uc.name AS 'ClientName', c.credit_limit AS 'ClientLimit', c.credit_balance AS 'ClientBalance', c.credit_debt AS 'ClientDebt'
    FROM ccc_users uc, clients c, transactions t 
    WHERE uc.accId = c.client_accId
    AND c.client_accId = t.client_accId
    ORDER BY uc.accId;");

    /* transaction_sellers_info view */
    $db->query("CREATE VIEW trans_sellers_info AS
    SELECT DISTINCT us.accId AS 'AccountID', us.name AS 'SellerName', s.commission AS 'SellerCom', s.total_profit AS 'SellerProfit', s.debt AS 'SellerDebt'
    FROM ccc_users us, sellers s, transactions t
    WHERE us.accId = s.seller_accId
    AND s.seller_accId = t.seller_accId
    ORDER BY us.accId;");

} catch (Exception $e) {
    $conn->query("DROP DATABASE IF EXISTS " . $dbo->get_db_name());
    $conn->close();
    $db->close();
    die("Error in SQL Query: " . $e);
}
?>

<?php
$conn->close();
$db->close();
header("location: ../index.php");
exit;
?>