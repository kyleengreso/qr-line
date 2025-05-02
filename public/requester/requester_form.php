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
    <div class="container d-flex justify-content-center align-items-center container-set" style="margin-top: 100px;min-height: 600px;">
        <div class="card shadow-sm p-4" style="max-width: 400px;width: 100%;border-radius:30px">
            <div class="w-100 py-3">
                <img src="./../asset/images/logo_blk.png" alt="<?php echo $project_name?>" class="img-fluid mx-auto d-block" style="max-width: 100px">
            </div>
            <h4 class="text-center fw-bold">QR FORM</h4>
            <p class="text-center text-muted">PLEASE FILL UP</p>

            <form method="post" id="frmUserForm">
                <div class="form-floating mb-2">
                    <input type="text" class="form-control" id="name" name="name" placeholder="Enter Your Name" required>
                    <label for="name" class="form-label">Name</label>
                </div>
                <div class="form-floating mb-2">
                    <input type="email" class="form-control" id="email" name="email" placeholder="Enter Your Email Address" required>
                    <label for="email" class="form-label">Email</label>
                </div>

                <div class="form-floating mb-3">
                    <select class="form-select"  name="transaction-history-payment" id="transaction-history-payment">
                        <option value="null">Payment</option>
                        <option value="registrar">Registrar</option>
                        <option value="assessment">Assessment</option>
                    </select>
                    <label for="email" class="form-label">Payment</label>
                </div>

                <button type="submit" class="btn btn-primary w-100">Next</button>
            </form>
        </div>
    </div>

<?php after_js()?>
    <script src="./../asset/js/message.js"></script>
    <script src="./../asset/js/user_form.js"></script>
    <!-- <script src="./../asset/js/counters.js"></script> -->
</body>
<?php include_once "./../includes/footer.php"; ?>
</html>
