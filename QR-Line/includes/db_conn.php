<?php

// MySQL Server
$servername = "localhost";
$username = "root";
$password = "root";

$dbname = "qr_queue_system";


$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
