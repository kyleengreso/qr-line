<?php
include "./../base.php"
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
        <div class="container d-flex justify-content-center before-footer" style="margin-top: 25px;transform:scale(0.8)">
        <div class="card shadow-sm p-4" style="max-width: 1000px; width: 100%;">
            <div class="w-100">
                <h4 class="text-center fw-bold fs-1"><?php echo $project_name ?> Stats</h4>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="text-center bg-white p-4 border border-2 shadow-sm rounded">
                        <h3 class="fs-2 fw-bold">25,198</h3>
                        <p class="text-muted mb-0">Total Transactions</p>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-4 m-0 p-0 d-flex align-items-stretch">
                    <div class="p-3 w-100">
                        <div class="text-center bg-white p-4 border border-2 shadow-sm rounded h-100">
                            <i class="bi bi-people fs-1 text-primary mb-3"></i>
                            <h3 class="fs-2 fw-bold">0</h3>
                            <p class="text-muted mb-0">Transaction Today</p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-4 m-0 p-0 d-flex align-items-stretch">
                    <div class="p-3 w-100">
                        <div class="text-center bg-white p-4 border border-2 shadow-sm rounded h-100">
                            <i class="bi bi-people-fill fs-1 text-primary mb-3"></i>
                            <h3 class="fs-2 fw-bold">6</h3>
                            <p class="text-muted mb-0">Transaction Yesterday</p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-4 m-0 p-0 d-flex align-items-stretch">
                    <div class="p-3 w-100">
                        <div class="text-center bg-white p-4 border border-2 shadow-sm rounded h-100">
                            <i class="bi bi-calendar fs-1 text-primary mb-3"></i>
                            <h3 class="fs-2 fw-bold">6,902</h3>
                            <p class="text-muted mb-0">This Month</p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-4 m-0 p-0 d-flex align-items-stretch">
                    <div class="p-3 w-100">
                        <div class="text-center bg-white p-4 border border-2 shadow-sm rounded h-100">
                            <i class="bi bi-calendar-fill fs-1 text-primary mb-3"></i>
                            <h3 class="fs-2 fw-bold">13,023</h3>
                            <p class="text-muted mb-0">Last 3 months</p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-4 m-0 p-0 d-flex align-items-stretch">
                    <div class="p-3 w-100">
                        <div class="text-center bg-white p-4 border border-2 shadow-sm rounded h-100">
                            <i class="bi bi-calendar-fill fs-1 text-primary mb-3"></i>
                            <h3 class="fs-2 fw-bold">13,023</h3>
                            <p class="text-muted mb-0">This Year</p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-4 m-0 p-0 d-flex align-items-stretch">
                    <div class="p-3 w-100">
                        <div class="text-center bg-white p-4 border border-2 shadow-sm rounded h-100">
                            <i class="bi bi-graph-up fs-1 text-primary mb-3"></i>
                            <h3 class="fs-2 fw-bold">25,198</h3>
                            <p class="text-muted mb-0">Total Transactions</p>
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
