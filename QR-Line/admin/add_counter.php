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
                    <h4 class="text-center mb-4">Add Counter</h4>
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

                            <table class="table table-striped" id="table-members">
                                <tr>
                                    <th></th>
                                    <th>#</th>
                                    <th>Username</th>
                                    <th>Status</th>
                                    <th>Queue</th>
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
                        <div class="text-center p-2">
                            <a class="text-a-black" href="./counters.php">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="./../asset/js/jquery-3.7.1.js"></script>
    <script src="./../asset/js/message.js"></script>
    <script src="./../asset/js/counter_get.js"></script>
</body>
</html>
