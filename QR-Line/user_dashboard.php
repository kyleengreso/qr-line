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
    <script src="https://kit.fontawesome.com/0aa2c3c0f4.js" crossorigin="anonymous"></script>
    <style>
        .circle {
            width: 500px;
            height: 500px;
            border-radius: 50%;
            border: 5px solid #ff6600;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            background-color: white;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
            position: relative;
            padding: 20px;
        }

        .queue-icon {
            width: 100px;
            margin-bottom: 15px;
        }

        .info-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 80%;
        }

        .info-box {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 45%;
        }

        .label {
            font-size: 22px;
            font-weight: bold;
            margin: 8px 0;
        }

        .value {
            font-size: 55px;
            font-weight: bold;
            margin: 0;
        }

        .current-number {
            font-size: 24px;
            font-weight: bold;
            margin-top: 20px;
        }
    </style>
</head>
<body class="bg-light">
    <?php include "includes/navbar.php"; ?>
    <div class="container d-flex justify-content-center align-items-center" style="margin-top: 10vh">
        <div class="circle text-center">
            <img src="asset/images/logo.gif" alt="Queue Icon" class="queue-icon">
            <div class="info-container">
                <div class="info-box">
                    <p class="label">Number:</p>
                    <p class="value"><?php echo htmlspecialchars($queueNumber); ?></p>
                </div>
                <div class="info-box">
                    <p class="label">Counter:</p>
                    <p class="value"><?php echo htmlspecialchars($counterNumber); ?></p>
                </div>
            </div>
            <p class="current-number">Current number: <strong><?php echo htmlspecialchars($currentQueueNumber); ?></strong></p>
        </div>
    </div>
</body>
</html>
