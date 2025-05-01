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
    <title>Request Account | <?php echo $project_name?></title>
    <?php head_css()?>
    <?php before_js()?>
</head>
<body class="bg">
    <?php include "./../includes/navbar_non.php"; ?>

    <div class="container d-flex justify-content-center align-items-center before-footer container-set" style="margin-top:100px">
        <div class="card shadow-sm p-4 w-100" style="max-width: 400px;border-radius:30px">
            <div class="w-100 py-3">
                <img src="./../asset/images/logo_blk.png" alt="<?php echo $project_name?>" class="img-fluid mx-auto d-block" style="max-width: 100px">
            </div>
            <div class="text-center">
                <h5 class="text-center fw-bold">Welcome to <?php echo $project_name?></h5>
                <p>Request Account</p>
            </div>
            <form method="POST" id="frmRegister">
                <div class="form-floating mb-2">
                    <input type="text" class="form-control" name="username" id="username" placeholder="Username">
                    <label for="username">Username</label>
                </div>
                <div class="form-floating mb-2">
                    <input type="text" class="form-control" name="email" id="email" placeholder="Email">
                    <label for="email">Email</label>
                </div>
                <div class="form-floating mb-2">
                    <input type="password" class="form-control" name="password" id="password" placeholder="Password">
                    <label for="password">Password</label>
                </div>
                <div class="form-floating">
                    <input type="password" class="form-control" name="confirm_password" id="confirm_password" placeholder="Confirm Password">
                    <label for="confirm_password">Confirm Password</label>
                </div>
                <button type="submit" class="btn btn-primary w-100">Request Account</button>
                <p class="text-center mt-3"><a class="register text-decoration-none" href="login.php">Login</a></p>
            </form>
        </div>
    </div>
    <?php after_js()?>
    <script src="./../asset/js/message.js"></script>
    <script src="./../asset/js/authenticate.js"></script>
    <?php include_once "./../includes/footer.php"; ?>
</body>
</html>
