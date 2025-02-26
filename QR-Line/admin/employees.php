<?php

include "./../includes/db_conn.php";
include "./../base.php";

if ($_SERVER["REQUEST_METHOD"] == "GET") {

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
                    <h4 class="text-center mb-4">Employees List</h4>

                    <?php if (isset($success_message)): ?>
                        <div class="alert alert-success"><?php echo $success_message; ?></div>
                    <?php elseif (isset($error_message)): ?>
                        <div class="alert alert-danger"><?php echo $error_message; ?></div>
                    <?php endif; ?>


                    <table class="table table-striped" id="table-members">
                        <tr>
                            <th>Username</th>
                            <th>Created At</th>
                            <th>Operations</th>
                        </tr>
                        <?php for ($i = 0; $i < count($employees); $i++): ?>
                        <tr>
                            <td><?php echo $employees[$i]['username']; ?></td>
                            <td><?php echo $employees[$i]['created_at']; ?></td>
                            <td><a class="text-a-black" href="./edit_employee.php?id=<?php echo $employees[$i]['id'];?>">Edit</a></td>
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
