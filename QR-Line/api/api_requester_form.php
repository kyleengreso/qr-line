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
        // Insert user data into the database
        $stmt = $conn->prepare("INSERT INTO requesters (name, email, payment) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $payment);
        $stmt->execute();
        $requester_id = $stmt->insert_id;
        $stmt->close();

        // Find the counter with the lowest queue count that employee is assigned to
        $stmt = $conn->prepare("SELECT idcounter FROM counter ORDER BY queue_count ASC LIMIT 1");
        $stmt->execute();
        $result = $stmt->get_result();
        $counter = $result->fetch_assoc();
        $stmt->close();


        if (!$counter) {
            throw new Exception("No available counters.");
        }

        $counter_id = $counter['idcounter'];

        // Get the next queue number
        $stmt = $conn->prepare("SELECT IFNULL(MAX(queue_number) + 1, 1) AS next_queue FROM transactions");
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $queue_number = $row['next_queue'];
        $stmt->close();

        // Get idemployee from the counter
        $stmt = $conn->prepare("SELECT idemployee FROM counter WHERE idcounter = ?");
        $stmt->bind_param("s", $counter_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $employee = $result->fetch_assoc();
        $stmt->close();

        // Generate a unique token number
        $token_number = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));

        // Insert queue details into transactions
        $stmt = $conn->prepare("INSERT INTO transactions (idrequester, idcounter, queue_number, token_number, idemployee, status) VALUES (?, ?, ?, ?, ?, 'pending')");
        $stmt->bind_param("sssss", $requester_id, $counter_id, $queue_number, $token_number, $employee['idemployee']);
        $stmt->execute();
        $stmt->close();

        // Update counter queue count
        $stmt = $conn->prepare("UPDATE counter SET queue_count = queue_count + 1 WHERE idcounter = ?");
        $stmt->bind_param("i", $counter_id);
        $stmt->execute();
        $stmt->close();

        // Commit transaction
        $conn->commit();

        echo json_encode(array(
            "status" => "success",
            "message" => "Queue number generated successfully",
            "queue_number" => $queue_number,
            "token_number" => $token_number,
            "counter_id" => $counter_id
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