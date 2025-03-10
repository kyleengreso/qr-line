<?php
include "./../includes/db_conn.php";

function employee_check($employee_id) {
    global $conn;
    $sql_cmd = "SELECT id FROM employees WHERE id = ?";
    $stmt = $conn->prepare($sql_cmd);
    $stmt->bind_param("s", $employee_id);
    $stmt->execute();
    $employee = $stmt->get_result();
    if ($employee->num_rows == 0) {
        return FALSE;
    } else {
        return TRUE;
    }
}
function get_available_counter_for_employee_id($employee_id, $queue_count = 2) {
    global $conn;

    // Get the counter with the highest queue count
    $sql_cmd = "SELECT t.idcounter, COUNT(t.idtransaction) as pending_count
                FROM transactions t
                LEFT JOIN employees e ON t.idemployee = e.id
                WHERE t.idcounter != ? AND t.status = 'pending' AND e.role_type = 'employee'
                GROUP BY t.idcounter;";
    $stmt = $conn->prepare($sql_cmd);
    $stmt->bind_param("s", $employee_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $counters = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Get idcounter using idemployee at counter table
    $sql_cmd = "SELECT idcounter, counterNumber, idemployee FROM counter WHERE idemployee = ?";
    $stmt = $conn->prepare($sql_cmd);
    $stmt->bind_param("s", $employee_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $counters_main = $result->fetch_assoc();
    $stmt->close();

    // echo json_encode(array(
    //     "status" => "success",
    //     "data" => $counters_main,
    //     "message" => "Counters found."
    // ));
    // exit;
    $counter_highest_queue = 0;
    $counter_id = 0;

    foreach ($counters as $counter) {
        if ($counter['pending_count'] > $counter_highest_queue) {
            $counter_highest_queue = $counter['pending_count'];
            $counter_id = $counter['idcounter'];
        }
    }

    // echo json_encode(array(
    //     "status" => "success",
    //     "highest_queue" => $counter_highest_queue,
    //     "counter_id" => $counter_id,
    //     "message" => "Counter found."
    // ));
    // exit;


    // Get the 2nd queue at the counter with the highest queue count
    $sql_cmd = "SELECT * FROM transactions WHERE idcounter = ? AND status = 'pending' ORDER BY idtransaction ASC LIMIT 1,1";
    $stmt = $conn->prepare($sql_cmd);
    $stmt->bind_param("s", $counter_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $transaction = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    if ($transaction) {
        $sql_cmd = "UPDATE transactions SET idcounter = ?, idemployee = ? WHERE idtransaction = ?";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->bind_param("sss", $counters_main['idcounter'], $employee_id, $transaction[0]['idtransaction']);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            echo json_encode(array(
                "status" => "success",
                "data" => $transaction,
                "message" => "Transaction updated successfully."
            ));
        } else {
            echo json_encode(array(
                "status" => "error",
                "message" => "No rows updated. Check if the transaction exists."
            ));
        }

        $stmt->close();
    }
    // exit();
}


header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = json_decode(file_get_contents("php://input"));
    if (isset($data->cashier) && !isset($data->employee_id)) {
        echo json_encode(array(
            "status" => "error",
            "message" => "Employee ID please."
        ));
        exit;
    }

    if (isset($data->cashier) && isset($data->employee_id) && isset($data->transaction_id)) {
        $sql_cmd = "UPDATE transactions SET status = 'completed' WHERE idtransaction = ?";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->bind_param("s", $data->transaction_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        echo json_encode(array(
            "status" => "success",
            "message" => "Transaction completed for transaction_id = " . $data->transaction_id . " by employee_id = " . $data->employee_id
        ));
        exit;
    }




    if (!isset($data->name) || !isset($data->email) || !isset($data->payment) && isset($data->cashier)) {
        echo json_encode(array(
            "status" => "error",
            "message" => "Please fill up the information."
        ));
        exit;
    }

    if (!isset($data->cashier)) {

        try {
            // Reserved for cashier
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(array(
                "status" => "error",
                "message" => "Error processing request: " . $e->getMessage()
            ));
            exit;
        }
        exit;
    }


    $conn->begin_transaction();

    try {
        $name = $data->name;
        $email = $data->email;
        $payment = $data->payment;

        // Insert user data into the database
        $stmt = $conn->prepare("INSERT INTO requesters (name, email, payment) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $payment);
        $stmt->execute();
        $user_id = $stmt->insert_id;
        $stmt->close();

        // To tell to find the available counters
        $stmt = $conn->prepare("SELECT idcounter FROM counter ORDER BY queue_count ASC LIMIT 1");
        $stmt->execute();
        $result = $stmt->get_result();
        $counter = $result->fetch_assoc();
        $stmt->close();

        if (!$counter) {
            echo json_encode(array(
                "status" => "error",
                "message" => "No available counters."
            ));
            exit;
        }

        $counter_id = $counter['idcounter'];

        // Get the next queue number
        $stmt = $conn->prepare("SELECT IFNULL(MAX(queue_number) + 1, 1) AS next_queue FROM transactions");
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $queue_number = $row['next_queue'];
        $stmt->close();

        // Generate a unique token number
        $token_number = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));

        // Insert queue details into transactions
        $stmt = $conn->prepare("INSERT INTO transactions (iduser, idcounter, queue_number, token_number, status, payment) VALUES (?, ?, ?, ?, 'pending', ?)");
        $stmt->bind_param("sssss", $user_id, $counter_id, $queue_number, $token_number, $payment);
        $stmt->execute();
        $stmt->close();

        // Update counter queue count
        $stmt = $conn->prepare("UPDATE counter SET queue_count = queue_count + 1 WHERE idcounter = ?");
        $stmt->bind_param("i", $counter_id);
        $stmt->execute();
        $stmt->close();

        // Commit transaction
        $conn->commit();

        // Redirect user to queue display page
        echo json_encode(array(
            "status" => "success",  
            "gotoLocation" => "./user_number.php?queue=$queue_number&counter=$counter_id",
            "message" => "Done..."
        ));
        exit();

    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        echo json_encode(array(
            "status" => "error",
            "message" => "Error processing request: " .$e->getMessage()
        ));
        exit;
    }


} else if ($_SERVER["REQUEST_METHOD"] == "GET") {
    // Base SQL query to select transaction details
    $sql_cmd = "SELECT t.idrequester as idrequester, e.username as employee_name, t.idcounter as idcounter, t.transaction_time as transaction_time, r.email as email, t.status as status, r.payment FROM transactions t JOIN requesters r ON t.idrequester = r.id JOIN employees e ON t.idemployee = e.id";
    $where_trigger = FALSE;

    function do_where() {
        global $sql_cmd, $where_trigger;
        if (!$where_trigger) {
            $sql_cmd .= " WHERE ";
            $where_trigger = TRUE;
        } else {
            $sql_cmd .= " AND ";
        }
    }

    // Total count of transactions
    if (isset($_GET['total_count'])) {
        $sql_cmd = "SELECT COUNT(idtransaction) as total FROM transactions";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->execute();
        $result = $stmt->get_result();
        $total = $result->fetch_all(MYSQLI_ASSOC)[0];
        $stmt->close();

        echo json_encode(array(
            "status" => "success",
            "data" => $total,
            "message" => "Total transactions retrieved successfully."
        ));
        exit;
    }
    // Login as cashier, as cashier will tell to collect his/her transaction queue
    if (isset($_GET['cashier']) && !isset($_GET['employee_id'])) {
        echo json_encode(array(
            "status" => "error",
            "message" => "Employee ID please."
        ));
        exit;
    }
    if (isset($_GET['cashier']) && $_GET['employee_id']) {
        $sql_cmd = "SELECT t.idtransaction, c.counterNumber, t.queue_number, t.status, e.username from counter c LEFT JOIN transactions t ON c.idemployee = t.idemployee LEFT JOIN employees e on c.idemployee = e.id WHERE e.id = ? AND t.status = 'pending' LIMIT 0, 1";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->bind_param("s", $_GET['employee_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $transaction = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        if ($transaction) {
            echo json_encode(array(
                "status" => "success",
                "data" => $transaction,
                "message" => "Transaction found."
            ));
        } else {
            get_available_counter_for_employee_id($_GET['employee_id']);
        }
        exit;
    }

    // Add search condition if 'search' parameter is set
    if (isset($_GET['search'])) {
        do_where();
        $sql_cmd .= "r.name LIKE '%" . $_GET['search'] . "%' OR r.email LIKE '%" . $_GET['search'] . "%' ";
    } 
    // Add condition to filter by transaction ID if 'id' parameter is set
    else if (isset($_GET['id'])) {
        do_where();
        $sql_cmd .= "t.idtransaction = " . $_GET['id'] . " ";
    }

    // Add condition to filter by corporate email if 'corporate' parameter is set
    if (isset($_GET['corporate'])) {
        if ($_GET['corporate'] == "true") {
            do_where();
            $sql_cmd .= " r.email LIKE '%psu.palawan.edu.ph%' ";
        } else if ($_GET['corporate'] == "false") {
            do_where();
            $sql_cmd .= " r.email NOT LIKE '%psu.palawan.edu.ph%' ";
        }
    }

    if (isset($_GET['payment'])) {
        if ($_GET['payment'] == "registrar") {
            do_where();
            $sql_cmd .= " r.payment = 'registrar' ";
        } else if ($_GET['payment'] == "assessment") {
            do_where();
            $sql_cmd .= " r.payment = 'assessment' ";
        }
    }
    // Add ORDER BY clause to sort results by transaction time in descending order
    $sql_cmd .= " ORDER BY t.transaction_time DESC ";

    // Add LIMIT clause for pagination if 'page' and 'paginate' parameters are set
    if (isset($_GET['page']) && isset($_GET['paginate'])) {
        $offset = ($_GET['page'] - 1) * $_GET['paginate'];
        $sql_cmd .= "LIMIT " . $offset . ", " . $_GET['paginate'];
    }
    

    // echo json_encode(array(
    //     "status" => "success",
    //     "message" => "Transactions found.",
    //     "sql" => $sql_cmd
    // ));
    // exit;
    // Prepare and execute the SQL query
    $stmt = $conn->prepare($sql_cmd);
    if (!$stmt) {
        echo json_encode(array(
            "status" => "error",
            "message" => "SQL error: " . $conn->error
        ));
        exit;
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $transactions = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Return the results as a JSON response
    if ($transactions) {
        echo json_encode(array(
            "status" => "success",
            "data" => $transactions,
            "message" => "Transactions found."
        ));
    } else {
        echo json_encode(array(
            "status" => "empty",
            "message" => "No transaction found."
        ));
    }
}
?>