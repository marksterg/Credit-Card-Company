# Credit-Card-Company

![Screenshot](index.jpg)

## About

This project was developed for a university course and involves creating a web application simulating Client-Seller payments and providing useful statistics. Core of this project is the design of a relational model using **MySQL** database to store and maintain all the transactions.

## Database ER Model

![Screenshot](er.png)

## Setup (Windows)

- Setup **XAMPP**: <https://www.apachefriends.org/faq_windows.html>

## Run

1. Copy the `ccc/` folder into `{XAMPP_FOLDER}/htdocs/`
2. Open XAMPP Control Panel as **Administrator**.
3. `Start` the **Apache** and **MySQL** services.
4. Open: `localhost/ccc/index.php`
   - **Routes/Pages**
     - `/index.php` -> main page
     - `/account.php` -> register / close account
     - `/transactions.php` -> buy / refund transactions, payoff procedure
     - `/queries.php` -> status reports for transactions
     - `/info.php` -> state of good clients / bad clients / seller of the month

5. (**Optional**) You can view and interact with the database through `http://localhost/phpmyadmin/index.php` page.

## Misc

For more details about the project structure see the PDF [report](report.pdf) file.

- **Sample Versions**:

    ```notes
    XAMPP v3.3.0
    PHP v8.2.12
    MySQL (MariaDB) v10.4.32
    ```
