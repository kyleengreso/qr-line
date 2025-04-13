<?php

$servername = "127.0.0.1";
$username = "root";
$password = "root";
$dbname = "qr_queue_system";

// Specify the socket file explicitly
// $socket = "/var/run/mysqld/mysqld.sock"; // Modify this path to match your system's MySQL socket location

$conn = new mysqli($servername, $username, $password, $dbname, 3306);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>