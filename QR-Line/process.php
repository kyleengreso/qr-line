<?php
include 'includes/db_conn.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"];
    $email = $_POST["email"];
    $purpose = $_POST["purpose"];

    // Insert user data into the database
    $stmt = $conn->prepare("INSERT INTO users (name, email) VALUES (?, ?)");
    $stmt->bind_param("ss", $name, $email);
    $stmt->execute();
    $user_id = $stmt->insert_id;

    // Find the counter with the lowest queue count
    $stmt = $conn->prepare("SELECT id FROM counter ORDER BY queue_count ASC LIMIT 1");
    $stmt->execute();
    $result = $stmt->get_result();
    $counter = $result->fetch_assoc();
    $counter_id = $counter['id'];

    // Get next queue number
    $stmt = $conn->prepare("SELECT IFNULL(MAX(queue_number) + 1, 1) AS next_queue FROM transactions");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $queue_number = $row['next_queue'];

    // Generate a unique token number
    $token_number = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));

    // Insert queue details into transactions
    $stmt = $conn->prepare("INSERT INTO transactions (user_id, counter_id, queue_number, token_number, status) VALUES (?, ?, ?, ?, 'pending')");
    $stmt->bind_param("iiis", $user_id, $counter_id, $queue_number, $token_number);
    $stmt->execute();

    // Update counter queue count
    $stmt = $conn->prepare("UPDATE counters SET queue_count = queue_count + 1 WHERE id = ?");
    $stmt->bind_param("i", $counter_id);
    $stmt->execute();

    // Redirect user to queue display page
    header("Location: ../queue_display.php?token=$token_number");
    exit();
}
?>
