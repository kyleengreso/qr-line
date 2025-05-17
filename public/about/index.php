<?php
include "./../base.php";

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php head_icon()?>
    <title>QR Form | <?php echo $project_name?></title>
    <?php head_css()?>
    <?php before_js()?>
</head>
<body class="bg">
    <?php include "./../includes/navbar_non.php"; ?>
    <div class="container d-flex justify-content-center align-items-center" style="margin-top: 100px;min-height: 600px;">
        <div class="card shadow-sm p-4 mb-4" style="width: 100%;border-radius:30px">
            <div class="w-100 py-3">
                <img src="./../asset/images/logo_blk.png" alt="<?php echo $project_name?>" class="img-fluid mx-auto d-block" style="max-width: 100px">
            </div>
            <div class="text-center">
                <div class="mb-4">
                    <h3>About this project</h3>
                </div>
                <div class="mb-4">
                    <h5>Developers</h5>
                    <div class="row">
                        <div class="col-6 col-lg-3 p-0 m-0">
                            <div class="card shadow m-2 card-body text-center">
                                <div class="d-flex justify-content-center">
                                    <img src="https://avatars.githubusercontent.com/u/68708541" alt="aceday"
                                    class="rounded-circle img-fluid" style="width: 150px;">
                                </div>
                                    <a class="text-decoration-none text-black" href="https://github.com/aceday">
                                        <h5 class="my-3">aceday</h5>
                                    </a>
                                <p class="text-muted mb-1">Mark Cedie Buday</p>
                                <p class="text-muted mb-4">BSCS-3 B1</p>
                                <div class="d-flex justify-content-center mb-2">
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-lg-3 p-0 m-0">
                            <div class="card shadow m-2 card-body text-center">
                                <div class="d-flex justify-content-center">
                                    <img src="https://avatars.githubusercontent.com/u/125529632" alt="kyleengreso"
                                    class="rounded-circle img-fluid" style="width: 150px;">
                                </div>
                                <a class="text-decoration-none text-black" href="https://github.com/kyleengreso">
                                    <h5 class="my-3">kyleengreso</h5>
                                </a>
                                <p class="text-muted mb-1">Kyle Engreso</p>
                                <p class="text-muted mb-4">BSCS-3 B1</p>
                                <div class="d-flex justify-content-center mb-2">
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-lg-3 p-0 m-0">
                            <div class="card shadow m-2 card-body text-center">
                                <div class="d-flex justify-content-center">
                                    <img src="https://avatars.githubusercontent.com/u/120568365" alt="NaldCapuno"
                                    class="rounded-circle img-fluid" style="width: 150px;">
                                </div>
                                <a class="text-decoration-none text-black" href="https://github.com/NaldCapuno">
                                    <h5 class="my-3">NaldCapuno</h5>
                                </a>
                                <p class="text-muted mb-1">Ronald Capuno</p>
                                <p class="text-muted mb-4">BSCS-3 B1</p>
                                <div class="d-flex justify-content-center mb-2">
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-lg-3 p-0 m-0">
                            <div class="card shadow m-2 card-body text-center">
                                <div class="d-flex justify-content-center">
                                    <img src="https://avatars.githubusercontent.com/u/141973593" alt="aceday"
                                    class="rounded-circle img-fluid" style="width: 150px;">
                                </div>
                                <a class="text-decoration-none text-black" href="https://github.com/PaelMacalinao">
                                    <h5 class="my-3">PaelMacalinao</h5>
                                </a>
                                <p class="text-muted mb-1">John Rafael Macalinao</p>
                                <p class="text-muted mb-4">BSCS-3 B1</p>
                                <div class="d-flex justify-content-center mb-2">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>

        </div>
    </div>

<?php after_js()?>
    <script src="./../asset/js/message.js"></script>
    <script src="./../asset/js/user_form.js"></script>
    <!-- <script src="./../asset/js/counters.js"></script> -->
</body>
<?php include_once "./../includes/footer.php"; ?>
</html>
