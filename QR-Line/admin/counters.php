<?php
session_start();

include "./../includes/db_conn.php";
include "./../base.php";
include "./../asset/php/message.php";
if ($_SERVER["REQUEST_METHOD"] == "GET") {

    $message_success = $_SESSION['message-success'] ?? null;
    $message_error = $_SESSION['message-error'] ?? null;
}
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
                    <div class="row w-100 mb-4">
                        
                        <div class="col col-9">
                            <h4 class="text-center">Counters List</h4>
                        </div>
                    
                        <div class="col col-3 p-0">
                        <button class="btn btn-success" onclick="window.location.href='./add_counter.php'">Add Counter</button>
                        </div>
                    
                    </div>

                    <table class="table table-striped table-members" id="table-counters">
                        <tr>
                            <th>#</th>
                            <th>Employee</th>
                            <th>Counter No,</th>
                            <th>Queue Count</th>
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
