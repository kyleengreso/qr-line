<?php
session_start();
include "./../includes/db_conn.php";
include "./../base.php";
include "./../asset/php/message.php";
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
                    <form method="POST" id="frmDeleteEmployee">
                        <label class="form-label">Do you want to delete this employee <strong><span id="username"></span></strong>?</label>
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
    <script src="./../asset/js/jquery-3.7.1.js"></script>
    <script src="./../asset/js/message.js"></script>
    <script src="./../asset/js/employee.js"></script>   
</body>
</html>
