<?php
include './../base.php';
include './../includes/db_conn.php';
header("Content-Type: application/json");
// POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $data = json_decode(file_get_contents("php://input"));

    // Tell me to check numbers

    if (!isset($data->method)) {
        echo json_encode(array(
            "status" => "error",
            "message" => "Method is required"
        ));
        exit;
    }

    $method = $data->method;
    if ($method == "logout") {
        // This part will act as a logout
        session_unset();
        session_destroy();
        echo json_encode(array(
            "status" => "success",
            "message" => "User logged out"
        ));
        exit;
    }
    if ($method == "dashboard_admin") {
        // This part it will be act as admin of the dashboard

        // This can tell $token = base64_encode($username . ':' . date('Y-m-d H:i:s') . ':' . date('Y-m-d H:i:s', strtotime('+1 day')));
        // If the token is not yet expired, it will be able to access the dashboards

        // Decrypt the token
        if (!isset($data->token)) {
            echo json_encode(array(
                "status" => "error",
                "message" => "Token is required from json"
            ));
            exit;
        }
        $token = base64_decode($data->token);
        $token = explode('!!', $token);
        $username = $token[0];
        $date_created = $token[1];
        $date_expired = $token[2];

        // echo json_encode(array(
        //     "status" => "success",
        //     "message" => "Token decrypted",
        //     "username" => $username,
        //     "date_created" => $date_created,
        //     "date_expired" => $date_expired
        // ));

        // Check if the token is expired
        if (strtotime($date_expired) < strtotime(date('Y-m-d H:i:s'))) {
            echo json_encode(array(
                "status" => "error",
                "message" => "Token expired",
            ));
            exit;
        }

        // Check if the username is valid
        $stmt = $conn->prepare("SELECT username, role_type FROM employees WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $role_type = $user['role_type'];

            if ($role_type == "admin") {
                // Get all the employees
                $stmt = $conn->prepare("SELECT id, username, created_at FROM employees");
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    $employee = $result->fetch_all(MYSQLI_ASSOC)[0];

                    // Now you can get the data in realtime :)
                    
                    // TOTAL TRANSACTIONS FILTERED BY TRANSACTION TIME for today record
                    $stmt = $conn->prepare("SELECT COUNT(idtransaction) AS total_transactions FROM transactions WHERE DATE(transaction_time) = CURDATE()");
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $total_transactions = $result->fetch_assoc()['total_transactions'];
                    $stmt->close();

                    // GET PENDING TRANSACTIONS FILTERED BY TRANSACTION TIME for today record
                    $stmt = $conn->prepare("SELECT COUNT(idtransaction) AS pending_transactions FROM transactions WHERE status = 'pending' AND DATE(transaction_time) = CURDATE()");
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $pending_transactions = $result->fetch_assoc()['pending_transactions'];
                    $stmt->close();

                    // GET COMPLETED TRANSACTIONS FILTERED BY TRANSACTION TIME for today record
                    $stmt = $conn->prepare("SELECT COUNT(idtransaction) AS completed_transactions FROM transactions WHERE status = 'completed' AND DATE(transaction_time) = CURDATE()");
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $completed_transactions = $result->fetch_assoc()['completed_transactions'];
                    $stmt->close();

                    // GET CANCELED TRANSACTIONS FILTERED BY TRANSACTION TIME for today record
                    $stmt = $conn->prepare("SELECT COUNT(idtransaction) AS canceled_transactions FROM transactions WHERE status = 'cancelled' AND DATE(transaction_time) = CURDATE()");
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $canceled_transactions = $result->fetch_assoc()['canceled_transactions'];
                    $stmt->close();

                    echo json_encode(array(
                        "status" => "success",
                        "message" => "Admin authenticated",
                        "total_transactions" => $total_transactions,
                        "pending_transactions" => $pending_transactions,
                        "completed_transactions" => $completed_transactions,
                        "canceled_transactions" => $canceled_transactions,


                        "employees" => $employee
                    ));
                } else {
                    echo json_encode(array(
                        "status" => "error",
                        "message" => "Invalid username"
                    ));
                    exit;
                }

            } else {
                echo json_encode(array(
                    "status" => "error",
                    "message" => "Unauthorized access"
                ));
            }
        } else {
            echo json_encode(array(
                "status" => "error",
                "message" => "Invalid username"
            ));
        }

    } else if ($method == "dashboard_employee") {

    }
}