<?php
header("Content-Type: application/json");

// Delete all data stored in this system
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_GET['reset'])) {
        session_start();
        $master_key = "9875321";
        $reset_key = $_GET['reset'];
        
        if ($reset_key != $master_key) {
            echo json_encode(array(
                "status" => "error",
                "message" => "Invalid reset key."
            ));
            exit;
        }
        // Delete transactions and remove safe
        $sql_cmd = "DELETE FROM transactions";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->execute();
        $stmt->close();
        
        // Delete requesters
        $sql_cmd = "DELETE FROM requesters";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->execute();
        $stmt->close();
    
        // Delete counters
        $sql_cmd = "DELETE FROM counter";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->execute();
        $stmt->close();
    
        // Delete employees
        $sql_cmd = "DELETE FROM employees";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->execute();
        $stmt->close();
    
        echo json_encode(array(
            "status" => "success",
            "message" => "All data has been reset."
        ));
    } else if (isset($_GET['setup'])) {
        // Setup for first time use checker
        $master_key = "aceday";

        if ($_GET['setup'] != $master_key) {
            echo json_encode(array(
                "status" => "error",
                "message" => "Invalid setup key."
            ));
            exit;
        }

        // Checking if theres any employee in this system
        $sql_cmd = "SELECT * FROM employees";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {
            echo json_encode(array(
                "status" => "error",
                "message" => "System already setup."
            ));
            exit;
        } else {
            // Inserting default admin account
            $username = "admin";
            $password = "admin";
            $hash_password = password_hash($password, PASSWORD_DEFAULT);
            $sql_cmd = "INSERT INTO employees (username, password) VALUES (?, ?)";
            $stmt = $conn->prepare($sql_cmd);
            $stmt->bind_param("ss", $username, $hash_password);
            $stmt->execute();
            $stmt->close();
            
            echo json_encode(array(
                "status" => "success",
                "message" => "System setup successfully."
            ));
        }
    }
} else{
    echo json_encode(array(
        "status" => "error",
        "message" => "Invalid request method."
    ));
}
