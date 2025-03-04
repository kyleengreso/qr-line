<?php
session_start();
include "./../includes/db_conn.php";
include "./../base.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | <?php echo $project_name?></title>
    <link rel="stylesheet" href="./../asset/css/bootstrap.css">
    <link rel="stylesheet" href="./../asset/css/theme.css">
    <script src="https://kit.fontawesome.com/0aa2c3c0f4.js" crossorigin="anonymous"></script>
</head>
<body>
    <?php include "./../includes/navbar.php"; ?>

    <div class="container d-flex justify-content-center align-items-center" style="margin-top: 15vh">
        <div class="text-center w-100" style="max-width: 400px;">
            <h3 class="fw-bold">COUNTER</h3>

            <p class="mb-3">Current Serving</p>
            <div class="border border-warning rounded p-4 fw-bold fs-1 mb-3">
                <span id="queue-number"></span>
            </div>
            <form method="POST" id="frmNextTransaction">
                <button type="submit" name="next_queue" class="btn btn-warning text-white fw-bold px-4">NEXT</button>
            </form>
        </div>
    </div>

    <script src="./../asset/js/jquery-3.7.1.js"></script>
    <script src="./../asset/js/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="./../asset/js/bootstrap.bundle.js"></script>
    <script src="./../asset/js/dashboard_cashier.js"></script>
</body>
</html>
