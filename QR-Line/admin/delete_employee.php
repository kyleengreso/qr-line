<?php
session_start();
include "./../includes/db_conn.php";
include "./../base.php";
include "./../asset/php/message.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_GET['id'];

    $stmt = $conn->prepare("DELETE FROM employees WHERE id = ?");
    $stmt->bind_param("s", $id);

    // I can't tell the delete has trick to do
    try {
        if ($stmt->execute()) {
            $_SESSION['message-success'] = "Employee deleted successfully!";
            header("Location: ./employees.php");
        } else {
            $_SESSION['message-error'] = "Error: " . $conn->error;
            header("Location: ./employees.php");
        }
    } catch (Exception $e) {
        $_SESSION['message-error'] = "Error: " . $e->getMessage();
        header("Location: ./employees.php");
    }
} else if ($_SERVER["REQUEST_METHOD"] == "GET") {
    // header("Location: ./employees.php");
    if (isset($_GET['id'])) {
        $id = $_GET['id'];
        $stmt = $conn->prepare("SELECT id, username FROM employees WHERE id = ?");
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $employee = $result->fetch_assoc();

        if ($result->num_rows == 1) {
            $employee_username = $employee['username'];
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
    <title>Delete Employee | <?php echo $project_name?></title>
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
                    <h4 class="text-center mb-4">Delete Employee</h4>

                    <!-- <?php
                    // if (isset($_SERVER['message-success'])) {
                    //     message_success($_SERVER['message-success']);
                    //     header("Location: ./employees.php");
                    // } else if (isset($_SERVER['message-error'])) {
                    //     message_error($_SERVER['message-error']);
                    //     header("Location: ./employees.php");
                    // }
                    ?> -->

                    <form method="POST">

                        <label class="form-label">Do you want to delete this employee <strong><?php echo $employee_username;?></strong>?</label>

                        <div class="col col-12 offset-md-3 col-md-6 p-0">
                            <button type="submit" class="btn btn-danger w-100 w-md-50">Delete Employee</button>
                            <div class="text-center p-2">
                                <a class="text-a-black" href="./employees.php">Cancel</a>
                            </div>
                        </div>
                        

                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="./../asset/js/bootstrap.bundle.js"></script>
</body>
</html>
