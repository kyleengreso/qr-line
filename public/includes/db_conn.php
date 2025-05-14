<?php

$servername = "127.0.0.1";
$username = "root";
$password = "root";
$dbname = "qr_queue_system";

// PROD DB
// $servername = "77.37.35.51";
// $username = "u273960544_qrliners";
// $password = "QRlin3rs*";
// $dbname = "u273960544_qrline";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($servername, $username, $password, $dbname, 3306);
    // Set charset if needed
    $conn->set_charset("utf8mb4");
} catch (mysqli_sql_exception $e) {
    die("Unable to connect to the database or wrong password. Please try again later");
}

?>