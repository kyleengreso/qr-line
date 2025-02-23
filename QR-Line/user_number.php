<?php
include 'includes/db_conn.php';

// Get the latest transaction for the user (assuming the last user added is the current one)
$stmt = $conn->prepare("SELECT queue_number, idcounter FROM transactions ORDER BY idtransaction DESC LIMIT 1");
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

$queueNumber = $row['queue_number'] ?? "N/A";
$counterNumber = $row['idcounter'] ?? "N/A";

// Get the smallest "pending" queue number for the assigned counter (i.e., the current number being served)
$stmt = $conn->prepare("SELECT MIN(queue_number) AS current_queue FROM transactions WHERE idcounter = ? AND status = 'pending'");
$stmt->bind_param("i", $counterNumber);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

$currentQueueNumber = $row['current_queue'] ?? "N/A";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR-Line: Palawan State University</title>
    <link rel="stylesheet" href="asset/css/bootstrap.css">
    <link rel="stylesheet" href="asset/css/user_number.css">
    <script src="https://kit.fontawesome.com/0aa2c3c0f4.js" crossorigin="anonymous"></script>
</head>
<body style="background: none;">
    <?php include "includes/navbar.php"; ?>
    <link rel="stylesheet" href="asset/css/user_style.css">

    <div class="circle">
        <img src="queue_icon.png" alt="Queue Icon" class="queue-icon">
        <p class="label">Number:</p>
        <p class="value"><?php echo htmlspecialchars($queueNumber); ?></p>
        <p class="label">Counter:</p>
        <p class="value"><?php echo htmlspecialchars($counterNumber); ?></p>
        <p class="current-number">Current number: <strong><?php echo htmlspecialchars($currentQueueNumber); ?></strong></p>
    </div>
</body>
</html>
