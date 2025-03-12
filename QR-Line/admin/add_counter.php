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
    <title>Add Counter | <?php echo $project_name?></title>
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
                    <div class="row align-center my-2">
                        <div class="col d-flex justify-content-center">
                            <button class="btn btn-primary" onclick="window.location.href='./dashboard.php'">Back Dashboard</button>
                        </div>
                        <div class="col" >
                            <h4 class="text-center my-1">Add Counter</h4>
                        </div>
                        <div class="col d-flex justify-content-center">
                            <button class="btn btn-danger" style="width: 80%" onclick="window.location.href='./counters.php'">Back</button>
                        </div>
                    </div>
                    
                    <form method="POST" id="frmAddCounter">
                        <div class="mb-3">
                            <label class="form-label">Search</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input type="text" name="search" id="search" class="form-control" placeholder="Enter username">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Employees Available</label>
                            <div class="input-group">

                            <table class="table table-striped table-members" id="table-counters">
                                <tr>
                                    <th class="col-2"></th>
                                    <th>Username</th>
                                    <th>Available</th>
                                </tr>
                            </table>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Counter No.</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-sort-numeric-up"></i></span>
                                <input type="text" name="counter_no" id="counter_no" class="form-control" placeholder="Enter counter number" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Add Counter</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="./../asset/js/jquery-3.7.1.js"></script>
    <script src="./../asset/js/popper.min.js" ></script>
    <script src="./../asset/js/bootstrap.bundle.js"></script>
    <script src="./../asset/js/message.js"></script>
    <script src="./../asset/js/counter_get.js"></script>
</body>
</html>
