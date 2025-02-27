<?php
session_start();
include "./../includes/db_conn.php";
include "./../base.php";
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    header("Content-Type: application/json");
    $data = json_decode(file_get_contents("php://input"));

    if (!isset($data->username) || !isset($data->password) || !isset($data->auth_method)) {
        echo json_encode(array(
            "status" => "error",
            "message" => "Username, password, and auth_method are required"));
        exit;
    }

    $username = $data->username;
    $password = $data->password;
    $auth_method = $data->auth_method;

    if ($auth_method == "login") {

        if (!isset($data->username) || !isset($data->password)) {
            echo json_encode(array(
                "status" => "error",
                "message" => "Username and password are required"));
            exit;
        }

        $stmt = $conn->prepare("SELECT username, password FROM employees WHERE username = ?");
        $stmt->bind_param("s", $username);

        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['employee_id'] = $user['username'];

                // Generate token by using username and date and date expired
                $token = base64_encode($username . ':' . date('Y-m-d H:i:s') . ':' . date('Y-m-d H:i:s', strtotime('+1 day')));

                echo json_encode(array(
                    "status" => "success",
                    "message" => "User authenticated",
                    "token" => $token
                ));
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

    } else if ($auth_method == "register") {

        $confirm_password = $data->confirm_password;

        if ($password != $confirm_password) {
            echo json_encode(array(
                "status" => "error",
                "message" => "Passwords do not match"));
            exit;
        }

        $stmt = $conn->prepare("SELECT username FROM employees WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo json_encode(array(
                "status" => "error",
                "message" => "Username already exists"));
            exit;
        } else {
            $hash_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO employees (username, password) VALUES (?, ?)");
            $stmt->bind_param("ss", $username, $hash_password);

            if ($stmt->execute()) {
                echo json_encode(array(
                    "status" => "success",
                    "message" => "Employee registered successfully"));
            } else {
                echo json_encode(array(
                    "status" => "error",
                    "message" => "Error: " . $conn->error));
            }
        }
    }
}