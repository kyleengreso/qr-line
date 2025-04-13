<?php
include_once './../base.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $project_name?></title>
    <?php head_css()?>
    <?php before_js()?>
    <style>

    </style>
</head>
<body>
    <div class="container">
        <div class="p-4 justify-center" style="max-height: 150px; height: 300px;">
            <div>
                <img src="./../asset/images/favicon.png" alt="QR-Line-Logo" style="width: 100px; height: 100px;">
                <span class="pl-4 fw-bold fs-3" style=""><?php echo $project_name_full ?></span>
            </div>
        </div>
        <div class="card p-4 mx-0 my-2">
            <!-- make a paragrah -->
            <p class="fs-6 text-center">Your request has been proceed. Please check the details below.</p>

            <!-- your counter no is sentence -->
            <p class="fs-6 text-center">Your counter no is <span class="fs-bold fs-4">1</span></p>
            <div class="d-flex justify-content-center">
                <table class="table-members">
                    <tr><td class="fs-bold fs-6">Name</td><td>Marc Buday</td></tr>
                    <tr><td class="fs-bold fs-6">Email</td><td>webmaster@google.com</td></tr>
                    <tr><td class="fs-bold fs-6">Payment</td><td>Registrar</td></tr>
                </table>
            </div>

            <div class="col d-flex justify-content-center">
            <a href="https://www.google.com" class="btn btn-primary m-2">Check your request</a>
            <a href="https://www.google.com" class="btn btn-primary m-2">Cancel your request</a>
            </div>
        </div>

        <!-- feel free to contact -->
        <div class="card p-4 mx-0 my-2">
            <p class="fs-6 text-center">Feel free to contact us if you have any question.</p>
            <div class="d-flex justify-content-center">
                <div>
                    Email: <span class="mx-2"><a href="mailto:">webmaster@google.com</a></span>
                </div>
                <div>
                    Phone: <span class="mx-2s"><a href="tel:">0909-123-4567</a></span>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
