<?php
include './../base.php';
include './../../vendor/autoload.php';

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
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
    <?php include "./../includes/navbar_non.php"; ?>
    <div class="container d-flex justify-content-center align-items-center container-set" style="margin-top:100px;flex-direction:column">
        <div class="row circle text-center">
            <div>
                <img src="./../asset/images/logo_blk.png" alt="logo" width="75px" style="margin-top: -15px;">
            </div>
            <div class="info-container">
                <div class="info-box">
                    <p class="label">Number:</p>
                    <p class="value" id="queueNumber">N/A</p>
                </div>
                <div class="info-box">
                    <p class="label">Counter:</p>
                    <p class="value" id="counterNumber">N/A</p>
                </div>
            </div>
            <p class="current-number">Current number: <strong><span id="currentQueueNumber">N/A</span></strong></p>
        </div>
        <div class="d-flex justify-content-center align-items-center">
            <div class="mt-4 rounded-start p-4 d-flex justify-content-center" style="width: 100%">
                <a class="btn btn-primary text-white fw-bold" id="btnCancelRequestModal" data-toggle="modal" data-target="#requestCancelModal">Cancel Request</a>
            </div>
        </div>
    </div>

    <div class="modal fade" id="requestCancelModal" tabindex="-1" role="dialog"  aria-hidden="true" style="margin-top: 100px;">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-orange-custom d-flex justify-content-start text-white">
                <h5 class="modal-title fw-bold" id="viewEmployeeTitle">Cancel Transaction?</h5>
                </div>
                <div class="modal-body py-4 px-6" id="viewEmployeeBody">
                    Do you want to cancel you current transaction?
                </div>
                <div class="modal-footer col" id="viewEmployeeFooter">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal" id="btnCancelRequest">Cancel</button>
                </div>
            </div>
        </div>
    </div>
    <?php after_js()?>
    <script src="./../asset/js/user_number.js"></script>
</body>
<?php include_once "./../includes/footer.php"; ?>
</html>
