<?php
session_start();
include "./../includes/db_conn.php";
include "./../asset/php/message.php";

$response = array();
header("Content-Type: application/json");
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = json_decode(file_get_contents("php://input"));

    if (empty($data->method) || !isset($data->method)) {
        echo json_encode(array(
            "status" => "error",
            "message" => "Method is required"));
        exit;
    }

    $method = $data->method;

    if ($method == "create") {
        if (!isset($data->employee_id) || !isset($data->counter_no) || empty($data->counter_pwd)) {
            echo json_encode(array(
                "status" => "error",
                "message" => "Employee ID, Counter Number, Counter PWD are required"));
            exit;
        }
    
        $employee_id = $data->employee_id;
        $counter_no = $data->counter_no;
        $counter_pwd = $data->counter_pwd;
    
        if ($counter_pwd == "true") { $counter_pwd = "Y";
        } else { $counter_pwd = "N";}

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
            $stmt = $conn->prepare("INSERT INTO counter (idemployee, counterNumber, counter_pwd) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $employee_id, $counter_no, $counter_pwd);
            $stmt->execute();
            echo json_encode(array(
                "status" => "success",
                "message" => "Counter registered successfully"
            ));
            exit;
        }
    } else if ($method == "delete") {
        $counter_no = $data->counter_no;
        // Checking if the counter is exist
        $stmt = $conn->prepare("SELECT * FROM counter WHERE idcounter = ?");
        $stmt->bind_param("s", $counter_no);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows == 0) {
            echo json_encode(array(
                "status" => "error",
                "message" => "Counter not found"
            ));
            exit;
        } else {
            $stmt = $conn->prepare("DELETE FROM counter WHERE idcounter = ?");
            $stmt->bind_param("s", $counter_no);
            $stmt->execute();
            echo json_encode(array(
                "status" => "success",
                "message" => "Counter deleted successfully"
            ));
            exit;
        }
    } else if ($method == "update") {
        // BUG FIX: Counter number is not being updated
        if (!isset($data->employee_id) || !isset($data->counter_no) || empty($data->counter_pwd)) {
            echo json_encode(array(
                "status" => "error",
                "message" => "Employee ID, Counter Number, Counter PWD are required"));
            exit;
        }
    
        $employee_id = $data->employee_id;
        $counter_no = $data->counter_no;
        $counter_pwd = $data->counter_pwd;
    
        if ($counter_pwd == "true") { $counter_pwd = "Y";
        } else { $counter_pwd = "N";}

        // Checking if the employee is already registered
        $stmt = $conn->prepare("SELECT * FROM employees WHERE id = ?");
        $stmt->bind_param("s", $employee_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $employee = $result->fetch_all(MYSQLI_ASSOC)[0];
        if ($result->num_rows == 0) {
            echo json_encode(array(
                "status" => "error",
                "message" => "Employee not found"
            ));
            exit;
        } else {
            // echo json_encode(array(
            //     "status" => "error",
            //     "employee_id" => $employee['id'],
            //     "counter_no" => $counter_no,
            //     "counter_pwd" => $counter_pwd
            // ));
            // exit;
            $sql_cmd = "UPDATE counter SET counter_pwd = ?, counterNumber = ? WHERE idemployee = ?";
            $stmt = $conn->prepare($sql_cmd);
            $stmt->bind_param("sss", $counter_pwd, $counter_no, $employee['id']);
            $stmt->execute();
            if ($stmt->affected_rows == 0) {
                echo json_encode(array(
                    "status" => "error",
                    "message" => "Issue"
                ));
                exit;
            } else {
                echo json_encode(array(
                    "status" => "success",
                    "message" => "Counter updated successfully"
                ));
                exit;
            }
        }
    }

} if ($_SERVER["REQUEST_METHOD"] == "GET") {

    if (isset($_GET['page']) && isset($_GET['paginate'])) {
        $page = $_GET['page'];
        $pagination = $_GET['paginate'];
        $offset = ($page - 1) * $pagination;
        $sql_cmd = "SELECT c.idcounter, c.counterNumber, c.idemployee, c.queue_count, c.counter_pwd, e.username FROM counter c LEFT JOIN employees e ON c.idemployee = e.id LIMIT ? OFFSET ?";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->bind_param("ss", $pagination, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        $counters = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        echo json_encode(array(
            "status" => "success",
            "data" => $counters,
            "message" => "Counter list retrieved successfully"
        ));
        exit;
    }

    // Get the data about the counter
    if (isset($_GET['id'])) {
        $id = $_GET['id'];
        $sql_cmd = "SELECT c.idcounter, c.counterNumber, c.idemployee, c.queue_count, c.counter_pwd, e.username FROM counter c LEFT JOIN employees e ON c.idemployee = e.id WHERE c.idcounter = ?";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $counter = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        echo json_encode(array(
            "status" => "success",
            "data" => $counter[0],
            "message" => "Counter retrieved successfully"
        ));
        exit;
    }

    // Get total count of the counter
    if (isset($_GET['total_count'])) {
        $stmt = $conn->prepare("SELECT COUNT(idcounter) as total FROM counter");
        $stmt->execute();
        $result = $stmt->get_result();
        $total = $result->fetch_all(MYSQLI_ASSOC)[0];
        $stmt->close();

        echo json_encode(array(
            "status" => "success",
            "data" => $total,
            "message" => "Total counter retrieved successfully"
        ));
        exit;
    }

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
        $search = NULL;
        if (isset($_GET['search'])) {
            $search = $_GET['search'];
            $sql_cmd = "SELECT c.idcounter, e.username, c.queue_count, c.counterNumber, CASE WHEN c.queue_count = 0 THEN 'available' ELSE 'busy' END as availability FROM employees e JOIN counter c ON e.id = c.idemployee WHERE e.username LIKE '%$search%'";
        } else {
            $sql_cmd = "SELECT c.idcounter, e.username, c.queue_count, c.counterNumber, CASE WHEN c.queue_count = 0 THEN 'available' ELSE 'busy' END as availability FROM employees e JOIN counter c ON e.id = c.idemployee";
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
    }
}
?>