<?php
session_start();
include "./../includes/db_conn.php";
include "./../base.php";
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Usage
    // curl -X POST -H "Content-Type: application/json" -d "{\"username\":\"testuser\", \"password\":\"password123\"}" http://localhost/QR-Line/api/api_authenicate.php

    header("Content-Type: application/json");
    // $data = json_decode(file_get_contents("php://input"));
    $data = json_decode(file_get_contents("php://input"));
    if (!isset($data->username) || !isset($data->password)) {
        echo json_encode(array(
            "status" => "error",
            "message" => "Username and password are required"));
        exit;
    } 

    $username = $data->username;
    $password = $data->password;

    // echo $username;
    // echo $password;

    $stmt = $conn->prepare("SELECT username, password FROM employees WHERE username = ?");
    $stmt->bind_param("s", $username);

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['employee_id'] = $user['username'];
            echo json_encode(array(
                "status" => "success",
                "message" => "User authenticated"));
        } else {
            echo json_encode(array(
                "status" => "error",
                "message" => "Invalid password"));
        }
    } else {
        echo json_encode(array(
            "status" => "error",
            "message" => "Invalid username or password"));
    }
    // echo "TES";
    // 
}