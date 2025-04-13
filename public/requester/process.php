<?php
include './../includes/db_conn.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"];
    $email = $_POST["email"];
    $payment = $_POST["payment"];

    // Start transaction
    $conn->begin_transaction();

    try {
        // Insert user data into the database
        $stmt = $conn->prepare("INSERT INTO requesters (name, email, payment) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $payment);
        $stmt->execute();
        $user_id = $stmt->insert_id;
        $stmt->close();

        // Find the counter with the lowest queue count
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

        // Generate a unique token number
        $token_number = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));

        // Insert queue details into transactions
        $stmt = $conn->prepare("INSERT INTO transactions (iduser, idcounter, queue_number, token_number, status, payment) VALUES (?, ?, ?, ?, 'pending', ?)");
        $stmt->bind_param("iiiss", $user_id, $counter_id, $queue_number, $token_number, $payment);
        $stmt->execute();
        $stmt->close();

        // Update counter queue count
        $stmt = $conn->prepare("UPDATE counter SET queue_count = queue_count + 1 WHERE idcounter = ?");
        $stmt->bind_param("i", $counter_id);
        $stmt->execute();
        $stmt->close();

        // Commit transaction
        $conn->commit();

        // Redirect user to queue display page
        header("Location: user_number.php?queue=$queue_number&counter=$counter_id");
        exit();
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        die("Error processing request: " . $e->getMessage());
    }
}
?>
