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
$qrDataForm = (new QRCode($options))->render($origin . '/public/requester/requester_form.php');
$qrDataFormPriority = (new QRCode($options))->render($origin . '/public/requester/requester_form_priority.php');
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
</head>
<body class="bg">
    <?php include "./../includes/navbar_non.php"; ?>
    <div class="flex justify-center px-4 pt-24 pb-8 min-h-[600px]">
        <div class="w-full max-w-lg">
            <div class="bg-white rounded-2xl shadow p-6">
                <div class="text-center mb-4">
                    <img src="./../asset/images/logo_blk.png" alt="<?php echo $project_name?>" class="w-20 mx-auto mb-2">
                    <h4 class="text-xl font-bold">Join the queue</h4>
                    <p class="text-gray-500 text-sm">Scan a QR code or copy a link to join</p>
                </div>
                <div class="flex flex-wrap justify-center gap-6">
                    <div class="flex flex-col items-center">
                        <p class="text-lg font-semibold text-psu-orange mb-2">Standard Form</p>
                        <div class="border rounded-lg p-2 bg-white">
                            <img id="qr_code_form" src="<?php echo $qrDataForm; ?>" alt="QR code for form" class="w-44 h-auto">
                        </div>
                    </div>
                    <div class="flex flex-col items-center">
                        <p class="text-lg font-semibold text-psu-orange mb-2">Priority Form</p>
                        <div class="border rounded-lg p-2 bg-white">
                            <img id="qr_code_priority" src="<?php echo $qrDataFormPriority; ?>" alt="QR code for priority form" class="w-44 h-auto">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php after_js()?>
<script>
(function(){
    var copyBtns = document.querySelectorAll('.copy-link-btn');
    copyBtns.forEach(function(btn){
        btn.addEventListener('click', function(){
            var url = this.getAttribute('data-url');
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(url).then(function(){
                    btn.textContent = 'Copied';
                    setTimeout(function(){ btn.textContent = 'Copy'; }, 1200);
                }).catch(function(){ btn.textContent = 'Copy'; });
            } else {
                var ta = document.createElement('textarea');
                ta.value = url;
                document.body.appendChild(ta);
                ta.select();
                try { document.execCommand('copy'); btn.textContent = 'Copied'; } catch(e){}
                document.body.removeChild(ta);
                setTimeout(function(){ btn.textContent = 'Copy'; }, 1200);
            }
        });
    });
})();
</script>
</body>
<?php include_once "./../includes/footer.php"; ?>
</html>
