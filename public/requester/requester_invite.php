<?php
include './../base.php';
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
                <img class="p-2 w-100 h-100" id="qr_code_img" src="" alt="qr_code">
            </div>
            <div class="w-100 text-center">
                <h3>Scan the QR Code</h3>
            </div>
        </div>
    </div>

    <?php after_js()?>
    <script>
        let qr_code_link = document.getElementById("qr_code_link");
        let qr_code_img = document.getElementById("qr_code_img");
        qr_code_img.src = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" + realHost;
    </script>
</body>
<?php include_once "./../includes/footer.php"; ?>
</html>
