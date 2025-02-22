<?php
$queueNumber = 31;
$counterNumber = 2;
$currentNumber = 3;
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
        <p class="value"><?php echo $queueNumber; ?></p>
        <p class="label">Counter:</p>
        <p class="value"><?php echo $counterNumber; ?></p>
        <p class="current-number">Current number: <strong><?php echo $currentNumber; ?></strong></p>
    </div>
</body>
</html>