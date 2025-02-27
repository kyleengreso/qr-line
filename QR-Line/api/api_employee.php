<?php
session_start();
include "./../includes/db_conn.php";
include "./../asset/php/message.php";

$response = array();
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password != $confirm_password) {
        $response['status'] = 'error';
        $response['message'] = "Password does not match.";
    } else {

        // Load if user is exists
        $stmt = $conn->prepare("SELECT id FROM employees WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $response['status'] = 'error';
            $response['message'] = "Username already exists.";
        } else {
            $hash_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO employees (username, password) VALUES (?, ?)");
            $stmt->bind_param("ss", $username, $password);

            if ($stmt->execute()) {
                $response['status'] = 'success';
                $response['message'] = "Employee registered successfully!";
            } else {
                $response['status'] = 'error';
                $response['message'] = "Error: " . $conn->error;
            }
        }
    }
} else if ($_SERVER["REQUEST_METHOD"] == "GET") {

    // GET all list from employee/s
    if (isset($_GET['search'])) {
        $search = "%" . $_GET['search'] . "%";
        $stmt = $conn->prepare("SELECT id, username, created_at FROM employees WHERE username LIKE ?");
        $stmt->bind_param("s", $search);
        $stmt->execute();
        $result = $stmt->get_result();
        $employees = $result->fetch_all(MYSQLI_ASSOC);
    } else if (isset($_GET['id'])) {
        $id = $_GET['id'];
        $stmt = $conn->prepare("SELECT id, username, created_at FROM employees WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $employees = $result->fetch_all(MYSQLI_ASSOC);
    } else {
        $stmt = $conn->prepare("SELECT id, username, created_at FROM employees");
        $stmt->execute();
        $result = $stmt->get_result();
        $employees = $result->fetch_all(MYSQLI_ASSOC);
    }

    if ($result->num_rows > 0) {
        $response['status'] = 'success';
        $response['data'] = $employees;
        $response['message'] = "Employees found.";
    } else {
        $response['status'] = 'success';
        $response['data'] = [];
        $response['message'] = "No employees found.";
    }
}

echo json_encode($response);
?>