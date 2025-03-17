<?php
include "./../includes/db_conn.php";
include "./../base.php";

login_as_employee();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php head_icon()?>
    <title>Edit Employee | <?php echo $project_name?></title>
    <?php head_css()?>
    <?php before_js()?>
</head>
<body>
    <?php include "./../includes/navbar.php"; ?>

    <div class="container container-set before-footer" style="margin-top:100px">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6">
                <div class="card shadow-sm p-4">
                    <div class="row align-center my-2">
                        <div class="col d-flex justify-content-center">
                            <button class="btn btn-primary" onclick="window.location.href='./dashboard.php'">Back Dashboard</button>
                        </div>
                        <div class="col" >
                            <h4 class="text-center my-1">Edit Employee</h4>
                        </div>
                        <div class="col d-flex justify-content-center">
                            <button class="btn btn-danger" style="width: 80%" onclick="window.location.href='./employees.php'">Back</button>
                        </div>
                    </div>
                    <form id="frmUpdateEmployee" method="POST">
                        <div class="col mb-3">
                            <label for="username" class="form-label col-3">Username</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" id="username" class="form-control" placeholder="Enter username" disabled>
                            </div>
                            
                        </div>
                        <!-- text or username only -->

                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" name="password" id="password" class="form-control" placeholder="Enter password">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Confirm Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Confirm password">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Update Employee</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php include_once "./../includes/footer.php"; ?>
    <?php after_js()?>
    <script src="./../asset/js/message.js"></script>
    <script src="./../asset/js/employee.js"></script>
</body>
</html>