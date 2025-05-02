<?php
include './../base.php';
include './../../vendor/autoload.php';

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
$data = "sample";
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
    <?php
    $web_domain = $_SERVER['HTTP_HOST'];
    ?>
</head>
<body class="bg">
    <?php include "./../includes/navbar_non.php"; ?>
    <div class="container d-flex justify-content-center" style="margin-top:100px;min-height:600px">
        <div class="col card shadow p-4" style="max-height:500px;max-width: 400px;border-radius:30px">
            <div class="w-100 text-center">
                <h3>
                    Join the queue
                </h3>
            </div>
            <div class="w-100">
            <?php
            // echo $serverName; // Debug
            echo '<img src="'.(new QRCode)->render($serverName).'" alt="QR Code" />';
            ?>
            </div>
            <div class="w-100 text-center">
                <h3>Scan the QR Code</h3>
            </div>
        </div>
    </div>

    <?php after_js()?>
</body>
<?php include_once "./../includes/footer.php"; ?>
</html>
