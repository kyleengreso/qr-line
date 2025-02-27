<?php
session_start();
include './../includes/db_conn.php';
include './../base.php';
include './../asset/php/message.php';
// Uncomment this, when the auth dev is complete
// if (isset($_SESSION["employee_id"])) {
//     header("Location: counter.php");
//     exit();
// }

// if ($_SERVER["REQUEST_METHOD"] == "POST") {
//     $success_message = $_SESSION['message-success'] ?? null;
//     $error_message = $_SESSION['message-error'] ?? null;

//     $username = trim($_POST['username']);
//     $password = trim($_POST['password']);

//     $stmt = $conn->prepare("SELECT id, password FROM employees WHERE username = ?");
//     $stmt->bind_param("s", $username);
//     $stmt->execute();
//     $result = $stmt->get_result();
//     $user = $result->fetch_assoc();
        
//     if ($user && password_verify($password, $user['password'])) {
//         $_SESSION['employee_id'] = $user['id'];
//         header("Location: ./../counter.php");
//         exit();
//     } else {
//         $error_message = "Invalid username or password.";
//     }
// } else {
//     $success_message = $_SESSION['message-success'] ?? null;
// }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | <?php echo $project_name?></title>
    <link rel="stylesheet" href="./../asset/css/bootstrap.css">
    <link rel="stylesheet" href="./../asset/css/theme.css">
    <script src="https://kit.fontawesome.com/0aa2c3c0f4.js" crossorigin="anonymous"></script>
</head>
<body class="bg">
    <?php include "./../includes/navbar.php"; ?>

    <div class="container d-flex justify-content-center align-items-center" style="margin-top: 10vh">
        <div class="card shadow-sm p-4" style="max-width: 400px; width: 100%;">
            <h4 class="text-center mb-4">Employee Login</h4>
            <?php if (isset($error_message)) {
                message_error($error_message);
                unset($_SESSION['message-error']);
            } else if (isset($success_message)) {
                message_success($success_message);
                unset($_SESSION['message-success']);
            }
            ?>
            <form method="POST" id="frmLogIn">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text" class="form-control" name="username" id="username" placeholder="Enter your username" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" class="form-control" name="password" id="password" placeholder="Enter your password" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary w-100">Login</button>
                <p class="text-center mt-3"><a class="register text-decoration-none" href="register.php">Register</a></p>
            </form>
        </div>
    </div>

    <script src="./../asset/js/bootstrap.bundle.js"></script>
    <script src="./../asset/js/jquery-3.6.0.min.js"></script>
    <script src="./../asset/js/authenticate.js"></script>
</body>
</html>
