    <?php
session_start();
include "./../includes/db_conn.php";
include "./../base.php";

// if (isset($_SESSION["employee_id"])) {
//     header("Location: counter.php");
//     exit();
// }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | <?php echo $project_name?></title>
    <link rel="stylesheet" href="./../asset/css/bootstrap.css">
    <link rel="stylesheet" href="./../asset/css/theme.css">
    <script src="https://kit.fontawesome.com/0aa2c3c0f4.js" crossorigin="anonymous"></script>
</head>
<body class="bg">
    <?php include "./../includes/navbar.php"; ?>

    <div class="container d-flex justify-content-center align-items-center" style="margin-top: 10vh">
        <div class="card shadow-sm p-4" style="max-width: 400px; width: 100%;">
            <h4 class="text-center mb-4">Employee Register</h4>

            <form method="POST" id="frmRegister">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text" class="form-control" name="username" id="username" placeholder="Enter your username">
                    </div>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" class="form-control" name="password" id="password" placeholder="Enter your password">
                    </div>
                </div>
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" class="form-control" name="confirm_password" id="confirm_password" placeholder="Confirm your password">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary w-100">Register</button>
                <p class="text-center mt-3"><a class="register text-decoration-none" href="login.php">Login</a></p>
            </form>
        </div>
    </div>

    <script src="./../asset/js/bootstrap.bundle.js"></script>
    <script src="./../asset/js/jquery-3.6.0.min.js"></script>
    <script src="./../asset/js/message.js"></script>
    <script src="./../asset/js/authenticate.js"></script>
</body>
</html>
