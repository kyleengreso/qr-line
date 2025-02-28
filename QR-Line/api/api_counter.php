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
        exit;
    }



} if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (isset($_GET['available'])) {
        $search = isset($_GET['search']) ? '%' . $_GET['search'] . '%' : '%';
    
        $stmt_employee = $conn->prepare("SELECT id, username, created_at FROM employees WHERE username LIKE ?");
        $stmt_employee->bind_param("s", $search);
        $stmt_employee->execute();
        $result_employee = $stmt_employee->get_result();
        $employees = $result_employee->fetch_all(MYSQLI_ASSOC);
    
        $stmt_counter = $conn->prepare("SELECT * FROM counter;");
        $stmt_counter->execute();
        $result_counter = $stmt_counter->get_result();
        $counters = $result_counter->fetch_all(MYSQLI_ASSOC);
    
        // The sample is
        // $response['data'] = array([
        //     'employee_id' => employee_id,
        //     'employee_username' => employee_username,
        //     'available' => true,
        //     'queue_count' => 0 or queue_count from existed date])
    
        // employee than counter
        $response['status'] = 'success';
        $response['message'] = 'Employee list retrieved successfully';
        $response['data'] = [];
    
        foreach ($employees as $employee) {
            $employee_id = $employee['id'];
            $employee_username = $employee['username'];
            $available = true;
            $queue_count = 0;
    
            foreach ($counters as $counter) {
                if ($counter['idemployee'] == $employee_id) {
                    $available = false;
                    $queue_count = $counter['queue_count'];
                    break;
                }
            }
    
            $response['data'][] = array(
                'employee_id' => $employee_id,
                'employee_username' => $employee_username,
                'available' => $available,
                'queue_count' => $queue_count
            );
        }
    } else if (isset($_GET['id'])) {
        // CALL THE COUNTER's ID so it will confirmation if exists
        $id = $_GET['id'];
        $stmt = $conn->prepare("SELECT * FROM counter WHERE idcounter = ?");
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $counters = $result->fetch_all(MYSQLI_ASSOC);

    // CALL THE COUNTER's EMPLOYEE ID so it will confirmation if exists
    } else if (isset($_GET['employee_id'])) {
        $employee_id = $_GET['employee_id'];
        $stmt = $conn->prepare("SELECT * FROM counter WHERE idemployee = ?");
        $stmt->bind_param("s", $employee_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $counters = $result->fetch_all(MYSQLI_ASSOC);
    } else if (isset($_GET['page']) && isset($_GET['paginate'])) {
        $page = $_GET['page'];
        $paginate = $_GET['paginate'];
        $offset = ($page - 1) * $paginate;
        $stmt = $conn->prepare("SELECT * FROM counter LIMIT ?, ?");
        $stmt->bind_param("ii", $offset, $paginate);
        $stmt->execute();
        $result = $stmt->get_result();
        $counters = $result->fetch_all(MYSQLI_ASSOC);
    } else  {
        // GET ALL REGISTERED COUNTERS
        $stmt = $conn->prepare("SELECT * FROM counter");
        $stmt->execute();
        $result = $stmt->get_result();
        $counters = $result->fetch_all(MYSQLI_ASSOC);
    }
    if ($counters) {
        $response['status'] = 'success';
        $response['data'] = $counters;
        $response['message'] = 'Counters found';
    } else {
        $response['status'] = 'error';
        $response['message'] = 'No counters found';
    }
}

echo json_encode($response);

?>