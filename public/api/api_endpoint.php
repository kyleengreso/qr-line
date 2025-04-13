<?php
// QR-LINE Endpoint v2
// (c) aceday. All Rights Reserved 2025

require_once __DIR__ . '/./../base.php';
require_once __DIR__ . '/./../includes/system_auth.php';

header("Content-Type: application/json");

global $conn;

if (!$conn) {
    echo json_encode(array(
        "status" => "error",
        "message" => "Database connection error"));
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // DATA JSON
    $data = json_decode(file_get_contents("php://input"));
    
    if (!isset($data->method)) {
        echo json_encode(array(
            "status" => "error",
            "message" => "Invalid request"
        ));
        exit;
    }

    $method = $data->method;
    if ($method == "logout") {
        // Check token from cookie
        if (isset($_COOKIE['token'])) {
            $token = $_COOKIE['token'];
            $decToken = decryptToken($token, $master_key);
            $web = false;
            if ($decToken) {
                // Delete the cookie
                setcookie("token", "", time() - 3600, "/");
                echo json_encode(array(
                    "status" => "success",
                    "message" => "Logout successful"
                ));
                if ($web) {
                    header("Location: " . "/../auth/login.php");
                }
                exit;
            } else {
                echo json_encode(array(
                    "status" => "error",
                    "message" => "Invalid token"
                ));
                exit;
            }
        } else {
            echo json_encode(array(
                "status" => "error",
                "message" => "No token found"
            ));
            exit;
        }
    } else if ($method == "login") {
        if (!isset($data->username) || !isset($data->password)) {
            echo json_encode(array(
                "status" => "error",
                "message" => "Input username and password!"
            ));
            exit;
        }
        $username = $data->username;
        $password = $data->password;

        $sql_cmd = "SELECT e.id, e.username, e.password, e.active, e.role_type, e.email
                    FROM employees e
                    WHERE e.username = ? OR e.email = ?";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $employee = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        if ($result->num_rows == 0) {
            echo json_encode(array(
                "status" => "error",
                "message" => "Username or Password is invalid"
            ));
            exit;
        }
        if ($employee[0]['active'] == 0) {
            echo json_encode(array(
                "status" => "error",
                "message" => "Account is deactivated"
            ));
            exit;
        }
        if (password_verify($password, $employee[0]['password'])) {
            
            // Get Counter if was assigned
            $sql_cmd = "SELECT c.counterNumber
                        FROM counters c
                        WHERE c.idemployee = ?";
            $stmt = $conn->prepare($sql_cmd);
            $stmt->bind_param("s", $employee[0]['id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $counter = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            $token = array(
                "id" => $employee[0]['id'],
                "username" => $employee[0]['username'],
                "role_type" => $employee[0]['role_type'],
                "email" => $employee[0]['email'],
                "counterNumber" => $counter[0]['counterNumber'] ?? null,
            );
            $encToken = encryptToken($token, $master_key);
            setcookie("token", $encToken, time() + (86400 * 30), "/");

            echo json_encode(array(
                "status" => "success",
                "message" => "Login successful",
                "data" => $token
            ));
            exit;
        } else {
            // Attempt login
            $sql_cmd = "SELECT setup_value_int
                        FROM setup_system
                        WHERE setup_key = 'attempt_login'";
            $stmt = $conn->prepare($sql_cmd);
            $stmt->execute();
            $result = $stmt->get_result();
            $setup = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            $attempt_set = $setup[0]['setup_value_int'];

            $sql_cmd = "SELECT attempt_login
                        FROM employees
                        WHERE username = ? or email = ?";
            $stmt = $conn->prepare($sql_cmd);
            $stmt->bind_param("ss", $username, $username);
            $stmt->execute();
            $result = $stmt->get_result();
            $attempt = $result->fetch_all(MYSQLI_ASSOC)[0]['attempt_login'];
            $stmt->close();
            if ($attempt < $attempt_set) {
                $attempt = $attempt + 1;
                $sql_cmd = "UPDATE employees
                            SET attempt_login = ?
                            WHERE username = ? or email = ?";
                $stmt = $conn->prepare($sql_cmd);
                $stmt->bind_param("sss", $attempt, $username, $username);
                $stmt->execute();
                $stmt->close();
                echo json_encode(array(
                    "status" => "error",
                    "attempt" => $attempt,
                    "message" => "Username or Password is invalid"
                ));
                exit;
            } else {
                // Deactivate
                $sql_cmd = "UPDATE employees
                            SET active = 0
                            WHERE username = ? or email = ?";
                $stmt = $conn->prepare($sql_cmd);
                $stmt->bind_param("ss", $username, $username);
                $stmt->execute();
                $stmt->close();
                echo json_encode(array(
                    "status" => "error",
                    "message" => "Account is deactivated"
                ));
                exit;
            }
            echo json_encode(array(
                "status" => "error",
                "message" => "Username or Password is invalid"
            ));
            exit;
        }
    // REGISTER
    } else if ($method == "register") {
        if (!isset($data->username) || !isset($data->password) || !isset($data->email)) {
            echo json_encode(array(
                "status" => "error",
                "message" => "Input username, password and email!"
            ));
            exit;
        }
        $username = $data->username;
        $password = $data->password;
        $email = $data->email;

        $sql_cmd = "SELECT e.id
                    FROM employees e
                    WHERE e.username = ? OR e.email = ?";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $employee = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        if ($result->num_rows > 0) {
            echo json_encode(array(
                "status" => "error",
                "message" => "Username or Email already exists"
            ));
            exit;
        } else {
            $hash_password = password_hash($password, PASSWORD_DEFAULT);
            $sql_cmd = "INSERT INTO employees (username, password, email) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql_cmd);
            $stmt->bind_param("sss", $username, $hash_password, $email);
            if ($stmt->execute()) {
                echo json_encode(array(
                    "status" => "success",
                    "message" => "Employee registered successfully"
                ));
                exit;
            } else {
                echo json_encode(array(
                    "status" => "success",
                    "message" => "Error: " . $conn->error
                ));
                exit;
            }
        }

        exit;
    
    // EMPLOYEES
    } else if ($method == "employees-add") {
        if (!isset($data->username) || !isset($data->password) || !isset($data->email) || !isset($data->role_type)) {
            echo json_encode(array(
                "status" => "error",
                "message" => "Input username, password, email and role type!"
            ));
            exit;
        }
        $username = $data->username;
        $password = $data->password;
        $email = $data->email;
        $role_type = $data->role_type;

        $sql_cmd = "SELECT e.id
                    FROM employees e
                    WHERE e.username = ?";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $employee = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        if ($result->num_rows > 0) {
            echo json_encode(array(
                "status" => "error",
                "message" => "Username or Email already exists"
            ));
            exit;
        }
        $hash_password = password_hash($password, PASSWORD_DEFAULT);
        $sql_cmd = "INSERT INTO employees (username, password, email, role_type) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->bind_param("ssss", $username, $hash_password, $email, $role_type);
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            // Activate the employee
            if (isset($data->active) && $data->active == 1) {
                $sql_cmd = "UPDATE employees
                            SET active = 1
                            WHERE username = ?";
                $stmt = $conn->prepare($sql_cmd);
                $stmt->bind_param("s", $username);
                $stmt->execute();
                $stmt->close();
            }
            echo json_encode(array(
                "status" => "success",
                "message" => "Employee registered successfully"
            ));
            exit;
        } else {
            echo json_encode(array(
                "status" => "error",
                "message" => "Error: " . $conn->error
            ));
            exit;
        }
        exit;
    } else if ($method == "employees-update") {
        if (!isset($data->id) || !isset($data->username) || !isset($data->password) || !isset($data->email) || !isset($data->role_type) || !isset($data->active)) {
            echo json_encode(array(
                "status" => "error",
                "message" => "Input id, username, email and role type!"
            ));
            exit;
        }

        $id = $data->id;
        $username = $data->username;
        $password = $data->password;
        $email = $data->email;
        $role_type = $data->role_type;
        $active = $data->active;

        // Check if the username is already exists
        $sql_cmd = "SELECT e.id
                    FROM employees e
                    WHERE e.username = ? AND e.id != ?";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->bind_param("ss", $username, $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $employee = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        if ($result->num_rows > 0) {
            echo json_encode(array(
                "status" => "error",
                "message" => "Username already exists"
            ));
            exit;
        }

        // Check if the email is already exists
        $sql_cmd = "SELECT e.id
                    FROM employees e
                    WHERE e.email = ? AND e.id != ?";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->bind_param("ss", $email, $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $employee = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        if ($result->num_rows > 0) {
            echo json_encode(array(
                "status" => "error",
                "message" => "Email already exists"
            ));
            exit;
        }

        // Check role_type
        $role_type_accept = array('admin', 'employee');
        if (!in_array($role_type, $role_type_accept)) {
            echo json_encode(array(
                "status" => "error",
                "message" => "Invalid role type"
            ));
            exit;
        }

        // Check active
        $active_accept = array(0, 1);
        if (!in_array($active, $active_accept)) {
            echo json_encode(array(
                "status" => "error",
                "message" => "Invalid active status"
            ));
            exit;
        }

        // Update the employee

        $sql_cmd = "UPDATE employees SET ";
        
        $params = [];
        $types = "";
        
        if (strlen($username) > 0) {
            $sql_cmd .= "username = ?,";
            $params[] = $username;
            $types .= "s";
        }
        if (strlen($password) > 0) {
            $sql_cmd .= "password = ?,";
            $params[] = password_hash($password, PASSWORD_DEFAULT);
            $types .= "s";
        }
        if (strlen($email) > 0) {
            $sql_cmd .= "email = ?,";
            $params[] = $email;
            $types .= "s";
        }
        $sql_cmd .= "role_type = ?, active = ?
                    WHERE id = ?";
        $params[] = $role_type;
        $params[] = $active;
        $params[] = $id;
        $types .= "sss";
        $sql_cmd = rtrim($sql_cmd, ",");

        $stmt = $conn->prepare($sql_cmd);
        $stmt->bind_param($types, ...$params);

        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            echo json_encode(array(
                "status" => "success",
                "message" => "Employee updated successfully"
            ));
        } else if ($stmt->affected_rows == 0) {
            echo json_encode(array(
                "status" => "error",
                "message" => "No changes made"
            ));
        } else {
            echo json_encode(array(
                "status" => "error",
                "message" => "Error: " . $conn->error
            ));
        }
        exit;
    } else if ($method == "employees-delete") {
        if (!isset($data->id)) {
            echo json_encode(array(
                "status" => "error",
                "message" => "Input id!"
            ));
            exit;
        }
        $id = $data->id;
        $sql_cmd = "DELETE FROM employees WHERE id = ?";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->bind_param("s", $id);
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            echo json_encode(array(
                "status" => "success",
                "message" => "Employee deleted successfully"
            ));
        } else if ($stmt->affected_rows == 0) {
            echo json_encode(array(
                "status" => "error",
                "message" => "Employee is not found"
            ));
        } else {
            echo json_encode(array(
                "status" => "error",
                "message" => "Error: " . $conn->error
            ));
        }
        $stmt->close();
        exit;
    } else if ($method == "employees-reset") {
        if (!isset($data->id)) {
            echo json_encode(array(
                "status" => "error",
                "message" => "Input id!"
            ));
            exit;
        }
        $id = $data->id;
        $sql_cmd = "UPDATE employees SET password = ? WHERE id = ?";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->bind_param("ss", password_hash("password", PASSWORD_DEFAULT), $id);
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            echo json_encode(array(
                "status" => "success",
                "message" => "Employee password reset successfully"
            ));
        } else if ($stmt->affected_rows == 0) {
            echo json_encode(array(
                "status" => "error",
                "message" => "Employee not found"
            ));
        } else {
            echo json_encode(array(
                "status" => "error",
                "message" => "Error: " . $conn->error
            ));
        }
        $stmt->close();
        exit;
    
    // COUNTERS
    } else if ($method == "counter-add") {
        if (!isset($data->counterNumber) || !isset($data->idemployee)) {
            echo json_encode(array(
                "status" => "error",
                "message" => "Input counter number and employee id!"
            ));
            exit;
        }
        // check if only counterNumber is exist
        $sql_cmd = "SELECT * FROM counters WHERE counterNumber = ?";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->bind_param("s", $data->counterNumber);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        if ($result->num_rows > 0) {
            echo json_encode(array(
                "status" => "error",
                "message" => "Counter number already assigned"
            ));
            exit;
        }

        // check if only idemployee is exist
        $sql_cmd = "SELECT * FROM counters WHERE idemployee = ?";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->bind_param("s", $data->idemployee);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        if ($result->num_rows > 0) {
            echo json_encode(array(
                "status" => "error",
                "message" => "Employee already assigned to another counter"
            ));
            exit;
        }

        // check if exists in both
        $sql_cmd = "SELECT * FROM counters WHERE counterNumber = ? AND idemployee = ?";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->bind_param("ss", $data->counterNumber, $data->idemployee);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            echo json_encode(array(
                "status" => "error",
                "message" => "Counter number already registered"
            ));
            exit;
        }
        // Employee checking
        $sql_cmd = "SELECT * FROM employees WHERE id = ?";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->bind_param("s", $data->idemployee);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        if ($result->num_rows == 0) {
            echo json_encode(array(
                "status" => "error",
                "message" => "Employee not found"
            ));
            exit;
        }
        // Insert counter
        $sql_cmd = "INSERT INTO counters (idemployee, counterNumber) VALUES (?, ?)";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->bind_param("ss", $data->idemployee, $data->counterNumber);
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            echo json_encode(array(
                "status" => "success",
                "message" => "Counter registered successfully"
            ));
        } else {
            echo json_encode(array(
                "status" => "error",
                "message" => "Error: " . $conn->error
            ));
        }
    } else if ($method == "counters-update") {
        if (!isset($data->id) || !isset($data->counterNumber) || !isset($data->idemployee)) {
            echo json_encode(array(
                "status" => "error",
                "message" => "Input id, counter number and employee id!"
            ));
            exit;
        }
        $id = $data->id;                        // idcounter
        $counterNumber = $data->counterNumber;  // counterNumber
        $employee_id = $data->idemployee;       // idemployee

        // check if exists about idemployee
        $sql_cmd = "SELECT * FROM employees WHERE id = ?";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->bind_param("s", $employee_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        if ($result->num_rows == 0) {
            echo json_encode(array(
                "status" => "error",
                "message" => "Employee not found"
            ));
            exit;
        }

        // however how about for counter's table
        $sql_cmd = "SELECT * FROM counters WHERE idemployee = ? AND idcounter != ?";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->bind_param("ss", $employee_id, $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        if ($result->num_rows > 0) {
            echo json_encode(array(
                "status" => "error",
                "message" => "Employee already assigned to another counter"
            ));
            exit;
        }


        // check if the counterNumber was existed in the table
        $sql_cmd = "SELECT * FROM counters WHERE counterNumber = ? AND idcounter != ?";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->bind_param("ss", $counterNumber, $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        if ($result->num_rows > 0) {
            echo json_encode(array(
                "status" => "error",
                "message" => "Counter number already registered"
            ));
            exit;
        }

        // Update the counter
        $sql_cmd = "UPDATE counters SET idemployee = ?, counterNumber = ? WHERE idcounter = ?";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->bind_param("sss", $employee_id, $counterNumber, $id);
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            echo json_encode(array(
                "status" => "success",
                "message" => "Counter updated successfully"
            ));
        } else if ($stmt->affected_rows == 0) {
            echo json_encode(array(
                "status" => "error",
                "message" => "No changes made"
            ));
        } else {
            echo json_encode(array(
                "status" => "error",
                "message" => "Error: " . $conn->error
            ));
        }
        $stmt->close();
        exit;
    
    } else if ($method == "counters-delete") {
        if (!isset($data->id)) {
            echo json_encode(array(
                "status" => "error",
                "message" => "Input id!"
            ));
            exit;
        }
        $id = $data->id;
        $sql_cmd = "DELETE FROM counters WHERE idcounter = ?";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->bind_param("s", $id);
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            echo json_encode(array(
                "status" => "success",
                "message" => "Counter deleted successfully"
            ));
        } else if ($stmt->affected_rows == 0) {
            echo json_encode(array(
                "status" => "error",
                "message" => "Counter is not found"
            ));
        } else {
            echo json_encode(array(
                "status" => "error",
                "message" => "Error: " . $conn->error
            ));
        }
    } else if ($method == "refresh-count") {
        // Employee Section

        // Employee: Total
        $sql_cmd = "
            SELECT 
            -- TOTAL EMPLOYEES
            (SELECT COUNT(e.id) FROM employees e) AS employees_total,

            -- TOTAL TRANSACTIONS
            (SELECT COUNT(t.idtransaction) FROM transactions t) AS transactions_total,

            -- TOTAL EMPLOYEE ACTIVE
            (SELECT COUNT(e.id) FROM employees e WHERE e.active = 1) AS employees_active_total,

            -- TOTAL EMPLOYEE NOT ACTIVE
            (SELECT COUNT(e.id) FROM employees e WHERE e.active = 0) AS employees_not_active_total,

            -- TOTAL EMPLOYEE ADMIN
            (SELECT COUNT(e.id) FROM employees e WHERE e.role_type = 'admin') AS employeees_admin_total,

            -- TOTAL EMPLOYEE EMPLOYEES
            (SELECT COUNT(e.id) FROM employees e WHERE e.role_type = 'employees') AS employees_employees_total,


            -- TRANSACTIONS
            -- Transaction today count
            (SELECT COUNT(t.idtransaction) 
            FROM transactions t 
            WHERE DATE(t.transaction_time) = CURDATE()) AS transaction_today_total,

            -- Transaction today count for pending
            (SELECT COUNT(t.idtransaction) 
            FROM transactions t 
            WHERE DATE(t.transaction_time) = CURDATE() AND t.status = 'pending') AS transaction_today_pending_total,
                
            -- Transaction today count for completed
            (SELECT COUNT(t.idtransaction) 
            FROM transactions t 
            WHERE DATE(t.transaction_time) = CURDATE() AND t.status = 'completed') AS transaction_today_completed_total,
                
            -- Transaction today count for cancelled
            (SELECT COUNT(t.idtransaction) 
            FROM transactions t 
            WHERE DATE(t.transaction_time) = CURDATE() AND t.status = 'cancelled') AS transaction_today_cancelled_total,
                
            -- Transaction today count for serve
            (SELECT COUNT(t.idtransaction) 
            FROM transactions t 
            WHERE DATE(t.transaction_time) = CURDATE() AND t.status = 'served') AS transaction_today_served_total
        ";

        $stmt = $conn->prepare($sql_cmd);
        $stmt->execute();
        $result = $stmt->get_result();
        $stats = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        

        // UPDATE STATS
        $sql_cmd = "UPDATE setup_system SET setup_value_int = ? WHERE setup_key = 'employees_total'";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->bind_param("s", $stats[0]['employees_total']);
        $stmt->execute();
        $stmt->close();

        $sql_cmd = "UPDATE setup_system SET setup_value_int = ? WHERE setup_key = 'transactions_total'";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->bind_param("s", $stats[0]['transactions_total']);
        $stmt->execute();
        $stmt->close();

        $sql_cmd = "UPDATE setup_system SET setup_value_int = ? WHERE setup_key = 'employees_active_total'";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->bind_param("s", $stats[0]['employees_active_total']);
        $stmt->execute();
        $stmt->close();

        $sql_cmd = "UPDATE setup_system SET setup_value_int = ? WHERE setup_key = 'employees_not_active_total'";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->bind_param("s", $stats[0]['employees_not_active_total']);
        $stmt->execute();
        $stmt->close();

        $sql_cmd = "UPDATE setup_system SET setup_value_int = ? WHERE setup_key = 'employees_admin_total'";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->bind_param("s", $stats[0]['employees_admin_total']);
        $stmt->execute();
        $stmt->close();

        $sql_cmd = "UPDATE setup_system SET setup_value_int = ? WHERE setup_key = 'employees_employees_total'";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->bind_param("s", $stats[0]['employees_employees_total']);
        $stmt->execute();
        $stmt->close();

        $sql_cmd = "UPDATE setup_system SET setup_value_int = ? WHERE setup_key = 'transaction_today_total'";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->bind_param("s", $stats[0]['transaction_today_total']);
        $stmt->execute();
        $stmt->close();
        $sql_cmd = "UPDATE setup_system SET setup_value_int = ? WHERE setup_key = 'transaction_today_pending'";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->bind_param("s", $stats[0]['transaction_today_pending']);
        $stmt->execute();
        $stmt->close();
        $sql_cmd = "UPDATE setup_system SET setup_value_int = ? WHERE setup_key = 'transaction_today_completed'";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->bind_param("s", $stats[0]['transaction_today_completed']);
        $stmt->execute();
        $stmt->close();
        $sql_cmd = "UPDATE setup_system SET setup_value_int = ? WHERE setup_key = 'transaction_today_cancelled'";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->bind_param("s", $stats[0]['transaction_today_cancelled']);
        $stmt->execute();
        $stmt->close();
        $sql_cmd = "UPDATE setup_system SET setup_value_int = ? WHERE setup_key = 'transaction_today_serve'";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->bind_param("s", $stats[0]['transaction_today_serve']);
        $stmt->execute();
        $stmt->close();

        echo json_encode(array(
            "status" => "success",
            "message" => "Refresh count successfully",
            "data" => $stats[0]
        ));
        exit();
    // REQUESTER: PUSH SUCCESS
    } else if ($method == "cashier-success") {
        // counterNumber
        // method: cashier
        // token_number from trasaction

        if (!isset($data->idemployee)) {
            echo json_encode(array(
                "status" => "error",
                "message" => "Input idemployee!"
            ));
            exit;
        }

        // check if the employee is exist
        $sql_cmd = "SELECT * FROM employees WHERE id = ?";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->bind_param("s", $data->idemployee);
        $stmt->execute();
        $result = $stmt->get_result();
        $employee = $result->fetch_all(MYSQLI_ASSOC);
        if ($result->num_rows == 0) {
            echo json_encode(array(
                "status" => "error",
                "message" => "Employee not found"
            ));
            exit;
        }

        // echo json_encode(array(
        //     "status" => "success",
        //     "message" => "Employee found",
        //     "data" => $employee
        // ));

        // Check if the employee was assigned in counter
        $sql_cmd = "SELECT * FROM counters WHERE idemployee = ?";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->bind_param("s", $data->idemployee);
        $stmt->execute();
        $result = $stmt->get_result();
        $counter = $result->fetch_all(MYSQLI_ASSOC);
        if ($result->num_rows == 0) {
            echo json_encode(array(
                "status" => "error",
                "message" => "Employee not assigned in counter"
            ));
            exit;
        }

        // echo json_encode(array(
        //     "status" => "success",
        //     "message" => "Employee found",
        //     "data" => $counter
        // ));
        // exit;

        // Get the 'serve' by 'idcounter' and 'idemployee'
        $sql_cmd = "SELECT * FROM transactions WHERE idcounter = ? AND idemployee = ? AND status = 'serve'";
        $stmt = $conn->prepare($sql_cmd);   
        $stmt->bind_param("ss", $counter[0]['idcounter'], $data->idemployee);
        $stmt->execute();
        $result = $stmt->get_result();
        $transaction = $result->fetch_all(MYSQLI_ASSOC);
        if ($result->num_rows == 0) {
            echo json_encode(array(
                "status" => "error",
                "message" => "No transaction found"
            ));
            exit;
        } else {
            echo json_encode(array(
                "status" => "success",
                "message" => "Transaction found",
                "data" => $transaction
            ));
        }

        // Commit as 'serve' to 'completed'
        $sql_cmd = "UPDATE transactions SET status = 'completed' WHERE idtransaction = ?";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->bind_param("s", $transaction[0]['idtransaction']);
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            echo json_encode(array(
                "status" => "success",
                "message" => "Success Transaction updated successfully"
            ));
        } else if ($stmt->affected_rows == 0) {
            // echo json_encode(array(
            //     "status" => "error",
            //     "message" => "No changes made"
            // ));
        } else {
            echo json_encode(array(
                "status" => "error",
                "message" => "Error: " . $conn->error
            ));
        }
        exit;
    } else if ($method == "cashier-missed") {
        // counterNumber
        // method: cashier
        // token_number from trasaction

        if (!isset($data->idemployee)) {
            echo json_encode(array(
                "status" => "error",
                "message" => "Input idemployee!"
            ));
            exit;
        }

        // check if the employee is exist
        $sql_cmd = "SELECT * FROM employees WHERE id = ?";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->bind_param("s", $data->idemployee);
        $stmt->execute();
        $result = $stmt->get_result();
        $employee = $result->fetch_all(MYSQLI_ASSOC);
        if ($result->num_rows == 0) {
            echo json_encode(array(
                "status" => "error",
                "message" => "Employee not found"
            ));
            exit;
        }

        // echo json_encode(array(
        //     "status" => "success",
        //     "message" => "Employee found",
        //     "data" => $employee
        // ));

        // Check if the employee was assigned in counter
        $sql_cmd = "SELECT * FROM counters WHERE idemployee = ?";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->bind_param("s", $data->idemployee);
        $stmt->execute();
        $result = $stmt->get_result();
        $counter = $result->fetch_all(MYSQLI_ASSOC);
        if ($result->num_rows == 0) {
            echo json_encode(array(
                "status" => "error",
                "message" => "Employee not assigned in counter"
            ));
            exit;
        }

        // echo json_encode(array(
        //     "status" => "success",
        //     "message" => "Employee found",
        //     "data" => $counter
        // ));
        // exit;

        // Get the 'serve' by 'idcounter' and 'idemployee'
        $sql_cmd = "SELECT * FROM transactions WHERE idcounter = ? AND idemployee = ? AND status = 'serve'";
        $stmt = $conn->prepare($sql_cmd);   
        $stmt->bind_param("ss", $counter[0]['idcounter'], $data->idemployee);
        $stmt->execute();
        $result = $stmt->get_result();
        $transaction = $result->fetch_all(MYSQLI_ASSOC);
        if ($result->num_rows == 0) {
            echo json_encode(array(
                "status" => "error",
                "message" => "No transaction found"
            ));
            exit;
        } else {
            echo json_encode(array(
                "status" => "success",
                "message" => "Transaction found",
                "data" => $transaction
            ));
        }

        // Commit as 'serve' to 'completed'
        $sql_cmd = "UPDATE transactions SET status = 'missed' WHERE idtransaction = ?";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->bind_param("s", $transaction[0]['idtransaction']);
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            echo json_encode(array(
                "status" => "success",
                "message" => "Missed Transaction updated successfully"
            ));
        } else if ($stmt->affected_rows == 0) {
            // echo json_encode(array(
            //     "status" => "error",
            //     "message" => "No changes made"
            // ));
        } else {
            echo json_encode(array(
                "status" => "error",
                "message" => "Error: " . $conn->error
            ));
        }
        exit;

    // Requster: Submit Form
    } else if ($method == "requester_form") {
        if (!isset($data->name) || !isset($data->email) || !isset($data->payment) || !isset($data->website)) {
            echo json_encode(array(
                "status" => "error",
                "message" => "Please fill up the information."
            ));
            exit;
        }
    
        $name = $data->name;
        $email = $data->email;
        $payment = $data->payment;
        $website = $data->website;
    
        $conn->begin_transaction();
    
        try {
            // Commit the request transaction
            $sql_cmd = "INSERT INTO requesters (name, email, payment) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql_cmd);
            $stmt->bind_param("sss", $name, $email, $payment);
            $stmt->execute();
            $requester_id = $stmt->insert_id;
            $stmt->close();
    
            // Get queue_count_int value from setup
            $sql_cmd = "SELECT setup_value_int FROM setup_system WHERE setup_key = 'queue_count'";
            $stmt = $conn->prepare($sql_cmd);
            $stmt->execute();
            $result = $stmt->get_result();
            $setup_row = $result->fetch_assoc();
            $queue_count_int = $setup_row['setup_value_int'];
            // Then increment the queue_count_int value by 1
            $queue_count_int++;
            $stmt->close();
            $sql_cmd = "UPDATE setup_system SET setup_value_int = ? WHERE setup_key = 'queue_count'";
            $stmt = $conn->prepare($sql_cmd);
            $stmt->bind_param("s", $queue_count_int);
            $stmt->execute();
            $stmt->close();
        
            // Generate a random token number
            $token_number = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));
    
            // Commit the transaction after the requester is inserted
            $sql_cmd = "INSERT INTO transactions (idrequester, token_number, queue_number, email_sent) VALUES (?, ?, ?, 1)";
            $stmt = $conn->prepare($sql_cmd);
            $stmt->bind_param("sss", $requester_id, $token_number, $queue_count_int);
            $stmt->execute();
            $transaction_id = $stmt->insert_id;
            $stmt->close();
    
            $conn->commit();
    
            $_SESSION['requester_token'] = $token_number;
            echo json_encode(array(
                "status" => "success",
                "message" => "Queue number generated successfully",
                "queue_number" => $queue_count_int,
                "token_number" => $token_number,
            ));
    
            // how to hide email reponse below this code
            // After the transaction was posted then use email feature to
            // send the transaction data to the requester using his/her email
            global $project_name, $project_name_full;
            global $email_feature;
            global $smtp_host, $smtp_port, $smtp_email, $smtp_password;
    
            $website_check = $website . '?requester_token=' . $token_number;
            $website_cancel = $website . '?requester_token=' . $token_number . '&cancel=true';
    
            $request_data = array(
                "name" => $name,
                "email" => $email,
                "payment" => $payment,
                "transaction_id" => $transaction_id,
                "website_check" => $website_check,
                "website_cancel" => $website_cancel,
                "queue_count_int" => $queue_count_int
            );
            include "./email_content.php";
            send_email_request_submit($request_data);
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(array(
                "status" => "error",
                "message" => "An error occurred. Please try again.",
                "error" => $e->getMessage()
            ));
            exit;
        }
    } else if ($method == "requester_form_cancel") {
        // WAIT LANG
    }

} else if ($_SERVER["REQUEST_METHOD"] === "GET") {
    if (isset($_GET['dashboard_stats'])) {
        $sql_cmd = "";
        $stmt = null;
        
        if (isset($_GET['day'])) {
            // Query for today's transactions (12AM to 11:59PM)
            $sql_cmd = "SELECT *
                        FROM transactions t
                        WHERE DATE(t.transaction_time) = CURDATE()";
        } else if (isset($_GET['week'])) {
            // Query for the last 7 days' transactions
            $sql_cmd = "SELECT *
                        FROM transactions t
                        WHERE t.transaction_time >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
        } else if (isset($_GET['month'])) {
            // Query for the last 30 days' transactions
            $sql_cmd = "SELECT *
                        FROM transactions t
                        WHERE t.transaction_time >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        } else if (isset($_GET['year'])) {
            // Query for the last 12 months' transactions
            $sql_cmd = "SELECT *
                        FROM transactions t
                        WHERE t.transaction_time >= DATE_SUB(NOW(), INTERVAL 12 MONTH)";
        } else {
            // Invalid request handling
            echo json_encode(array(
                "status" => "error",
                "message" => "Invalid request"
            ));
            exit;
        }
        
        // Execute the query and return results
        if (!empty($sql_cmd)) {
            $stmt = $conn->prepare($sql_cmd);
            $stmt->execute();
            $result = $stmt->get_result();
            $stats = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
    
            echo json_encode(array(
                "status" => "success",
                "message" => "Dashboard stats successfully retrieved",
                "data" => $stats
            ));
        }
        exit;


    // GENERATE REPORT
    } else if (isset($_GET['generate-report'])) {
        include_once __DIR__ . '/../includes/fpdf186/fpdf.php';
        if (!isset($_GET['year']) || !isset($_GET['month'])) {
            echo json_encode(array(
                "status" => "error",
                "message" => "Please provide the tentative year and month."
            ));
            exit;
            }

        
            
            // parse start and end datetime
        $date = new DateTime();

        $year = $_GET['year'];
        $month = $_GET['month'];
    
        $date = new DateTime();
        $date->setDate($year, $month, 1);
        $monthName = $date->format('F');
        // Transfer value month to words like January, February, etc.
        // i mean thats tentative
        
        if (isset($_GET['payment'])) {
            $payment_type = $_GET['payment'];
        }
    
        class PDF extends FPDF { 
            // Page header 
            function Header() 
            {
                global $master_key;

                // Get the username from token
                $token = $_COOKIE['token'];
                $token = decryptToken($token, $master_key);
                $token = json_encode($token, true);
                $token = json_decode($token, true);
                $genBy = "Generated by: " .$token['username'];
                global $project_name, $project_name_full;
                global $start_date_str, $end_date_str;
                global $year, $monthName;
                // Logo
                $this->Image('./../asset/images/favicon.png',15,15,20);
                // Arial bold 15
                $this->SetFont('Arial','B',15);
                // Move to the right
                $this->Cell(90);
                // Title
                $this->Cell(15,30,$project_name_full,0,0,'C');
                $this->SetFont('Arial','',10);
                $this->Ln(5);
                $this->Cell(90);
                $this->Cell(20,30,'Tiniguiban Heights, Puerto Princesa, 5300',0,0,'C');
                $this->Ln(5);
                $this->Cell(90);
                $this->Cell(20,30,'Palawan, Philippines',0,0,'C');
                $this->Ln(20);
                $this->Cell(70);
                $this->SetFont('Arial','B',12);
                $this->Cell(50,10,'GENERATED REPORT',1,0,'C');
                $this->SetFont('Arial','',10);
            
                // Generate Report Info
                $this->Ln(10);
                $this->Cell(50, 10, $genBy ,0,0,'L');
                $this->Ln(5);
                $this->Cell(50, 10, 'Transaction Date: ' . $monthName . ' ' . $year, 0, 0, 'L');
                $this->Ln(10);
            } 
    
            // Page footer 
            function Footer() 
            { 
                // Position at 1.5 cm from bottom 
                $this->SetY(-15); 
                
                // Set font-family and font-size of footer. 
                $this->SetFont('Arial', 'I', 8); 
    
                // set page number 
                $this->Cell(0, 10, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'C'); 
            } 
        }

            // $data = json_decode(file_get_contents("php://input"), true);

        $pdf = new PDF();
        $pdf->AliasNbPages();

        //////////////////////////////////////////////////

        // Total transactions
        $pdf->AddPage();
        $pdf->SetFont('Arial','B',12);
        $pdf->Cell(0,10,'Total Transactions',0,1,'C');
        $pdf->SetFont('Arial','',10);

        // Table Header
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(50,10,'Transaction Time',1,0,'C');
        $pdf->Cell(60,10,'Name',1,0,'C');
        $pdf->Cell(50,10,'Email',1,0,'C');
        $pdf->Cell(30,10,'Payment',1,0,'C');
        $pdf->SetFont('Arial','',10);
        $pdf->Ln(10);

        $sql_cmd = "SELECT * FROM requesters
                    WHERE (YEAR(created_at) = ? AND MONTH(created_at) = ?)";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->bind_param("ss", $year, $month);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        foreach ($result as $row) {
            $d = new DateTime($row['created_at']);
            $created_at = $d->format('Y-m-d h:i:s A');
            $pdf->Cell(50,10,$created_at,1,0,'C');
            $pdf->Cell(60,10,$row['name'],1,0,'C');
            $pdf->Cell(50,10,$row['email'],1,0,'C');
            $pdf->Cell(30,10,$row['payment'],1,0,'C');
            $pdf->Ln(10);
        }


        /////////////////////////////////////////////////

        // Registrar transactions
        $pdf->AddPage();
        $pdf->SetFont('Arial','B',12);
        $pdf->Cell(0,10,'Registrar',0,1,'C');
        $pdf->SetFont('Arial','',10);

        // Table Header
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(50,10,'Transaction Time',1,0,'C');
        $pdf->Cell(60,10,'Name',1,0,'C');
        $pdf->Cell(50,10,'Email',1,0,'C');
        $pdf->Cell(30,10,'Payment',1,0,'C');
        $pdf->SetFont('Arial','',10);
        $pdf->Ln(10);

        $sql_cmd = "SELECT * FROM requesters
                    WHERE (YEAR(created_at) = ? AND MONTH(created_at) = ?) AND payment = 'registrar'";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->bind_param("ss", $year, $month);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        foreach ($result as $row) {
            $d = new DateTime($row['created_at']);
            $created_at = $d->format('Y-m-d h:i:s A');
            $pdf->Cell(50,10,$created_at,1,0,'C');
            $pdf->Cell(50,10,$row['created_at'],1,0,'C');
            $pdf->Cell(60,10,$row['name'],1,0,'C');
            $pdf->Cell(50,10,$row['email'],1,0,'C');
            $pdf->Cell(30,10,$row['payment'],1,0,'C');
            $pdf->Ln(10);
        }

        /////////////////////////////////////////////////

        // Assessment transactions
        $pdf->AddPage();
        $pdf->SetFont('Arial','B',12);
        $pdf->Cell(0,10,'Registrar',0,1,'C');
        $pdf->SetFont('Arial','',10);

        // Table Header
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(50,10,'Transaction Time',1,0,'C');
        $pdf->Cell(60,10,'Name',1,0,'C');
        $pdf->Cell(50,10,'Email',1,0,'C');
        $pdf->Cell(30,10,'Payment',1,0,'C');
        $pdf->SetFont('Arial','',10);
        $pdf->Ln(10);

        $sql_cmd = "SELECT * FROM requesters
                    WHERE (YEAR(created_at) = ? AND MONTH(created_at) = ?) AND payment = 'assessment'";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->bind_param("ss", $year, $month);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        foreach ($result as $row) {
            $d = new DateTime($row['created_at']);
            $created_at = $d->format('Y-m-d h:i:s A');
            $pdf->Cell(50,10,$created_at,1,0,'C');
            $pdf->Cell(60,10,$row['name'],1,0,'C');
            $pdf->Cell(50,10,$row['email'],1,0,'C');
            $pdf->Cell(30,10,$row['payment'],1,0,'C');
            $pdf->Ln(10);
        }

        $pdf->Output();
        
        exit;
    }
        
    // EMPLOYEES
    if (isset($_GET['employees'])) {
        $sql_cmd = "SELECT e.id, e.username, e.role_type, e.email, e.active, e.created_at, e.employee_last_login
                    FROM employees e WHERE 1=1 ";
        $params = [];
        $types = "";

        if (isset($_GET['id'])) {
            $sql_cmd .= "AND e.id = ? ";
            $params[] = $_GET['id'];
            $types .= "s";
        }

        if (isset($_GET['search'])) {
            $sql_cmd .= "AND e.username LIKE ? ";
            $params[] = "%" . $_GET['search'] . "%";
            $types .= "s";
        }

        if (isset($_GET['total'])) {
            $sql_cmd = "SELECT COUNT(e.id) as total_employees FROM employees e WHERE 1=1";
        }

        if (isset($_GET['active'])) {
            $sql_cmd .= "AND e.active = ? ";
            $params[] = $_GET['active'];
            $types .= "s";
        }

        if (isset($_GET['role_type'])) {
            $sql_cmd .= "AND e.role_type = ? ";
            $params[] = $_GET['role_type'];
            $types .= "s";
        }

        if (isset($_GET['page']) && isset($_GET['paginate'])) {
            $limit = $_GET['paginate'];
            $offset = ($_GET['page'] - 1) * $limit;
            $sql_cmd .= " LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            $types .= "ss";
        }

        $stmt = $conn->prepare($sql_cmd);
        
        if ($types) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $employees = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        if (isset($_GET['total'])) {
            echo json_encode(array(
                "status" => "success",
                "total_employees" => $employees[0]['total_employees']
            ));
            exit;
        } else if ($result->num_rows > 0) {
            if (isset($_GET['id'])) {
                echo json_encode(array(
                    "status" => "success",
                    "employee" => $employees[0],
                    "message" => "Employee found",
                ));
                exit;
            }
            echo json_encode(array(
                "status" => "success",
                "employees" => $employees,
                "message" => "Employees found",
            ));
            exit;
        } else {
            echo json_encode(array(
                "status" => "error",
                "message" => "No employees found",
            ));
        }
        exit;
    
    // COUNTERS
    } else if (isset($_GET['counters'])) {
        $sql_cmd = "SELECT c.idcounter, c.counterNumber, c.idemployee, c.queue_count, e.username, c.created_at, e.role_type, e.active
                    FROM counters c
                    LEFT JOIN employees e ON c.idemployee = e.id
                    WHERE 1=1 AND e.role_type = 'employee' ";
        $params = [];
        $types = "";

        if (isset($_GET['id'])) {
            $sql_cmd .= "AND c.idcounter = ? ";
            $params[] = $_GET['id'];
            $types .= "s";
        }

        if (isset($_GET['available'])) {
            $sql_cmd = "SELECT e.username, e.id, e.active, e.role_type,
                        CASE 
                            WHEN c.queue_count IS NULL THEN 'Available'
                            ELSE 'Assigned' 
                        END as availability
                        FROM employees e
                        LEFT JOIN counters c ON e.id = c.idemployee
                        WHERE 
                            -- c.idemployee IS NULL 
                            -- AND 
                            e.role_type = 'employee'";
        }

        if (isset($_GET['search'])) {
            $sql_cmd .= "AND e.username LIKE ? ";
            $params[] = "%" . $_GET['search'] . "%";
            $types .= "s";
        }

        if (isset($_GET['id'])) {
            $sql_cmd .= "AND c.idcounter = ? ";
            $params[] = $_GET['id'];
            $types .= "s";
        }

        if (isset($_GET['total'])) {
            $sql_cmd = "SELECT COUNT(c.idcounter) as total_counters FROM counters c WHERE 1=1";
        }

        if (isset($_GET['order_by']) && $_GET['desc']) {
            // DESENDING ORDER
        } else {
            // ASCENDING ORDER

        }
        if (isset($_GET['page']) && isset($_GET['paginate'])) {
            $limit = $_GET['paginate'];
            $offset = ($_GET['page'] - 1) * $limit;
            $sql_cmd .= " LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            $types .= "ss";
        }


        $stmt = $conn->prepare($sql_cmd);

        if ($types) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $counters = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        if (isset($_GET['total'])) {
            echo json_encode(array(
                "status" => "success",
                "total_counters" => $counters[0]['total_counters']
            ));
            exit;
        } else if ($result->num_rows > 0) {
            if (isset($_GET['id'])) {
                echo json_encode(array(
                    "status" => "success",
                    "counter" => $counters[0],
                    "message" => "Counter found",
                ));
                exit;
            }
            echo json_encode(array(
                "status" => "success",
                "counters" => $counters,
                "message" => "Counters found",
            ));
            exit;
        } else {
            echo json_encode(array(
                "status" => "error",
                "message" => "No counters found",
            ));
        }
        exit;


    // TRANSACTIONS HISTORY
    } else if (isset($_GET['transactions'])) {
        $sql_cmd = "SELECT *
                    FROM transactions t
                    LEFT JOIN requesters r ON t.idrequester = r.id
                    WHERE 1=1 ";
        $params = [];
        $types = "";

        if (isset($_GET['id'])) {
            $sql_cmd .= "AND t.idtransaction = ? ";
            $params[] = $_GET['id'];
            $types .= "s";
        }

        if (isset($_GET['total'])) {
            $sql_cmd = "SELECT COUNT(t.idtransaction) as total_transactions
                        FROM transactions t";
        }

        if(isset($_GET['completed'])) {
            $sql_cmd .= "AND t.status = 'completed' ";
            $params[] = $_GET['completed'];
        }

        if(isset($_GET['pending'])) {
            $sql_cmd .= "AND t.status = 'pending' ";
            $params[] = $_GET['pending'];
        }

        if(isset($_GET['cancelled'])) {
            $sql_cmd .= "AND t.status = 'cancelled' ";
            $params[] = $_GET['cancelled'];
        }

        if (isset($_GET['serve'])) {
            $sql_cmd .= "AND t.serve = 'serve' ";
            $params[] = $_GET['serve'];
        }


        if (isset($_GET['payment'])) {
            if ($_GET['payment'] != 'none') {
                $sql_cmd .= "AND r.payment = ? ";
                $params[] = $_GET['payment'];
                $types .= "s";
            }

        }

        if (isset($_GET['email'])) {
            if ($_GET['email'] != 'none') {
                $sql_cmd .= "AND r.email = ? ";
                $params[] = $_GET['email'];
                $types .= "s";
            }
        }

        if (isset($_GET['today'])) {
            $sql_cmd .= "AND DATE(t.transaction_time) = CURDATE() ";
        }

        // Descending order
        if (isset($_GET['desc'])) {
            $sql_cmd .= "ORDER BY t.transaction_time DESC ";
        }

        if (isset($_GET['page']) && isset($_GET['paginate'])) {
            $limit = $_GET['paginate'];
            $offset = ($_GET['page'] - 1) * $limit;
            $sql_cmd .= " LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            $types .= "ss";
        }

        $stmt = $conn->prepare($sql_cmd);

        if ($types) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $transactions = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        if (isset($_GET['total'])) {
            echo json_encode(array(
                "status" => "success",
                "total_transactions" => $transactions[0]['total_transactions']
            ));
            exit;
        } else if ($result->num_rows > 0) {
            echo json_encode(array(
                "status" => "success",
                "transactions" => $transactions,
                "message" => "Transactions found",
            ));
            exit;
        } else {
            echo json_encode(array(
                "status" => "error",
                "message" => "No transactions found",
            ));
            exit;
        }
    exit;

    // CASHIER
    } else if (isset($_GET['cashier'])) {
        // This part will get the free queue transaction
        // after you transaction session
        if (isset($_GET['employee_id'])) {
            // Checking if that employee is exists
            $sql_cmd = "SELECT *
                        FROM employees e
                        WHERE e.id = ? AND e.role_type = 'employee'";
            $stmt = $conn->prepare($sql_cmd);
            $stmt->bind_param("s", $_GET['employee_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $employee = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            if ($result->num_rows == 0) {
                echo json_encode(array(
                    "status" => "error",
                    "message" => "Employee not found"
                ));
                exit;
            }

            // Checking if the counter was already assigned
            $sql_cmd = "SELECT *
                        FROM counters c
                        LEFT JOIN employees e ON c.idemployee = e.id
                        WHERE e.id = ? AND e.role_type = 'employee'";
            $stmt = $conn->prepare($sql_cmd);
            $stmt->bind_param("s", $_GET['employee_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $counters = $result->fetch_all(MYSQLI_ASSOC);
            // echo json_encode(array(
            //     "status" => "success",
            //     "message" => "Counter found",
            //     "data" => $counters
            // ));
            // exit;
            $stmt->close();
            if ($result->num_rows == 0) {
                echo json_encode(array(
                    "status" => "error",
                    "message" => "Counter not found"
                ));
                exit;
            }

            // Counting if theres transaction today was recorded
            $queue_count = 0;
            $sql_cmd = "SELECT COUNT(t.idtransaction) as total_transactions
                        FROM transactions t
                        WHERE DATE(t.transaction_time) = CURDATE()";
            $stmt = $conn->prepare($sql_cmd);
            $stmt->execute();
            $result = $stmt->get_result();
            $transactions = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            if ($result->num_rows > 0) {
                $queue_count = $transactions[0]['total_transactions'];
            }

            // Checking if the transaction was already assigned
            $sql_cmd = "SELECT *
                        FROM transactions
                        WHERE idcounter = ? AND idemployee = ? AND status = 'serve'";
            $stmt = $conn->prepare($sql_cmd);
            $stmt->bind_param("ss", $counters[0]['idcounter'], $_GET['employee_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $transactions = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            if ($result->num_rows == 1) {
                echo json_encode(array(
                    "status" => "success",
                    "message" => "Transaction already assigned",
                    "data" => $transactions[0]
                ));
                exit;
            }
            $sql_cmd  = "SELECT *
                        FROM transactions t
                        WHERE t.status = 'pending' AND t.idcounter IS NULL AND t.idemployee IS NULL
                        ORDER BY t.transaction_time ASC
                        LIMIT 1";
            $stmt = $conn->prepare($sql_cmd);
            $stmt->execute();
            $result = $stmt->get_result();
            $transactions = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            if ($result->num_rows == 0) {
                echo json_encode(array(
                    "status" => "error",
                    "message" => "No transaction available for today"
                ));
                exit;
            }
            // Or get the transaction where can still available for today
            $sql_cmd = "SELECT *
                        FROM transactions t
                        WHERE t.status = 'pending' AND t.idcounter IS NULL AND t.idemployee IS NULL
                        ORDER BY t.transaction_time ASC
                        LIMIT 1";
            $stmt= $conn->prepare($sql_cmd);
            $stmt->execute();
            $result = $stmt->get_result();
            $transactions = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            if ($result->num_rows > 0) {
                $sql_cmd = "UPDATE transactions
                            SET idcounter = ?, idemployee = ?, status = 'serve'
                            WHERE idtransaction = ?";
                            $stmt = $conn->prepare($sql_cmd);
                            $stmt->bind_param("sss", $counters[0]['idcounter'], $_GET['employee_id'], $transactions[0]['idtransaction']);
                            $stmt->execute();

                // This feature is optional for sending email
                echo json_encode(array(
                    "status" => "success",
                    "message" => "Transaction found",
                    "data" => $transactions[0]
                ));
            } else {
                echo json_encode(array(
                    "status" => "success",
                    "message" => "No transactions available for today"
                ));
                exit;
            }
        }

        exit;
    // REQUESTER
    } else if (isset($_GET['requesters'])) {
        // Display Requester Number
        if (isset($_GET['token_number'])) {
            $sql_cmd = "SELECT *
                        FROM transactions t
                        LEFT JOIN requesters r ON t.idrequester = r.id
                        WHERE t.token_number = ?";
            $stmt = $conn->prepare($sql_cmd);
            $stmt->bind_param("s", $_GET['token_number']);
            $stmt->execute();
            $result = $stmt->get_result();
            $requesters = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            if ($result->num_rows > 0) {
                echo json_encode(array(
                    "status" => "success",
                    "message" => "Requester found",
                    "data" => $requesters[0]
                ));
                exit;
            } else {
                echo json_encode(array(
                    "status" => "error",
                    "message" => "Requester not found"
                ));
                exit;
            }
        }
    } else if (isset($_GET['dashboard_admin'])) {
        // Admin Dashboard
        $sql_cmd = "SELECT COUNT(id) as today_transactions";
    } else {
        echo json_encode(array(
            "status" => "error",
            "message" => "Invalid request"
        ));
        exit;
    }

};