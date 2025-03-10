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
    <title>Delete Counter | <?php echo $project_name?></title>
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
                        <div class="col align-middle" >
                            <h4 class="text-center my-1">Delete Counter</h4>
                        </div>
                        <div class="col d-flex justify-content-center">
                            <button class="btn btn-danger" style="width: 80%" onclick="window.location.href='./counters.php'">Back</button>
                        </div>
                    </div>
                    <form method="POST" id="frmDeleteCounter">
                        <label class="form-label">Do you want to delete this employee <strong><span id="username"></span></strong>?</label>
                        <div class="col col-12 offset-md-3 col-md-6 p-0">
                            <button type="submit" class="btn btn-danger w-100 w-md-50">Delete Counter</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="./../asset/js/jquery-3.7.1.js"></script>
    <script src="./../asset/js/popper.min.js" ></script>
    <script src="./../asset/js/bootstrap.bundle.js"></script>
    <script src="./../asset/js/message.js"></script>
    <script src="./../asset/js/counters.js"></script>
</body>
</html>
