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

// PROD DB 2
    // $servername = "qrliners-qrline.k.aivencloud.com";
    // $username = "prod";
    // $password = "AVNS_3m8N9PgsxvAYmyKK6sh";
    // $dbname = "qr_queue_system";
    // $port = 22410;
    // $ca_cert = __DIR__ . "/ca.pem";
// $ssl_options = [
//     MYSQLI_OPT_SSL_VERIFY_SERVER_CERT => true,
//     MYSQLI_OPT_SSL_CA => $ca_cert,
// ];

try {
    // Create a new MySQLi instance with SSL options
    $conn = new mysqli($servername, $username, $password, $dbname, $port);

    // $conn->ssl_set(null, null, $ca_cert, null, null);

    if (!$conn->real_connect($servername, $username, $password, $dbname, $port, null, MYSQLI_CLIENT_SSL)) {
        die("Connection failed: " . $conn->connect_error);
    }

    $conn->set_charset("utf8mb4");

    // echo "Connected successfully with SSL!";
} catch (mysqli_sql_exception $e) {
    die("Unable to connect to the database: " . $e->getMessage());
}

?>  