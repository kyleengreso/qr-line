<?php

// Local DB
$servername = "127.0.0.1";
$username = "root";
$password = "root";
$dbname = "qr_queue_system";
$port = 3306;

// PROD DB
// $servername = "77.37.35.51";
// $username = "u273960544_qrliners";
// $password = "QRlin3rs*";
// $dbname = "u273960544_qrline";


try {
    // Create a new MySQLi instance with SSL options
    $conn = new mysqli($servername, $username, $password, $dbname, $port);

    if (!$conn->real_connect($servername, $username, $password, $dbname, $port, null, MYSQLI_CLIENT_SSL)) {
        die("Connection failed: " . $conn->connect_error);
    }

    $conn->set_charset("utf8mb4");

    // echo "Connected successfully with SSL!";
} catch (mysqli_sql_exception $e) {
    die("Unable to connect to the database: " . $e->getMessage());
}

?>  