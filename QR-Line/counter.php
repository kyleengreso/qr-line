<?php
session_start();
include "includes/db_conn.php";

// Check if employee is logged in
if (!isset($_SESSION["employee_id"])) {
    header("Location: login.php");
    exit();
}

// Get employee details
$employee_id = $_SESSION["employee_id"];
$stmt = $conn->prepare("SELECT * FROM employees WHERE id = ?");
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$employee = $stmt->get_result()->fetch_assoc();

// Get assigned counter
$stmt = $conn->prepare("SELECT * FROM counter WHERE idemployee = ?");
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$counter = $stmt->get_result()->fetch_assoc();

// Get the next queue number
$counter_id = $counter['idcounter'];
$stmt = $conn->prepare("SELECT * FROM transactions WHERE idcounter = ? AND status = 'pending' ORDER BY queue_number ASC LIMIT 1");
$stmt->bind_param("i", $counter_id);
$stmt->execute();
$next_queue = $stmt->get_result()->fetch_assoc();

// If 'NEXT' button is clicked, update the queue
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["next_queue"])) {
    if ($next_queue) {
        $transaction_id = $next_queue["idtransaction"];
        $stmt = $conn->prepare("UPDATE transactions SET status = 'completed' WHERE idtransaction = ?");
        $stmt->bind_param("i", $transaction_id);
        $stmt->execute();

        // Refresh the page to update the queue number
        header("Location: counter.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Dashboard | QR-Line</title>
    <link rel="stylesheet" href="asset/css/bootstrap.css">
    <link rel="stylesheet" href="asset/css/style.css">
    <script src="https://kit.fontawesome.com/0aa2c3c0f4.js" crossorigin="anonymous"></script>
</head>
<body>
    <?php include "includes/navbar.php"; ?>

    <div class="container d-flex justify-content-center align-items-center" style="margin-top: 10vh">
        <div class="text-center w-100" style="max-width: 400px;">
            <h3 class="fw-bold">COUNTER <?php echo $counter['counterNumber']; ?></h3>
            <p class="mb-3">Current Serving</p>
            <div class="border border-warning rounded p-4 fw-bold fs-1 mb-3">
                <?php echo $next_queue ? $next_queue['queue_number'] : "No Queue"; ?>
            </div>
            <form method="POST">
                <button type="submit" name="next_queue" class="btn btn-warning text-white fw-bold px-4">NEXT</button>
            </form>
        </div>
    </div>

    <script src="asset/js/bootstrap.bundle.js"></script>
</body>
</html>
