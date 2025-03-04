<?php
session_start();
include "./../includes/db_conn.php";
include "./../base.php";
// Check if employee is logged in
// if (!isset($_SESSION["employee_id"])) {
//     header("Location: ./../authlogin.php");
//     exit();
// }

// Get employee details
// $employee_id = $_SESSION["employee_id"];
// $stmt = $conn->prepare("SELECT * FROM employees WHERE id = ?");
// $stmt->bind_param("i", $employee_id);
// $stmt->execute();
// $employee = $stmt->get_result()->fetch_assoc();

// // Get assigned counter
// $stmt = $conn->prepare("SELECT * FROM counter WHERE idemployee = ?");
// $stmt->bind_param("i", $employee_id);
// $stmt->execute();
// $counter = $stmt->get_result()->fetch_assoc();

// // Get the next queue number
// if ($counter) {
//     $counter_id = $counter['idcounter'];
//     $stmt = $conn->prepare("SELECT * FROM transactions WHERE idcounter = ? AND status = 'pending' ORDER BY queue_number ASC LIMIT 1");
//     $stmt->bind_param("i", $counter_id);
//     $stmt->execute();
//     $next_queue = $stmt->get_result()->fetch_assoc();
// } else {
//     $next_queue = null;
// }

// // If 'NEXT' button is clicked, update the queue
// if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["next_queue"])) {
//     if ($next_queue) {
//         $transaction_id = $next_queue["idtransaction"];
//         $stmt = $conn->prepare("UPDATE transactions SET status = 'completed' WHERE idtransaction = ?");
//         $stmt->bind_param("i", $transaction_id);
//         $stmt->execute();

//         // Refresh the page to update the queue number
//         header("Location: counter.php");
//         exit();
//     }
// }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Dashboard | <?php echo $project_name?></title>
    <link rel="stylesheet" href="./../asset/css/bootstrap.css">
    <link rel="stylesheet" href="./../asset/css/theme.css">
    <script src="https://kit.fontawesome.com/0aa2c3c0f4.js" crossorigin="anonymous"></script>
</head>
<body>
    <?php include "./../includes/navbar.php"; ?>

    <div class="container d-flex justify-content-center align-items-center" style="margin-top: 15svh">
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
