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
    <?php head_icon()?>
    <title>Dashboard | <?php echo $project_name?></title>
    <?php head_css()?>
    <?php before_js()?>
</head>
<body>
    <?php include "./../includes/navbar.php"; ?>

    <div class="container d-flex justify-content-center align-items-center before-footer container-set" style="margin-top: 50px">
        <div class="text-center w-100" style="max-width: 400px;" id="employeeDashboard">
            <h3 class="fw-bold">COUNTER <span id="employee-counter-number"></span></h3>

            <p class="mb-3">Current Serving</p>
            <div class="border border-warning rounded p-4 fw-bold fs-1 mb-3">
                <span id="queue-number"></span>
            </div>
            <form method="POST" id="frmNextTransaction">
                <button type="submit" name="next_queue" id="btn-counter-success"class="btn btn-warning text-white fw-bold px-4">NEXT</button>
                <button type="submit" name="next_queue" id="btn-counter-skip"class="btn btn-warning text-white fw-bold px-4">SKIP</button>
            </form>
        </div>
    </div>

    <?php after_js()?>
    <script src="./../asset/js/message.js"></script>
    <script src="./../asset/js/dashboard_cashier.js"></script>
</body>
<?php include_once "./../includes/footer.php"; ?>
</html>
