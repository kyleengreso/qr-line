<?php
include './../base.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Queue | <?php echo $project_name?></title>
    <link rel="stylesheet" href="./../asset/css/bootstrap.css">
    <link rel="stylesheet" href="./../asset/css/theme.css">
    <script src="https://kit.fontawesome.com/0aa2c3c0f4.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="./../asset/css/user_number.css">
</head>
<body class="bg">
    <?php include "./../includes/navbar.php"; ?>
    <div class="container d-flex justify-content-center align-items-center" style="margin-top: 15vh">
        <div class="circle text-center">
            <img src="./../asset/images/queue_icon.png" alt="Queue Icon" class="queue-icon">
            <div class="info-container">
                <div class="info-box">
                    <p class="label">Number:</p>
                    <p class="value" id="queueNumber"></p>
                </div>
                <div class="info-box">
                    <p class="label">Counter:</p>
                    <p class="value" id="counterNumber"></p>
                </div>
            </div>
            <p class="current-number">Current number: <strong><span id="currentQueueNumber"></span></strong></p>
        </div>
    </div>
    <script src="./../asset/js/jquery-3.7.1.js"></script>
    <script src="./../asset/js/user_number.js"></script>
</body>
</html>
