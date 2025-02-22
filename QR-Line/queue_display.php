<?php
include '<includes/db_conn.php';

$token_number = $_GET['token'] ?? '';

// Get queue details
$stmt = $conn->prepare("SELECT queue_number, counter_id FROM transactions WHERE token_number = ?");
$stmt->bind_param("s", $token_number);
$stmt->execute();
$result = $stmt->get_result();
$transaction = $result->fetch_assoc();

if (!$transaction) {
    die("Invalid token.");
}

// Get counter details
$stmt = $conn->prepare("SELECT counterNumber FROM counters WHERE id = ?");
$stmt->bind_param("i", $transaction['counter_id']);
$stmt->execute();
$result = $stmt->get_result();
$counter = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Queue Display | QR-Line</title>
    <link rel="stylesheet" href="asset/css/bootstrap.css">
</head>
<body>
    <div class="container text-center mt-5">
        <h2>Your Queue Number</h2>
        <h1 class="display-3"><?= $transaction['queue_number'] ?></h1>
        <h3>Counter: <?= $counter['counterNumber'] ?></h3>
        <p>Your Token: <strong><?= $token_number ?></strong></p>
    </div>
</body>
</html>
