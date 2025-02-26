<?php
session_start();
include "./../includes/db_conn.php";
include "./../base.php";
include "./../asset/php/message.php";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $success_message = $_SERVER['message-success'] ?? null;
    $error_message = $_SERVER['message-error'] ?? null;

    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $error_message = "Password does not match.";
    } else {

        // Load if user is exists
        $stmt = $conn->prepare("SELECT id FROM employees WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error_message = "Username already exists.";
        } else {
            $hash_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO employees (username, password) VALUES (?, ?)");
            $stmt->bind_param("ss", $username, $password);
        
            if ($stmt->execute()) {
                $_SESSION['message-success'] = "Employee registered successfully!";
                header("Location: ./employees.php");
            } else {
                $error_message = "Error: " . $conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Employee | <?php echo $project_name?></title>
    <link rel="stylesheet" href="./../asset/css/bootstrap.css">
    <link rel="stylesheet" href="./../asset/css/theme.css">
    <script src="https://kit.fontawesome.com/0aa2c3c0f4.js" crossorigin="anonymous"></script>
</head>
<body>
    <?php include "./../includes/navbar.php"; ?>

    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6">
                <div class="card shadow-sm p-4">
                    <h4 class="text-center mb-4">Add Employee</h4>

                    <?php
                    if (isset($error_message)) {
                        message_error($error_message);
                        unset($error_message);
                    }
                    ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" name="username" class="form-control" placeholder="Enter username" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" name="password" class="form-control" placeholder="Enter password" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Confirm Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" name="confirm_password" class="form-control" placeholder="Confirm password" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Add Employee</button>
                        <div class="text-center p-2">
                            <a class="text-a-black" href="./employees.php">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="./../asset/js/bootstrap.bundle.js"></script>
</body>
</html>
