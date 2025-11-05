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
    <?php
    ?>
</head>
<body class="bg">
    <?php include "./../includes/navbar_non.php"; ?>
    <div class="container d-flex justify-content-center" style="margin-top:100px;min-height:600px">
        <div class="col-12 col-sm-10 col-md-8 col-lg-6">
            <div class="card shadow mb-4 p-4" style="border-radius:18px;">
                <div class="row g-3 align-items-center">
                    <div class="col-12 text-center">
                        <img src="./../asset/images/logo_blk.png" alt="<?php echo $project_name?>" style="max-width:88px" class="mb-2">
                        <h4 class="mb-1">Join the queue</h4>
                        <p class="text-muted small mb-3">Scan a QR code or copy a link to join</p>
                    </div>

                    <div class="col-12 d-flex justify-content-center gap-4 flex-wrap">
                        <div class="d-flex flex-column align-items-center">
                            <p class="small fw-semibold mb-2 h1 text-primary">Standard Form</p>
                            <div class="border rounded-3 p-2 bg-white" style="max-width:200px;">
                                <img id="qr_code_form" src="<?php echo $qrDataForm; ?>" alt="QR code for form" style="display:block;max-width:180px;width:100%;height:auto;margin:0 auto;">
                            </div>
                        </div>
                        <div class="d-flex flex-column align-items-center">
                            <p class="small fw-semibold mb-2 h1 text-primary">Priority Form</p>
                            <div class="border rounded-3 p-2 bg-white" style="max-width:200px;">
                                <img id="qr_code_priority" src="<?php echo $qrDataFormPriority; ?>" alt="QR code for priority form" style="display:block;max-width:180px;width:100%;height:auto;margin:0 auto;">
                            </div>
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
                    }).catch(function(){
                        btn.textContent = 'Copy';
                    });
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
