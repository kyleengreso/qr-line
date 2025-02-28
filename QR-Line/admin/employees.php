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
    <title>Employees List | <?php echo $project_name?></title>
    <link rel="stylesheet" href="./../asset/css/bootstrap.css">
    <link rel="stylesheet" href="./../asset/css/theme.css">
    <script src="https://kit.fontawesome.com/0aa2c3c0f4.js" crossorigin="anonymous"></script>
</head>
<body>
    <?php include "./../includes/navbar.php"; ?>

    <div class="container" style="margin-top: 15vh">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6">
                <div class="card shadow-sm p-4">
                    <div class="row w-100 mb-4">
                        
                        <div class="col col-9">
                            <h4 class="text-center">Employees List</h4>
                        </div>
                    
                        <div class="col col-3 p-0">
                        <button class="btn btn-success" onclick="window.location.href='./add_employee.php'">Add Employees</button>
                        </div>
                    
                    </div>


                    <table class="table table-striped table-members" id="table-employees">
                        <tr>
                            <th>#</th>
                            <th>Username</th>
                            <th>Created At</th>
                            <th>Action</th>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="./../asset/js/bootstrap.bundle.js"></script>
    <script src="./../asset/js/jquery-3.7.1.js"></script>
    <script src="./../asset/js/employee.js"></script>
</body>
</html>
