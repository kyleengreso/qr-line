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
    <title>Counters List | <?php echo $project_name?></title>
    <link rel="stylesheet" href="./../asset/css/bootstrap.css">
    <link rel="stylesheet" href="./../asset/css/theme.css">
    <script src="https://kit.fontawesome.com/0aa2c3c0f4.js" crossorigin="anonymous"></script>
</head>
<body>
    <?php include "./../includes/navbar.php"; ?>

    <div class="container">
        <div class="container d-flex justify-content-center align-items-center" style="margin-top: 15vh;">

            <div class="col-md-6">
                <div class="card shadow-sm p-4">
                    <div class="row align-center my-4">
                        <div class="col d-flex justify-content-center">
                            <button class="btn btn-primary" onclick="window.location.href='./dashboard.php'">Back Dashboard</button>
                        </div>
                        <div class="col" >
                            <h4 class="text-center my-1">Counters</h4>
                        </div>
                        <div class="col d-flex justify-content-center">
                            <button class="btn btn-success" style="width: 80%" onclick="window.location.href='./add_counter.php'">Add Counter</button>
                        </div>
                    </div>
                    <div class="row align-center my-4">
                            <div class="mb-3">
                                <label class="form-label">Search</label>
                                <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input type="text" name="search" id="search-counter" class="form-control" placeholder="Enter username">
                            </div>
                    </div>
                    <table class="table table-striped table-members" id="table-counters">
                        <tr>
                            <th>#</th>
                            <th>Employee</th>
                            <th>Queue Count</th>
                            <th>Action</th>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="./../asset/js/jquery-3.7.1.js"></script>
    <script src="./../asset/js/counters.js"></script>
</body>
</html>
