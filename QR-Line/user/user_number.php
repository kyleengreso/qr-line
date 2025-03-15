<?php
include './../base.php';
include './../../vendor/autoload.php';

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php head_icon()?>
    <title>Queue | <?php echo $project_name?></title>
    <?php head_css()?>
    <?php before_js()?>
    <link rel="stylesheet" href="./../asset/css/user_number.css">
    <?php
    $requester_token = $_SESSION['requester_token'];
    $web_domain = $_SERVER['HTTP_HOST'];
    $web_resource = $_SERVER['REQUEST_URI'];
    $website = $web_domain . $web_resource . '?requester_token=' . $requester_token;
    ?>
</head>
<body class="bg">
    <?php include "./../includes/navbar.php"; ?>

    <div class="container col d-flex justify-content-center align-items-center" style="margin-top: 15vh;flex-direction:column">
        <div class="row" id="circle-info">

        </div>

        <div class="row circle text-center">
            <?php 
            echo '<img src="' . (new QRCode)->render($website) . '" alt="Queue Icon" class="queue-icon" id="qr-code-img">'; ?>
        
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
    <div class="container d-flex justify-content-center align-items-center">
        <!-- set white div -->
        <div class="mt-4 rounded-start p-4 d-flex justify-content-center" style="width: 100%">
            <a class="btn btn-primary text-white fw-bold" id="btnCancelRequest">Cancel Request</a>
        </div>
    </div>
    <script src="./../asset/js/jquery-3.7.1.js"></script>
    <script src="./../asset/js/message.js"></script>
    <script src="./../asset/js/user_number.js"></script>
</body>
</html>
