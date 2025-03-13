<?php
include "./../includes/db_conn.php";
header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the POST data
    $data = json_decode(file_get_contents("php://input"));

    if (!isset($data->name) || !isset($data->email) || !isset($data->payment)) {
        echo json_encode(array(
            "status" => "error",
            "message" => "Please fill up the information."
        ));
        exit;
    }

    $name = $data->name;
    $email = $data->email;
    $payment = $data->payment;

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
        $sql_cmd = "SELECT setup_value_int FROM setup WHERE setup_key = 'queue_count'";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->execute();
        $result = $stmt->get_result();
        $setup_row = $result->fetch_assoc();
        $queue_count_int = $setup_row['setup_value_int'];
        // Then increment the queue_count_int value by 1
        $queue_count_int++;
        $stmt->close();
        $sql_cmd = "UPDATE setup SET setup_value_int = ? WHERE setup_key = 'queue_count'";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->bind_param("s", $queue_count_int);
        $stmt->execute();
        $stmt->close();
    
        // Generate a random token number
        $token_number = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));

        // Commit the transaction after the requester is inserted
        $sql_cmd = "INSERT INTO transactions (idrequester, token_number, queue_number) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->bind_param("sss", $requester_id, $token_number, $queue_count_int);
        $stmt->execute();
        $stmt->close();

        $conn->commit();

        echo json_encode(array(
            "status" => "success",
            "message" => "Queue number generated successfully",
            "queue_number" => $queue_count_int,
            "token_number" => $token_number,
        ));
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(array(
            "status" => "error",
            "message" => "An error occurred. Please try again.",
            "error" => $e->getMessage()
        ));
        exit;
    }
}
?>