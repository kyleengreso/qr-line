<?php
include_once __DIR__ . '/../base.php';

restrictCheckLoggedIn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <?php head_icon()?>
    <title>Login | <?php echo $project_name?></title>
    <?php head_css()?>
    <?php before_js()?>
</head>
<body class="bg">
    <?php include "./../includes/navbar_non.php"; ?>

    <div class="container d-flex justify-content-center align-items-center container-set" style="margin-top: 100px;min-height: 600px;">
        <div class="card shadow-sm p-4 w-100" style="max-width: 400px;border-radius:30px">
            <div class="w-100 py-3">
                <img src="./../asset/images/logo_blk.png" alt="<?php echo $project_name?>" class="img-fluid mx-auto d-block" style="max-width: 100px">
            </div>
            <div class="text-center">
                <h5 class="text-center fw-bold">Welcome to <?php echo $project_name?></h5>
                <p>Login to continue</p>
            </div>
            <form class="needs-validation" method="POST" id="frmLogIn" novalidate>
                <div class="input-group mb-2">
                    <div class="input-group-text" id="basic-addon1"><i class="bi bi-person-fill"></i></div>
                    <div class="form-floating">
                        <input type="text" class="form-control" name="username" id="username" placeholder="Username" required>
                        <label for="username">Username</label>
                    </div>
                </div>
                <div class="input-group mb-4">
                    <div class="input-group-text" id="basic-addon1"><i class="bi bi-lock-fill"></i></div>
                    <div class="form-floating">
                        <input type="password" class="form-control" name="password" id="password" placeholder="Password" required>
                        <label for="password">Password</label>
                    </div>
                </div>
                <div class="mb-2">
                    <button type="submit" class="btn btn-primary w-100">Login</button>
                    <p class="text-center mt-3"><a class="forgot-password text-decoration-none" href="forgot_password.php">Forgot Password?</a></p>
                    <?php if ($enable_register_employee) : ?>
                    <p class="text-center mt-3"><a class="register text-decoration-none" href="register.php">Request Account</a></p>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <?php after_js()?>
    <script src="./../asset/js/message.js"></script>
    <script src="./../asset/js/authenticate.js"></script>
    <?php include_once "./../includes/footer.php"; ?>
</body>
</html>