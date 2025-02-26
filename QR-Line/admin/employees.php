<?php
session_start();

include "./../includes/db_conn.php";
include "./../base.php";
include "./../asset/php/message.php";
if ($_SERVER["REQUEST_METHOD"] == "GET") {

    $message_success = $_SESSION['message-success'] ?? null;
    $message_error = $_SESSION['message-error'] ?? null;

    // GET all list from employees

    $stmt = $conn->prepare("SELECT id, username, created_at FROM employees");
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $employees = $result->fetch_all(MYSQLI_ASSOC);
    } else {
        $employees = [];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employees List | <?php echo $project_name?></title>
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
                <?php if (isset($message_success)) {
                    message_success($message_success);
                    unset($_SESSION['message-success']);
                } else if (isset($message_error)) {
                    message_error($message_error);
                    unset($_SESSION['message-error']);
                }
                ?>
                    <div class="row w-100 mb-4">
                        
                        <div class="col col-9">
                            <h4 class="text-center">Employees List</h4>
                        </div>
                    
                        <div class="col col-3 p-0">
                        <button class="btn btn-success" onclick="window.location.href='./add_employee.php'">Add Employees</button>
                        </div>
                    
                    </div>


                    <table class="table table-striped" id="table-members">
                        <tr>
                            <th>Username</th>
                            <th>Created At</th>
                            <th>Operations</th>
                        </tr>
                        <?php for ($i = 0; $i < count($employees); $i++): ?>
                        <tr>
                            <td><strong><?php echo $employees[$i]['username']; ?></strong></td>
                            <td><?php echo $employees[$i]['created_at']; ?></td>
                            <td>
                                <div class="d-flex flex-column flex-md-row justify-content-center p-0 w-100">
                                    <button class="btn btn-primary m-1" onclick="window.location.href='./edit_employee.php?id=<?php echo $employees[$i]['id'];?>'">Edit</button>
                                    <button class="btn btn-danger m-1" onclick="window.location.href='./delete_employee.php?id=<?php echo $employees[$i]['id'];?>'">Delete</button>
                                </div>
                            </td>
                        </tr>
                        <?php endfor; ?>
                    </table>

                </div>
            </div>
        </div>
    </div>

    <script src="./../asset/js/bootstrap.bundle.js"></script>
</body>
</html>
