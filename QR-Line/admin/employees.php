<?php
session_start();

include "./../includes/db_conn.php";
include "./../base.php";

login_as_employee();
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
                    <div class="row align-center my-4">
                        <div class="col d-flex justify-content-center">
                            <button class="btn btn-primary" onclick="window.location.href='./dashboard.php'">Back Dashboard</button>
                        </div>
                        <div class="col" >
                            <h4 class="text-center my-1">Employees</h4>
                        </div>
                        <div class="col d-flex justify-content-center">
                            <button class="btn btn-success" style="width: 80%" onclick="window.location.href='./add_employee.php'">Add Employee</button>
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
                    <nav aria-label="Page navigation example">
                        <ul class="pagination justify-content-center">
                            <li class="page-item">
                            <a class="page-link" onclick="prevPaginateEmployees()" id="pagePrevEmployees">Previous</a>
                            </li>
                            <li class="page-item">
                            <a class="page-link" onclick="nextPaginateEmployees();" id="pageNextEmployees" href="#">Next</a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <script src="./../asset/js/jquery-3.7.1.js"></script>
    <script src="./../asset/js/popper.min.js" ></script>
    <script src="./../asset/js/bootstrap.bundle.js"></script>
    <script src="./../asset/js/jquery-3.7.1.js"></script>
    <script src="./../asset/js/employee.js"></script>
</body>
</html>
