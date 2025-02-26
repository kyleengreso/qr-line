<?php
session_start();
include "./../includes/db_conn.php";
include "./../base.php";
include "./../asset/php/message.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $id = $_GET['id'];

    if ($password !== $confirm_password) {
        $error_message = "Passwords do not match.";
    } else {

        if (empty($password)) {
            $stmt = $conn->prepare("UPDATE employees SET username = ? WHERE id = ?");
            $stmt->bind_param("ss", $username, $id);
        } else {
            $hash_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE employees SET username = ?, password = ? WHERE id = ?");
            $stmt->bind_param("sss", $username, $hash_password, $id);
        }

        try {
            if ($stmt->execute()) {
                $_SESSION['message-success'] = "Employee updated successfully!";
                header("Location: ./employees.php");
            } else {
                $_SESSION['message-error'] = "Error: " . $conn->error;
                header("Location: ./employees.php");
            }
        } catch (Exception $e) {
            $_SESSION['message-error'] = "Error: " . $e->getMessage();
            header("Location: ./employees.php");
        }
    }
} else {
    // Get employee data
    if (isset($_GET['id'])) {

        $id = $_GET['id'];
        $stmt = $conn->prepare("SELECT * FROM employees WHERE id = ?");
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $employee = $result->fetch_assoc();

        if ($result->num_rows == 1) {
            if ($employee) {
                // $employee_id = $employee['id'];              // RESERVED //
                $employee_username = $employee['username'];
            }
        } else {
            $_SESSION['message-error'] = "Employee not found.";
            header("Location: ./employees.php");
        }
    } else {
        header("Location: ./employees.php");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Employee | <?php echo $project_name?></title>
    <link rel="stylesheet" href="./../asset/css/bootstrap.css">
    <link rel="stylesheet" href="./../asset/css/theme.css">
    <script src="https://kit.fontawesome.com/0aa2c3c0f4.js" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <?php include "./../includes/navbar.php"; ?>

    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6">
                <div class="card shadow-sm p-4">
                    <h4 class="text-center mb-4">Edit Employee</h4>

                    <?php if (isset($success_message)): ?>
                        <div class="alert alert-success"><?php echo $success_message; ?></div>
                    <?php elseif (isset($error_message)): ?>
                        <div class="alert alert-danger"><?php echo $error_message; ?></div>
                    <?php endif; ?>

                    <form id="employeeForm" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" name="username" class="form-control" placeholder="Enter username" value="<?php echo $employee_username?>"required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" name="password" class="form-control" placeholder="Enter password">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Confirm Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" name="confirm_password" class="form-control" placeholder="Confirm password">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Update Employee</button>
                        <div class="text-center p-2">
                            <a class="text-a-black" href="./employees.php">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>