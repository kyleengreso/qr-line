<?php
session_start();
include 'includes/db_conn.php'; // Database connection

$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password.";
    } else {
        $stmt = $conn->prepare("SELECT id, password FROM employees WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['employee_id'] = $user['id'];
            header("Location: employee_dashboard.php");
            exit();
        } else {
            $error = "Invalid username or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | QR-Line</title>
    <link rel="stylesheet" href="asset/css/bootstrap.css">
    <link rel="stylesheet" href="asset/css/style.css">
    <script src="https://kit.fontawesome.com/0aa2c3c0f4.js" crossorigin="anonymous"></script>
    <style>
        .bg {
            background: url('asset/images/background.jpg') no-repeat center center fixed;
            background-size: cover;
            position: relative;
        }
        .btn-primary {
            background-color: rgb(255, 110, 55);
            border-color: rgb(255, 110, 55);
        }
        .btn-primary:hover {
            background-color: rgb(230, 100, 50);
            border-color: rgb(230, 100, 50);
        }
        .input-group-text {
            background-color: rgb(255, 110, 55);
            color: white;
            border-color: rgb(255, 110, 55);
        }
        .register {
            color: rgb(255, 110, 55);
        }
        .register:hover {
            color: rgb(230, 100, 50);
        }
    </style>
</head>
<body class="bg">
    <?php include "includes/navbar.php"; ?>

    <div class="container d-flex justify-content-center align-items-center" style="margin-top: 10vh">
        <div class="card shadow-sm p-4" style="max-width: 400px; width: 100%;">
            <h4 class="text-center mb-4">Employee Login</h4>
            <?php if ($error): ?>
                <div class="alert alert-danger"> <?= $error ?> </div>
            <?php endif; ?>
            <form method="POST">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text" class="form-control" name="username" placeholder="Enter your username">
                    </div>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" class="form-control" name="password" placeholder="Enter your password">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary w-100">Login</button>
                <p class="text-center mt-3"><a class="register" href="register.php">Register</a></p>
            </form>
        </div>
    </div>

    <script src="asset/js/bootstrap.bundle.js"></script>
</body>
</html>
