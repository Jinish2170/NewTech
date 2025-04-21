<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $servername = "localhost";
    $username = "root";
    $password = "Secure123!";
    $dbname = "bigtechtimes";

    $conn = new mysqli($servername, $username, $password, $dbname);
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    error_log($e->getMessage(), 3, __DIR__ . '/../logs/db_errors.log');
    die('Database connection failed.');
}
