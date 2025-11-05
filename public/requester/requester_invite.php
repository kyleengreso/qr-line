<?php
include './../base.php';
@include_once __DIR__ . '/../../vendor/autoload.php';
use chillerlan\QRCode\{QRCode, QROptions};
use chillerlan\QRCode\Output\QROutputInterface;

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$origin = $scheme . '://' . $host;

$options = new QROptions([
    'outputType'   => QROutputInterface::GDIMAGE_PNG,
    'eccLevel'     => QRCode::ECC_M,
    'scale'        => 6,
    'outputBase64' => true,
]);

$qrData = (new QRCode($options))->render($origin);
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
            <div class="w-100" id="qr_code_link">
                <img class="p-2 mx-auto d-block" style="max-width:250px;width:100%;height:auto" id="qr_code_img" src="<?php echo $qrData; ?>" alt="qr_code">
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
