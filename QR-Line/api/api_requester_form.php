<?php
include_once "./../base.php";
include_once "./../includes/db_conn.php";


header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the POST data
    session_start();
    $data = json_decode(file_get_contents("php://input"));

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
}
?>