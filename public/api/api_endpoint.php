<?php
// QR-LINE Self-Endpoint v2
// (c) QR-Line. All Rights Reserved 2025

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
                // Log the login
                $sql_cmd = "INSERT INTO user_logs (user_id, comment, updated_at) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($sql_cmd);

                $user_id = $decToken['id'];
                $username = $decToken['username'];
                $comment = "LOG_OUT: " . $username . " is logged out";
                $curdate = date("Y-m-d H:i:s");

                $stmt->bind_param("sss", $user_id, $comment, $curdate);
                $stmt->execute();
                $stmt->close();
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
            if ($employee[0]['role_type'] == 'employee') {
                $sql_cmd = "SELECT c.counterNumber
                            FROM counters c
                            WHERE c.idemployee = ?";
                $stmt = $conn->prepare($sql_cmd);
                $stmt->bind_param("s", $employee[0]['id']);
                $stmt->execute();
                $result = $stmt->get_result();
                $counter = $result->fetch_all(MYSQLI_ASSOC);
                $stmt->close();
    
                if (!$counter) {
                    echo json_encode(array(
                        "status" => "error",
                        "message" => "Cashier was not assigned yet"
                    ));
                    exit;
                }
            }

            $token = array(
                "id" => $employee[0]['id'],
                "username" => $employee[0]['username'],
                "role_type" => $employee[0]['role_type'],
                "email" => $employee[0]['email'],
                "counterNumber" => $counter[0]['counterNumber'] ?? null,
            );
            $encToken = encryptToken($token, $master_key);
            setcookie("token", $encToken, time() + (86400 * 30), "/");

            // Log the login
            $sql_cmd = "INSERT INTO user_logs (user_id, comment, updated_at, device_name) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql_cmd);
            
            $user_id = $employee[0]['id'];
            $comment = "LOG_IN: " . $employee[0]['username'] . " is logged in";
            $curdate = date("Y-m-d H:i:s");
            $device_name = $data->device_name ?? null;
            $stmt->bind_param("ssss", $user_id, $comment, $curdate, $device_name);
            $stmt->execute();
            $stmt->close();


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

                // Log the deactivated account 
                $sql_cmd = "INSERT INTO user_logs (user_id, comment, updated_at) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($sql_cmd);
                $user_id = $employee[0]['id'];
                $comment = "LOG_IN: " . $employee[0]['username'] . " has been deactivated after attempting many times";
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
    
    } else if ($method == "forgot-password") {
        if (!isset($data->username)) {
            echo json_encode(array(
                "status" => "error",
                "message" => "Input username!"
            ));
            exit;
        }

        // Find the username 
        $username = $data->username;

        $sql_cmd = "SELECT e.id, e.username, e.email
                    FROM employees e
                    WHERE e.username = ?";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $employee = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        if ($result->num_rows == 0) {
            echo json_encode(array(
                "status" => "error",
                "message" => "Can't find username " . $username . "."
            ));
            exit;
        }

        // Generate 12 digit password
        $password = bin2hex(random_bytes(6));
        $hash_password = password_hash($password, PASSWORD_DEFAULT);
        $sql_cmd = "UPDATE employees
                    SET password = ?
                    WHERE username = ?";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->bind_param("ss", $hash_password, $username);
        $stmt->execute();
        // if affected_row
        if ($stmt->affected_rows > 0) {
            include_once __DIR__ . '/email_content.php';
            $employee = array(
                "username" => $username,
                "email" => $employee[0]['email'],
                "password" => $password
            );
            send_forgot_passwd($employee);
            echo json_encode(array(
                "status" => "success",
                "message" => "Password reset successfully",
                "new_password" => $password
            ));
            exit;
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
            exit;
        }

    // EMPLOYEES
    } else if ($method == "employee-cut-off") {
        if ($data->id == null) {
            echo json_encode(array(
                "status" => "error",
                "message" => "Input id!"
            ));
            exit;
        }
        
        // Check if the employee exists
        $sql_cmd = "SELECT *
                    FROM employees
                    WHERE id = ?";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->bind_param("s", $data->id);
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

        $employee = $employee[0];
        $cut_off = 0;
        if ($employee['cut_off_state'] == 0) {
            $cut_off = 1;
        } else {
            $cut_off = 0;

            // Affects only for counter table
            $sql_cmd = "UPDATE counters c
                        SET c.queue_remain = NULL
                        WHERE c.idemployee = ?";
            $stmt = $conn->prepare($sql_cmd);
            $stmt->bind_param("s", $data->id);
            $stmt->execute();
            $stmt->close();
        }

        // Toggle cut off
        $sql_cmd = "UPDATE employees
                    SET cut_off_state = ?
                    WHERE id = ?";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->bind_param("ss", $cut_off, $data->id);
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            if ($cut_off == 1) {
                $message = "Employee " . $employee['username'] . " is cut off";
            } else {
                $message = "Employee " . $employee['username'] . " is resume";
            }
            echo json_encode(array(
                "status" => "success",
                "cut_off" => $cut_off,
                "message" => $message
            ));
        } else {
            echo json_encode(array(
                "status" => "error",
                "message" => "Error: " . $conn->error
            ));
        }
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
        $active = $data->active ?? 1;

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
        $sql_cmd = "INSERT INTO employees (username, password, email, role_type, active) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->bind_param("sssss", $username, $hash_password, $email, $role_type, $active);
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
                "message" => "Please select the role type"
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

        // Checking for transaction history if theres assigned will be nulll and panding
        $sql_cmd = "UPDATE transactions t
                    SET t.idcounter = NULL, t.idemployee = NULL, t.status = 'pending'
                    WHERE t.idcounter = ?";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->bind_param("s", $data->id);
        $stmt->execute();
        $stmt->close();

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

        $idemployee = $data->idemployee;

        // check if the employee is exist
        $sql_cmd = "SELECT * FROM employees WHERE id = ?";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->bind_param("s", $idemployee);
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
        $stmt->bind_param("s", $idemployee);
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
        $stmt->bind_param("ss", $counter[0]['idcounter'], $idemployee);
        $stmt->execute();
        $result = $stmt->get_result();
        $transaction = $result->fetch_all(MYSQLI_ASSOC);
        if ($result->num_rows == 0) {
            // echo json_encode(array(
            //     "status" => "error",
            //     "message" => "No transaction found"
            // ));
            // exit;
        } else {
            // echo json_encode(array(
            //     "status" => "success",
            //     "message" => "Transaction found",
            //     "data" => $transaction
            // ));
            // exit;
        }

        // Commit as 'serve' to 'completed'
        $sql_cmd = "UPDATE transactions SET status = 'completed' WHERE idtransaction = ?";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->bind_param("s", $transaction[0]['idtransaction']);
        $stmt->execute();

        
        if ($stmt->affected_rows > 0) {
            // queue remain notify

            // Fetch
            $sql_cmd = "SELECT queue_remain
                        FROM counters c
                        WHERE c.idemployee = ?";
            $stmt = $conn->prepare($sql_cmd);
            $stmt->bind_param("s", $idemployee);
            $stmt->execute();
            $result = $stmt->get_result();
            $counter = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            $queue_remain_get = $counter[0]['queue_remain'];

            if ($queue_remain_get !== null) {
                $queue_remain_set = $queue_remain_get - 1;
                if ($queue_remain_set == 0) {
                    // Trigger
                    $sql_cmd = "UPDATE employees e
                                SET cut_off_state = 1
                                WHERE e.id = ?";
                    $stmt = $conn->prepare($sql_cmd);
                    $stmt->bind_param("s", $idemployee);
                    $stmt->execute();
                    $stmt->close();
                } else {
                    $sql_cmd = "UPDATE counters c
                                SET c.queue_remain = ?
                                WHERE c.idemployee = ?";
                    $stmt = $conn->prepare($sql_cmd);
                    $stmt->bind_param("ss", $queue_remain_set, $idemployee);
                    $stmt->execute();
                    $stmt->close();
                }
            }

            echo json_encode(array(
                "status" => "success",
                "message" => "Success Transaction updated successfully"
            ));
            // exit;
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
            exit;
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

        $idemployee = $data->idemployee;

        // check if the employee is exist
        $sql_cmd = "SELECT * FROM employees WHERE id = ?";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->bind_param("s", $idemployee);
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
        $stmt->bind_param("s", $idemployee);
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
        $stmt->bind_param("ss", $counter[0]['idcounter'], $idemployee);
        $stmt->execute();
        $result = $stmt->get_result();
        $transaction = $result->fetch_all(MYSQLI_ASSOC);
        if ($result->num_rows == 0) {
            // echo json_encode(array(
            //     "status" => "error",
            //     "message" => "No transaction found"
            // ));
            // exit;
        } else {
            // echo json_encode(array(
            //     "status" => "success",
            //     "message" => "Transaction found",
            //     "data" => $transaction
            // ));
            // exit;
        }

        // Commit as 'serve' to 'missed'
        $sql_cmd = "UPDATE transactions SET status = 'missed' WHERE idtransaction = ?";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->bind_param("s", $transaction[0]['idtransaction']);
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            // queue remain notify

            // Fetch
            $sql_cmd = "SELECT queue_remain
                        FROM counters c
                        WHERE c.idemployee = ?";
            $stmt = $conn->prepare($sql_cmd);
            $stmt->bind_param("s", $idemployee);
            $stmt->execute();
            $result = $stmt->get_result();
            $counter = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            $queue_remain_get = $counter[0]['queue_remain'];

            if ($queue_remain_get !== null) {
                $queue_remain_set = $queue_remain_get - 1;
                if ($queue_remain_set == 0) {
                    // Trigger
                    $sql_cmd = "UPDATE employees e
                                SET cut_off_state = 1
                                WHERE e.id = ?";
                    $stmt = $conn->prepare($sql_cmd);
                    $stmt->bind_param("s", $idemployee);
                    $stmt->execute();
                    $stmt->close();
                } else {
                    $sql_cmd = "UPDATE counters c
                                SET c.queue_remain = ?
                                WHERE c.idemployee = ?";
                    $stmt = $conn->prepare($sql_cmd);
                    $stmt->bind_param("ss", $queue_remain_set, $idemployee);
                    $stmt->execute();
                    $stmt->close();
                }
            }

            echo json_encode(array(
                "status" => "success",
                "message" => "Missed Transaction updated successfully"
            ));
            exit;
        } else if ($stmt->affected_rows == 0) {
            echo json_encode(array(
                "status" => "error",
                "message" => "No transaction was assigned"
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
    
    // Requester's cancel function
    } else if ($method == "requester-form-cancel") {
        if (!isset($data->token_number)) {
            echo json_encode(array(
                "status" => "error",
                "message" => "Please input token number."
            ));
            exit;
        }
        $token_number = $data->token_number;

        // Check exisitence
        $sql_cmd = "SELECT *
                    FROM transactions t 
                    WHERE t.token_number = ?";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->bind_param("s", $token_number);
        $stmt->execute();
        $result = $stmt->get_result();
        $transaction = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        if ($result->num_rows == 0) {
            echo json_encode(array(
                "status" => "error",
                "message" => "Transaction was not found"
            ));
            exit;
        }

        // Check if the transaction was cancelled
        $sql_cmd = "SELECT *
                    FROM transactions t 
                    WHERE t.token_number = ? AND t.status = 'missed'";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->bind_param("s", $token_number);
        $stmt->execute();
        $result = $stmt->get_result();
        $transaction = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        if ($result->num_rows > 0) {
            echo json_encode(array(
                "status" => "error",
                "message" => "Transaction was already cancelled"
            ));
            exit;
        }

        // Complete
        $sql_cmd = "SELECT *
                    FROM transactions t 
                    WHERE t.token_number = ? AND t.status = 'completed'";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->bind_param("s", $token_number);
        $stmt->execute();
        $result = $stmt->get_result();
        $transaction = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        if ($result->num_rows > 0) {
            echo json_encode(array(
                "status" => "error",
                "message" => "Transaction was already completed"
            ));
            exit;
        }

        // Cancel it 
        $sql_cmd = "UPDATE transactions
                    SET status = 'cancelled'
                    WHERE token_number = ?";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->bind_param("s", $token_number);
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            echo json_encode(array(
                "status" => "success",
                "message" => "Transaction cancelled successfully"
            ));
        } else if ($stmt->affected_rows == 0) {
            echo json_encode(array(
                "status" => "error",
                "message" => "Already cancelled this transaction"
            ));
        } else {
            echo json_encode(array(
                "status" => "error",
                "message" => "Error: " . $conn->error
            ));
        }


        
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
        $pwd = $data->pwd ?? "none";
        $website = $data->website;
        $student = $data->is_student ?? 0;
    
        $conn->begin_transaction();
    
        try {
            // Commit the request transaction
            $sql_cmd = "INSERT INTO requesters (name, email, payment, pwd, is_student) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql_cmd);
            $stmt->bind_param("sssss", $name, $email, $payment, $pwd, $student);
            $stmt->execute();
            $requester_id = $stmt->insert_id;
            $stmt->close();
    
            // Count the transactions during day
            $sql_cmd = "SELECT COUNT(t.idtransaction) as total_transactions
                        FROM transactions t
                        WHERE DATE(t.transaction_time) = CURDATE()";
            $stmt = $conn->prepare($sql_cmd);
            $stmt->execute();
            $result = $stmt->get_result();
            $transaction_count = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            $queue_count_int =  $transaction_count[0]['total_transactions'] + 1;

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

            // Send the email
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
    } else if ($method == "counter_queue_remain") {
        if (!isset($data->counter_number)) {
            echo json_encode(array(
                "status" => "error",
                "message" => "Counter Number is required"
            ));
        }
        // if (!isset($data->queue_remain)) {
        //     echo json_encode(array(
        //         "status" => "error",
        //         "message" => "Queue Reamin is required"
        //     ));
        // }
        $counter_number = $data->counter_number;
        $queue_remain = $data->queue_remain;

        // Check counter number is registered?
        $sql_cmd = "SELECT *
                    FROM counters c
                    WHERE c.counterNumber = ?";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->bind_param("s", $counter_number);
        $stmt->execute();
        $result = $stmt->get_result();
        $counter = $result->fetch_all(MYSQLI_ASSOC);
        if ($result->num_rows == 0) {
            echo json_encode(array(
                "status" => "error",
                "message" => "Counter number not found"
            ));
            exit;
        }
        $stmt->close();

        $sql_cmd = "UPDATE counters
                    SET queue_remain = ?
                    WHERE counterNumber = ?";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->bind_param("ss", $queue_remain, $counter_number);
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            echo json_encode(array(
                "status" => "success",
                "message" => "Queue remain updated successfully"
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
    
    // Schedule
    } else if ($method == "schedule-create") {
        if (!isset($data->enable) || !isset($data->schedule_type) || !isset($data->schedule_key)) {
            echo json_encode(array(
                "status" => "error",
                "message" => "Please fill up the information for schedule."
            ));
            exit;
        }

        $date_start = $data->date_start ?? null;
        $date_end = $data->date_end ?? null;
        $time_start = $data->time_start ?? null;
        $time_end = $data->time_end ?? null;
        $enable = $data->enable;
        $schedule_type = $data->schedule_type;
        $schedule_key = $data->schedule_key;
        $repeat = $data->repeat ?? null;
        $everyday = $data->everyday ?? null;

        $sql_cmd = "INSERT INTO scheduler (date_start, date_end, enable, schedule_type, schedule_key, `repeat`, time_start,  time_end, everyday) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->bind_param("sssssssss", $date_start, $date_end, $enable, $schedule_type, $schedule_key, $repeat, $time_start, $time_end, $everyday);
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            echo json_encode(array(
                "status" => "success",
                "message" => "Schedule created successfully"
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
    } else if ($method == "schedule-update-requester_form") {
        if (!isset($data->time_start) || !isset($data->time_end) || !isset($data->enable)) {
            echo json_encode(array(
                "status" => "error",
                "message" => "Please fill up the information for schedule."
            ));
            exit;
        }

        $time_start = $data->time_start ?? null;
        $time_end = $data->time_end ?? null;
        $enable = $data->enable;
        $repeat = $data->repeat ?? null;
        $everyday = $data->everyday ?? null;

        // Checking schedule_key was exist
        $sql_cmd = "SELECT *
                    FROM scheduler
                    WHERE schedule_key = 'requester_form'";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->execute();
        $result = $stmt->get_result();
        $schedule = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        if ($result->num_rows == 0) {

            $sql_cmd = "INSERT INTO scheduler (time_start, time_end, enable, schedule_type, schedule_key, `repeat`, everyday) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql_cmd);

            // You can update this part to set the default value
            $time_start = "8:00:00";
            $time_end = "17:00:00";
            $enable = 1;
            $schedule_type = "requester";
            $schedule_key = "requester_form";
            $repeat = "daily";
            $everyday = "mon;true;wed;thu;fri";
            $stmt->bind_param("sssssss", $time_start, $time_end, $enable, $schedule_type, $schedule_key, $repeat, $everyday);
            $stmt->execute();

            echo json_encode(array(
                "status" => "error",
                "message" => "Schedule not found, however the schedule will reset to default."
            ));
            exit;
        } else if ($result->num_rows > 1) {
            // Delete all requester_form schedule_key related
            $sql_cmd = "DELETE FROM scheduler
                        WHERE schedule_key = 'requester_form'";
            $stmt = $conn->prepare($sql_cmd);
            $stmt->execute();
            if ($stmt->affected_rows > 0) {
                $sql_cmd = "INSERT INTO scheduler (time_start, time_end, enable, schedule_type, schedule_key, `repeat`, everyday) 
                            VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql_cmd);

                // You can update this part to set the default value
                $time_start = "8:00:00";
                $time_end = "17:00:00";
                $enable = 1;
                $schedule_type = "requester";
                $schedule_key = "requester_form";
                $repeat = "daily";
                $everyday = "mon;true;wed;thu;fri";
                $stmt->bind_param("sssssss", $time_start, $time_end, $enable, $schedule_type, $schedule_key, $repeat, $everyday);
                $stmt->execute();
            }
        }

        // Only take 1 :>
        $sql_cmd = "UPDATE scheduler
                    SET time_start = ?, time_end = ?, enable = ?, `repeat` = ?, everyday = ?
                    WHERE schedule_key = 'requester_form'";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->bind_param("sssss", $time_start, $time_end, $enable, $repeat, $everyday);
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            echo json_encode(array(
                "status" => "success",
                "message" => "Schedule updated successfully"
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
    }

} else if ($_SERVER["REQUEST_METHOD"] === "GET") {
    if (isset($_GET['dashboard_stats'])) {

        // Version 1
        // $sql_cmd = "SELECT

        //                 -- TRANSACTIONS FOR TODAY
        //                 (SELECT COUNT(idtransaction) FROM transactions WHERE DATE(transaction_time) = CURDATE()) as transaction_total_today,
        //                 (SELECT COUNT(idtransaction) FROM transactions WHERE DATE(transaction_time) = CURDATE() AND status = 'pending')  as transaction_total_pending,
        //                 (SELECT COUNT(idtransaction) FROM transactions WHERE DATE(transaction_time) = CURDATE() AND status = 'completed') as transaction_total_completed,
        //                 (SELECT COUNT(idtransaction) FROM transactions WHERE DATE(transaction_time) = CURDATE() AND status = 'cancelled')  as transaction_total_cancelled,

        //                 -- TRANSACTION HISTORY STATS
        //                 (SELECT COUNT(idtransaction) FROM transactions WHERE DATE(transaction_time) = CURDATE() - INTERVAL 1 DAY)  as transaction_yesterday,
        //                 (SELECT COUNT(idtransaction) FROM transactions WHERE WEEK(transaction_time) = WEEK(CURDATE()) AND YEAR(transaction_time) = YEAR(CURDATE())) as transaction_total_this_week,
        //                 (SELECT COUNT(idtransaction) FROM transactions WHERE MONTH(transaction_time) = MONTH(CURDATE()) AND YEAR(transaction_time) = YEAR(CURDATE())) as transaction_total_this_month,   
        //                 (SELECT COUNT(idtransaction) FROM transactions WHERE YEAR(transaction_time) = YEAR(CURDATE())) as transaction_total_this_year,
        //                 (SELECT COUNT(*) FROM transactions) as transaction_total_all  
        //             ";

        // Version 2
        $sql_cmd = "SELECT setup_key, setup_value_int
                    FROM setup_system
                    WHERE setup_key LIKE 'transactions%';
        ";
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
            if ($_GET['role_type'] != 'none') {
                $sql_cmd .= "AND e.role_type = ? ";
                $params[] = $_GET['role_type'];
                $types .= "s";
            }
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

        if(isset($_GET['status'])) {
            if ($_GET['status'] != 'none') {
                $sql_cmd .= "AND t.status = ? ";
                $params[] = $_GET['status'];
                $types .= "s";
            }
        }

        if (isset($_GET['students'])) {

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
                $sql_cmd .= "AND r.email LIKE ? ";
                $params[] = "%" . $_GET['email'] . "%";
                $types .= "s";
            }
        }

        if (isset($_GET['today'])) {
            $sql_cmd .= "AND DATE(t.transaction_time) = CURDATE() ";
        }

        if (isset($_GET['date_range'])) {
            if ($_GET['date_range'] != 'none') {
                if ($_GET['date_range'] == 'today') {
                    $sql_cmd .= "AND DATE(t.transaction_time) = DATE(CURDATE()) ";
                } else if ($_GET['date_range'] == 'yesterday') {
                    $sql_cmd .= "AND DATE(t.transaction_time) = DATE(CURDATE() - INTERVAL 1 DAY) ";
                } else if ($_GET['date_range'] == 'this_week') {
                    $sql_cmd .= "AND WEEK(t.transaction_time) = WEEK(CURDATE()) AND YEAR(t.transaction_time) = YEAR(CURDATE()) ";
                } else if ($_GET['date_range'] == 'last_week') {
                    $sql_cmd = "AND WEEK(t.transaction_time) = WEEK(CURDATE() - INTERVAL 1 WEEK) AND YEAR(t.transaction_time) = YEAR(CURDATE()) ";
                } else if ($_GET['date_range'] == 'this_month') {
                    $sql_cmd .= "AND MONTH(t.transaction_time) = MONTH(CURDATE()) AND YEAR(t.transaction_time) = YEAR(CURDATE()) ";
                } else if ($_GET['date_range'] == 'last_month') {
                    $sql_cmd .= "AND MONTH(t.transaction_time) = MONTH(CURDATE() - INTERVAL 1 MONTH) AND YEAR(t.transaction_time) = YEAR(CURDATE()) ";
                } else if ($_GET['date_range'] == 'this_year') {
                    $sql_cmd .= "AND YEAR(t.transaction_time) = YEAR(CURDATE()) ";
                } else if ($_GET['date_range'] == 'last_year') {
                    $sql_cmd .= "AND YEAR(t.transaction_time) = YEAR(CURDATE() - INTERVAL 1 YEAR) ";
                }
            }
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

        // echo json_encode(array(
        //     "sql_cmd" => $sql_cmd
        // ));
        // exit;
        
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

            // Counting if theres transaction today was recorded in current day
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
                        WHERE 
                            t.status = 'pending' AND
                            t.idcounter IS NULL AND
                            t.idemployee IS NULL AND
                            DATE(t.transaction_time) = CURDATE()
                        ORDER BY t.transaction_time ASC
                        LIMIT 1";
            $stmt= $conn->prepare($sql_cmd);
            $stmt->execute();
            $result = $stmt->get_result();
            $transactions = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            if ($result->num_rows > 0) {

                


                $sql_cmd = "UPDATE transactions
                            SET idcounter = ?, idemployee = ?,  status = 'serve'
                            WHERE idtransaction = ?";
                $stmt = $conn->prepare($sql_cmd);
                $stmt->bind_param("sss", $counters[0]['idcounter'], $_GET['employee_id'], $transactions[0]['idtransaction']);
                $stmt->execute();

                // This feature is optional for sending email
                echo json_encode(array(
                    "status" => "success",
                    "message" => "Transaction found!",
                    "data" => $transactions[0]
                ));

                // Reminder before next 5

                $sql_cmd = "SELECT *
                        FROM transactions t
                        WHERE t.transaction_time > ?
                        ORDER BY t.transaction_time ASC
                        LIMIT 4, 1";
                $stmt = $conn->prepare($sql_cmd);
                $stmt->bind_param("s", $transactions[0]['transaction_time']);
                $stmt->execute();
                $result = $stmt->get_result();
                $transaction_f = $result->fetch_all(MYSQLI_ASSOC);
                $stmt->close();
                // echo json_encode(array(
                //     "status" => "success",
                //     "message" => "Transaction found",
                //     "data" => $transaction_f
                // ));
                // exit;
                // if ($result->num_rows == 0) {
                //     exit;
                // }

                // Get information from using idrequester
                $sql_cmd = "SELECT * 
                        FROM requesters r
                        WHERE r.id = ?";
                $stmt = $conn->prepare($sql_cmd);
                $stmt->bind_param("s", $transaction_f[0]['idrequester']);
                $stmt->execute();
                $result = $stmt->get_result();
                $requester = $result->fetch_all(MYSQLI_ASSOC);
                $stmt->close();
                // if ($result->num_rows > 0) {
                //     echo json_encode(array(
                //         "status" => "success",
                //         "message" => "Requester found",
                //         "data" => $requester
                //     ));
                //     exit;
                // }

                include "./email_content.php";

                $requester = $requester[0];
                $requester_name = $requester['name'];
                $requester_email = $requester['email'];
                $requester_payment = $requester['payment'];
                $requester_token = $transaction_f[0]['token_number'];
                $request_data = array(
                    "name" => $requester_name,
                    "email" => $requester_email,
                    "payment" => $requester_payment,
                    "transaction_id" => $transaction_f[0]['idtransaction'],
                    "queue_count_int" => $transaction_f[0]['queue_number'],
                    "website_check" => $serverName . '/public/requester/requester_number.php?requester_token=' . $requester_token
                );
                echo json_encode($request_data);
                send_email_notify_before_5($request_data);

                //////////////////////////////////////////////
                // Cancel the past 3

                $sql_cmd = "SELECT *
                        FROM transactions t
                        WHERE t.transaction_time < ? AND
                        DATE(t.transaction_time) = CURDATE() AND
                        t.status = 'missed'
                        ORDER BY t.transaction_time DESC
                        LIMIT 2, 1";
                $stmt = $conn->prepare($sql_cmd);
                $stmt->bind_param("s", $transactions[0]['transaction_time']);
                $stmt->execute();
                $result = $stmt->get_result();
                $transaction_p = $result->fetch_all(MYSQLI_ASSOC);
                $stmt->close();
                if ($result->num_rows > 0) {
                    // Didnt optimize yet..
            
                    // Then update it 'missed' to 'cancelled'
                    $sql_cmd = "UPDATE transactions
                                SET status = 'cancelled'
                                WHERE idtransaction = ?";
                    $stmt = $conn->prepare($sql_cmd);
                    $stmt->bind_param("s", $transaction_p[0]['idtransaction']);
                    $stmt->execute();
                    $stmt->close();
    
                    // echo json_encode(array(
                    //     "status" => "success",
                    //     "message" => "Transaction found",
                    //     "data" => $transaction_f
                    // ));
                    // exit;
                    // if ($result->num_rows == 0) {
                    //     exit;
                    // }
    
                    // Get information from using idrequester
                    $sql_cmd = "SELECT * 
                            FROM requesters r
                            WHERE r.id = ?";
                    $stmt = $conn->prepare($sql_cmd);
                    $stmt->bind_param("s", $transaction_p[0]['idrequester']);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $requester = $result->fetch_all(MYSQLI_ASSOC);
                    $stmt->close();
                    // if ($result->num_rows > 0) {
                    //     echo json_encode(array(
                    //         "status" => "success",
                    //         "message" => "Requester found",
                    //         "data" => $requester
                    //     ));
                    //     exit;
                    // }
    
    
                    $requester = $requester[0];
                    $requester_name = $requester['name'];
                    $requester_email = $requester['email'];
                    $requester_payment = $requester['payment'];
                    $requester_token = $transaction_p[0]['token_number'];
                    $request_data = array(
                        "name" => $requester_name,
                        "email" => $requester_email,
                        "payment" => $requester_payment,
                        "transaction_id" => $transaction_p[0]['idtransaction'],
                        "queue_count_int" => $transaction_p[0]['queue_number'],
                        "website_check" => $serverName . '/public/requester/requester_number.php?requester_token=' . $requester_token
                    );
                    echo json_encode($request_data);
                    send_email_notify_after_3($request_data);

                }
            } else {
                echo json_encode(array(
                    "status" => "error",
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

        // Spacial reserved

    // Cashier Current Number
    } else if (isset($_GET['counter_current_number'])) {
        /**
         * This part will show only in the public...
         */

        $sql_cmd = "SELECT 
                        c.counterNumber,
                        t.queue_number,
                        t.transaction_time
                    FROM 
                        counters c
                    LEFT JOIN 
                        transactions t
                    ON 
                        t.idtransaction = (
                            SELECT t2.idtransaction
                            FROM transactions t2
                            WHERE t2.idcounter = c.idcounter AND
                                DATE(t2.transaction_time) = DATE(CURDATE()) AND
                                t2.status = 'serve'
                            ORDER BY t2.transaction_time DESC
                            LIMIT 1
                        );
            ";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->execute();
        $result = $stmt->get_result();
        $counters = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        if ($result->num_rows > 0) {
            $sql_cmd = "SELECT t.queue_number
                        FROM transactions t
                        WHERE
                            DATE(t.transaction_time) = DATE(CURDATE()) AND
                            t.status = 'serve'
                        ORDER BY
                            t.transaction_time DESC
                        LIMIT 1;
                        ";
            $stmt = $conn->prepare($sql_cmd);
            $stmt->execute();
            $result_1 = $stmt->get_result();
            $requester_latest = $result_1->fetch_all(MYSQLI_ASSOC);
            echo json_encode(array(
                "status" => "success",
                "counters" => $counters,
                "requester" => $requester_latest[0]['queue_number'] ?? "No queue",
                "message" => "Counters found",
            ));
        } else {
            echo json_encode(array(
                "status" => "error",
                "message" => "No counters found",
            ));
        }
        exit;

    
    // Requester Number Monitor
    } else if (isset($_GET['requester_number'])) {
        // F

        if (!isset($_GET['requester_token'])) {
            echo json_encode(array(
                "status" => "error",
                "message" => "Please provide the requester token that you submitted."
            ));
            exit;
        }
    
        // 
        $requester_token = $_GET['requester_token'];
        // echo json_encode(array(
        //     "status" => "success",
        //     "requester_token" => $requester_token
        // ));
        // exit;
    
        // Get your counter number based from token
        $stmt = $conn->prepare("SELECT t.queue_number, c.counterNumber, t.idcounter, t.status, c.queue_count
                                FROM transactions t
                                LEFT JOIN counters c ON t.idcounter = c.idcounter
                                WHERE t.token_number = ?
                                ORDER BY t.transaction_time DESC LIMIT 1");
        $stmt->bind_param("s", $requester_token);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        // Get the highest queue_count from transaction where who are served
        $sql_cmd = "SELECT MAX(t.queue_number) AS queue_number
                    FROM transactions t
                    WHERE DATE(t.transaction_time) = CURDATE() AND t.status = 'serve'
                    ORDER BY t.transaction_time DESC";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->execute();
        $result = $stmt->get_result();
        $setup_row = $result->fetch_assoc();
        $stmt->close();
        $queue_count_int = $setup_row['queue_number'];
    
        // echo json_encode(array(
        //     "status" => "success",
        //     "queue_count_int" => $setup_row,
        // ));



        echo json_encode(array(
            "status" => "success",
            "queueNumber" => $row['queue_number'] ?? "N/A",
            "counterNumber" => $row['counterNumber'] ?? "N/A",
            "currentQueueNumber" => $queue_count_int ?? "N/A",
            "requester_status" => $row['status'] ?? "N/A"
        ));
        exit;
    } else if (isset($_GET['transaction_reminder'])) {
        if (!isset($_GET['idtransaction'])) {
            echo json_encode(array(
                "status" => "error",
                "message" => "Please provide the transaction id."
            ));
            exit;
        }

        $reminder_future = true;
        if ($reminder_future) {
            $sql_cmd = "SELECT *
                        FROM requesters r
                        WHERE r.created_at > ?
                        ORDER BY r.created_at ASC
                        LIMIT 4,1";
            $stmt = $conn->prepare($sql_cmd);
            $stmt->bind_param("s", $_GET['idtransaction']);
            $stmt->execute();
            $result = $stmt->get_result();
            $request_get = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            $requester = $request_get[0];

            // Get related from this above about requester's transaction
            $sql_cmd = "SELECT *
                        FROM transactions
                        WHERE idrequester = ? AND status = 'pending'";
            $stmt = $conn->prepare($sql_cmd);
            $stmt->bind_param("s", $requester['id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $transaction_get = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            $transaction_get = $transaction_get[0];
            // echo json_encode(array(
            //     "status" => "success",
            //     "data" => $transaction_get,
            //     "message" => "Request's transaction found."
            // ));
            // exit;
            // echo json_encode(array(
            //     "status" => "success",
            //     "data" => $request_get,
            //     "message" => "Transaction found."
            // ));
            // exit;
            include "./email_content.php";

            // $requester = $requester[0];

            $requester_name = $requester['name'];
            $requester_email = $requester['email'];
            $requester_payment = $requester['payment'];

            $request_data = array(
                "name" => $requester_name,
                "email" => $requester_email,
                "payment" => $requester_payment,
                "transaction_id" => $transaction_get['idtransaction'],
                "queue_count_int" => $transaction_get['queue_number']
            );
            send_email_notify_before_5($request_data);

            
        }
    } else if (isset($_GET['employeeCutOff'])) {
        if (!isset($_GET['id'])) {
            echo json_encode(array(
                "status" => "error",
                "message" => "Please provide the employee id."
            ));
            exit;
        }

        $sql_cmd = "SELECT e.cut_off_state
                    FROM employees e
                    WHERE e.id = ?";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->bind_param("s", $_GET['id']);
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
        } else {
            echo json_encode(array(
                "status" => "success",
                "cut_off_state" => $employee[0]['cut_off_state'],
                "message" => "Employee found"
            ));
        }
    
    // Transaction Stats
    } else if (isset($_GET['transactionStats'])) {
        // too lazy to sort :>
        if (!isset($_GET['data_range'])) {
            echo json_encode(array(
                "status" => "error",
                "message" => "Please provide the data range."
            ));
            exit;
        }
        $data_range = $_GET['data_range'];
        // CURRENT DAY
        if ($data_range === "day") {
            // Get the current day and group by hour
            $sql_cmd = "SELECT HOUR(transaction_time) as `hour` , COUNT(idtransaction) as total_transactions
                        FROM transactions
                        WHERE DATE(transaction_time) = CURDATE()
                        GROUP BY hour
                        -- ORDER BY `hour` ASC; -- On working
                        ";
            $stmt = $conn->prepare($sql_cmd);
            $stmt->execute();
            $result = $stmt->get_result();
            $transactions = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            echo json_encode(array(
                "status" => "success",
                "message" => "Transaction stats successfully retrieved",
                "stats" => $transactions
            ));

        // CURRENT WEEK
        } else if ($data_range === "week") {
            // Get the current week and group by day
            $sql_cmd = "SELECT DATE(transaction_time) as date, COUNT(idtransaction) as total_transactions
                        FROM transactions
                        WHERE (WEEK(transaction_time) = WEEK(CURDATE())) 
                        AND DATE(transaction_time) <= CURDATE() AND YEAR(transaction_time) = YEAR(CURDATE())
                        GROUP BY DATE(transaction_time)
                        ORDER BY date ASC;";
            $stmt = $conn->prepare($sql_cmd);
            $stmt->execute();
            $result = $stmt->get_result();
            $transactions = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            echo json_encode(array(
                "status" => "success",
                "message" => "Transaction stats successfully retrieved",
                "stats" => $transactions
            ));
        // LAST WEEK
        } else if ($data_range === "last-week") {
            $sql_cmd = "SELECT DATE(transaction_time) as date, COUNT(idtransaction) as total_transactions
                        FROM transactions
                        WHERE transaction_time >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND
                        DATE(transaction_time) <= CURDATE()
                        GROUP BY DATE(transaction_time)
                        ORDER BY date ASC
                        ";
            $stmt = $conn->prepare($sql_cmd);
            $stmt->execute();
            $result = $stmt->get_result();
            $transactions = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            echo json_encode(array(
                "status" => "success",
                "message" => "Transaction stats successfully retrieved",
                "stats" => $transactions
            ));

        // This Month
        } else if ($data_range === "month") {
            // Get the current month and group by day
            $sql_cmd = "SELECT DATE(transaction_time) as date, COUNT(idtransaction) as total_transactions
                        FROM transactions
                        WHERE MONTH(transaction_time) = MONTH(CURDATE()) AND
                            DATE(transaction_time) <= CURDATE()
                        GROUP BY DATE(transaction_time)
                        ORDER BY date ASC";
            $stmt = $conn->prepare($sql_cmd);
            $stmt->execute();
            $result = $stmt->get_result();
            $transactions = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            echo json_encode(array(
                "status" => "success",
                "message" => "Transaction stats successfully retrieved",
                "stats" => $transactions
            ));

        // Last 30 days
        } else if ($data_range === "last-30-days") {
            $sql_cmd = "SELECT DATE(transaction_time) as date, COUNT(idtransaction) as total_transactions
                        FROM transactions
                        WHERE transaction_time >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) AND
                        DATE(transaction_time) <= CURDATE()
                        GROUP BY DATE(transaction_time)
                        ORDER BY date ASC";
            $stmt = $conn->prepare($sql_cmd);
            $stmt->execute();
            $result = $stmt->get_result();
            $transactions = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            echo json_encode(array(
                "status" => "success",
                "message" => "Transaction stats successfully retrieved",
                "stats" => $transactions
            ));

        // LAST 3 MONTHS
        } else if ($data_range === "last-3-months") {
            $sql_cmd = "SELECT DATE_FORMAT(transaction_time, '%Y-%m') as month, COUNT(idtransaction) as total_transactions
                        FROM transactions
                        WHERE transaction_time >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH) AND
                            DATE(transaction_time) <= CURDATE()
                        GROUP BY DATE_FORMAT(transaction_time, '%Y-%m')
                        ORDER BY month ASC";
            $stmt = $conn->prepare($sql_cmd);
            $stmt->execute();
            $result = $stmt->get_result();
            $transactions = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            echo json_encode(array(
                "status" => "success",
                "message" => "Transaction stats successfully retrieved",
                "stats" => $transactions
            ));

        // LAST 12 MONTHS
        } else if ($data_range === "last-12-months") {
            $sql_cmd = "SELECT DATE_FORMAT(transaction_time, '%Y-%m') as month, COUNT(idtransaction) as total_transactions
                        FROM transactions
                        WHERE transaction_time >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH) AND
                            DATE(transaction_time) <= CURDATE()
                        GROUP BY DATE_FORMAT(transaction_time, '%Y-%m')
                        ORDER BY month ASC";
            $stmt = $conn->prepare($sql_cmd);
            $stmt->execute();
            $result = $stmt->get_result();
            $transactions = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            echo json_encode(array(
                "status" => "success",
                "message" => "Transaction stats successfully retrieved",
                "stats" => $transactions
            ));
        // This year
        } else if (isset($_GET['year'])) {
            $sql_cmd = "SELECT 
                            DATE_FORMAT(transaction_time, '%Y-%m') AS month, 
                            COUNT(idtransaction) AS total_transactions
                            FROM transactions
                            WHERE YEAR(transaction_time) = YEAR(CURDATE())
                            GROUP BY DATE_FORMAT(transaction_time, '%Y-%m')
                            ORDER BY month ASC";
            $stmt = $conn->prepare($sql_cmd);
            $stmt->execute();
            $result = $stmt->get_result();
            $transactions = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            echo json_encode(array(
                "status" => "success",
                "message" => "Transaction stats successfully retrieved",
                "stats" => $transactions
            ));
        } else {
            echo json_encode(array(
                "status" => "error",
                "message" => "Invalid request"
            ));
        }
    
    } else if (isset($_GET['counter_queue_remain'])) {
        if (!isset($_GET['counter_number'])) {
            echo json_encode(array(
                "status" => "error",
                "message" => "Please provide the counter number."
            ));
            exit;
        }

        $counter_number = $_GET['counter_number'];
        $sql_cmd = "SELECT c.queue_remain
                    FROM counters c
                    WHERE c.counterNumber = ?";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->bind_param("s", $counter_number);
        $stmt->execute();
        $result = $stmt->get_result();
        $counters = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        if ($result->num_rows == 0) {
            echo json_encode(array(
                "status" => "error",
                "message" => "Counter is not assigned"
            ));
            exit;
        } else {
            echo json_encode(array(
                "status" => "success",
                "queue_remain" => $counters[0]['queue_remain'],
                "message" => "Counter found"
            ));
            exit;
        }
        exit;
    // Reset the transaction for today
    } else if (isset($_GET['transaction_today_reset'])) {
        $sql_cmd = "UPDATE transactions t
                    SET t.idemployee = NULL, t.idcounter = NULL, t.status = 'pending'
                    WHERE DATE(t.;transaction_time) = DATE(CURDATE())";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->execute();
        $stmt->close();

    } else if (isset($_GET['schedule-requester_form'])) {
        $sql_cmd = "SELECT s.enable, s.time_start, s.time_end, s.everyday
                    FROM scheduler s
                    WHERE s.schedule_key = 'requester_form'";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->execute();
        $result = $stmt->get_result();
        $schedule = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        if ($result->num_rows > 0) {
            echo json_encode(array(
                "status" => "success",
                "message" => "Schedule found",
                "data" => $schedule[0]
            ));
            exit;
        } else {
            echo json_encode(array(
                "status" => "error",
                "message" => "Schedule not found"
            ));
            exit;
        }
    } else {
        echo json_encode(array(
            "status" => "error",
            "message" => "Invalid request"
        ));
        exit;
    }

};