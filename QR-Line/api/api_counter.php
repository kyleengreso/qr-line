<?php
session_start();
include "./../includes/db_conn.php";
include "./../asset/php/message.php";

$response = array();
if ($_SERVER["REQUEST_METHOD"] == "GET") {


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

    } else {
        // GET ALL REGISTERED COUNTERS
        $stmt = $conn->prepare("SELECT * FROM counter");
        $stmt->execute();
        $result = $stmt->get_result();
        $counters = $result->fetch_all(MYSQLI_ASSOC);
    }
}

echo json_encode($response);

?>