<?php
session_start();
include "./../includes/db_conn.php";
include "./../asset/php/message.php";

$response = array();
header("Content-Type: application/json");
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = json_decode(file_get_contents("php://input"));

    if (!isset($data->employee_id) || !isset($data->counter_no)) {
        echo json_encode(array(
            "status" => "error",
            "message" => "Employee ID and Counter Number are required"));
        exit;
    }

    $employee_id = $data->employee_id;
    $counter_no = $data->counter_no;

    // Checking if both employee and counter number are already registered
    // $stmt = $conn->prepare("SELECT * FROM counter WHERE idemployee = ? AND counterNumber = ?");
    // $stmt->bind_param("ss", $employee_id, $counter_no);
    // $stmt->execute();
    // $result = $stmt->get_result();

    // if ($result->num_rows > 0) {
    //     echo json_encode(array(
    //         "status" => "error",
    //         "message" => "Employee and counter number already registered"
    //     ));
    //     exit;
    // } else {
    //     $stmt = $conn->prepare("INSERT INTO counter (idemployee, counterNumber) VALUES (?, ?)");
    //     $stmt->bind_param("ss", $employee_id, $counter_no);
    //     $stmt->execute();
    //     echo json_encode(array(
    //         "status" => "success",
    //         "message" => "Counter registered successfully"
    //     ));
    //     exit;
    // }

    // Checking if the employee is already registered
    $stmt = $conn->prepare("SELECT * FROM employees WHERE id = ?");
    $stmt->bind_param("s", $employee_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows == 0) {
        echo json_encode(array(
            "status" => "error",
            "message" => "Employee not found"
        ));
        exit;
    }

    // Checking if the counter number is already registered
    $stmt = $conn->prepare("SELECT * FROM counter WHERE counterNumber = ?");
    $stmt->bind_param("s", $counter_no);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode(array(
            "status" => "error",
            "message" => "Counter number already registered"
        ));
        exit;
    } else {
        $stmt = $conn->prepare("INSERT INTO counter (idemployee, counterNumber) VALUES (?, ?)");
        $stmt->bind_param("ss", $employee_id, $counter_no);
        $stmt->execute();
        echo json_encode(array(
            "status" => "success",
            "message" => "Counter registered successfully"
        ));

        // Finding the transaction that was managed by the counter so transfer to the new registered counter
        $stmt = $conn->prepare("SELECT idtransaction FROM transactions WHERE counterNumber = ?");
        $stmt->bind_param("s", $counter_no);
        $stmt->execute();
        $result = $stmt->get_result();
        $transactions = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        echo json_encode(array(
            "status" => "success",
            "data" => $transactions,
            "message" => "Transaction list retrieved successfully"
        ));
        exit;
    }



} if ($_SERVER["REQUEST_METHOD"] == "GET") {

    function do_where() {
        global $sql_cmd, $where_trigger;
        if (!$where_trigger) {
            $sql_cmd .= " WHERE ";
            $where_trigger = TRUE;
        } else {
            $sql_cmd .= " AND ";
        }
    }

    if (isset($_GET['available'])) {
        // Applies to the counter that is available
        $sql_cmd = "SELECT e.username, e.id,  CASE WHEN c.queue_count IS NULL THEN 'Available' ELSE 'Busy' END as availability FROM employees e LEFT JOIN counter c ON e.id = c.idemployee WHERE c.idemployee IS NULL ";

        if (isset($_GET['search'])) {
            $search = $_GET['search'];
            $sql_cmd .= "AND e.username LIKE '%$search%' ";
        }

        $stmt = $conn->prepare($sql_cmd);
        $stmt->execute();
        $result = $stmt->get_result();
        $counters = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        echo json_encode(array(
            "status" => "success",
            "data" => $counters,
            "message" => "Counter list retrieved successfully"
        ));
    } else {
        // So i can tell that counter will we tell to that employee is assigned to specified counter no.
        // In short thats total counter that we have
        $sql_cmd = "SELECT e.username, c.queue_count, c.counterNumber, CASE WHEN c.queue_count = 0 THEN 'available' ELSE 'busy' END as availability FROM employees e JOIN counter c ON e.id = c.idemployee";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->execute();
        $result = $stmt->get_result();
        $counters = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        echo json_encode(array(
            "status" => "success",
            "data" => $counters,
            "message" => "Counter list retrieved successfully"
        ));
    }
}

?>