<?php
include "./../includes/db_conn.php";
include "./../base.php";

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

    // As cashier, to get the transaction queue
    if (isset($data->cashier) && isset($data->employee_id) && isset($data->transaction_id) && isset($data->status)) {
        $transaction_status = $data->status;
        if ($transaction_status == "completed") {
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
        } else if ($transaction_status == "missed") {
            $sql_cmd = "UPDATE transactions SET status = 'missed' WHERE idtransaction = ?";
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
        } else if ($transaction_status == "cancelled") {
            $sql_cmd = "UPDATE transactions SET status = 'cancelled' WHERE idtransaction = ?";
            $stmt = $conn->prepare($sql_cmd);
            $stmt->bind_param("s", $data->transaction_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();
            echo json_encode(array(
                "status" => "success",
                "message" => "Transaction cancelled for transaction_id = " . $data->transaction_id . " by employee_id = " . $data->employee_id
            ));
            exit;
        }
    }
} else if ($_SERVER["REQUEST_METHOD"] == "GET") {

    $data = json_decode(file_get_contents("php://input"));

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

    if (isset($_GET['getEmployee']) && isset($_GET['employee_id'])) {
        $sql_cmd = "SELECT * FROM counter WHERE idemployee = ?";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->bind_param("s", $_GET['employee_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $employee = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        echo json_encode(array(
            "status" => "success",
            "data" => $employee,
            "message" => "Employee found."
        ));
        exit;

    }


    if (isset($_GET['cashier']) && isset($_GET['employee_id'])) {
        // Get the transaction queue for the cashier
        $sql_cmd = "SELECT * 
                    FROM transactions t
                    WHERE t.status = 'pending' AND t.idemployee is null AND t.idcounter is null
                    ORDER BY t.transaction_time ASC
                    LIMIT 0,1";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->execute();
        $result = $stmt->get_result();
        $transaction = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        $no_transaction = FALSE;
        $has_serve = FALSE;
        if ($result->num_rows > 0) {
            // Assign the transaction to the cashier
            // echo json_encode(array(
            //     "status" => "success",
            //     "data" => $transaction,
            //     "message" => "Transaction found."
            // ));
            // exit;

            // Counter data
            $sql_cmd = "SELECT * FROM counter WHERE idemployee = ?";
            $stmt = $conn->prepare($sql_cmd);
            $stmt->bind_param("s", $_GET['employee_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $counter = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            // echo json_encode(array(
            //     "status" => "success",
            //     "data" => $counter,
            //     "message" => "Counter found."
            // ));
            // exit;

            // Get queue_count
            // $sql_cmd = "SELECT setup_value_int FROM setup_system WHERE setup_key = 'queue_count'";
            // $stmt = $conn->prepare($sql_cmd);
            // $stmt->execute();
            // $result = $stmt->get_result();
            // $queue_count = $result->fetch_all(MYSQLI_ASSOC)[0]['setup_value_int'];
            // $stmt->close();

            // echo json_encode(array(
            //     "status" => "success",
            //     "data" => $queue_count,
            //     "message" => "Transaction found."
            // ));
            // exit;

            // $queue_count++;

            // echo json_encode(array(
            //     "status" => "success",
            //     "data" => $queue_count,
            //     "message" => "Transaction found."
            // ));
            // exit;

            // $sql_cmd = "UPDATE setup_system SET setup_value_int = ? WHERE setup_key = 'queue_count'";
            // $stmt = $conn->prepare($sql_cmd);
            // $stmt->bind_param("s", $queue_count);
            // $stmt->execute();
            // $result = $stmt->get_result();
            // $stmt->close();

            
            // Now checking if that transaction was already assigned to the cashier
            $sql_cmd = "SELECT *
                        FROM transactions
                        WHERE idcounter = ? AND idemployee = ? AND status = 'serve'";    
            $stmt = $conn->prepare($sql_cmd);
            $stmt->bind_param("ss", $counter[0]['idcounter'], $_GET['employee_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $transaction1 = $result->fetch_all(MYSQLI_ASSOC);
            
            if ($result->num_rows > 0) {
                echo json_encode(array(
                    "status" => "success",
                    "data" => $transaction1[0],
                    "message" => "Transaction found."
                ));
                exit;
            } else {
                // GET queue_number from transaction

                // ++++

                // Otherwise assign the transaction to the cashier
                $sql_cmd = "UPDATE transactions SET idcounter = ?, idemployee = ?, status = 'serve' WHERE idtransaction = ?";
                $stmt = $conn->prepare($sql_cmd);
                $stmt->bind_param("sss", $counter[0]['idcounter'], $_GET['employee_id'], $transaction[0]['idtransaction']);
                $stmt->execute();
                $stmt->close();

                // Then get the transaction details again
                $sql_cmd = "SELECT * FROM transactions WHERE idcounter = ? AND idemployee = ? AND status = 'serve'";
                $stmt = $conn->prepare($sql_cmd);
                $stmt->bind_param("ss", $counter[0]['idcounter'], $_GET['employee_id']);
                $stmt->execute();
                $result = $stmt->get_result();
                $transaction = $result->fetch_all(MYSQLI_ASSOC);
                $stmt->close();
    
                $queue_number = $transaction[0]['queue_number'];

                // echo json_encode(array(
                //     "status" => "success",
                //     "data" => $queue_number,
                //     "message" => "Transaction found."
                // ));
                // exit;

                // Then update it to the counter's queue_count
                $sql_cmd = "UPDATE counter SET queue_count = ? WHERE idcounter = ?";
                $stmt = $conn->prepare($sql_cmd);
                $stmt->bind_param("ss", $queue_number, $counter[0]['idcounter']);
                $stmt->execute();
                $stmt->close();

                // Then get the transaction details again
                $sql_cmd = "SELECT * FROM transactions WHERE idcounter = ? AND idemployee = ? AND status = 'serve'";
                $stmt = $conn->prepare($sql_cmd);
                $stmt->bind_param("ss", $counter[0]['idcounter'], $_GET['employee_id']);
                $stmt->execute();
                $result = $stmt->get_result();
                $transaction = $result->fetch_all(MYSQLI_ASSOC);
                $stmt->close();

                echo json_encode(array(
                    "status" => "success",
                    "data" => $transaction[0],
                    "message" => "Transaction found."
                ));
                exit;
            }
        } else {
            // Counter data
            $sql_cmd = "SELECT * FROM counter WHERE idemployee = ?";
            $stmt = $conn->prepare($sql_cmd);
            $stmt->bind_param("s", $_GET['employee_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $counter = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            // $dat = json_decode($counter, true);
            // get $counter 'status'
            // $c_status
            $idcounter = $counter[0]['idcounter'];
            // echo json_encode(array(
            //     "status" => "success",
            //     "data" => $counter[0]
            // ));

            // exit;
            // Now checking if that transaction was already assigned to the cashier
            $sql_cmd = "SELECT *
                        FROM transactions
                        WHERE idcounter = ? AND idemployee = ? AND status = 'serve'";    
            $stmt = $conn->prepare($sql_cmd);
            $stmt->bind_param("ss", $idcounter, $_GET['employee_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $transaction1 = $result->fetch_all(MYSQLI_ASSOC);
            
            if ($result->num_rows > 0) {
                echo json_encode(array(
                    "status" => "success",
                    "data" => $transaction1[0],
                    "message" => "Transaction found."
                ));
                exit;
            } else {
                // No transaction available
                echo json_encode(array(
                    "status" => "empty",
                    "message" => "No transaction available."
                ));
            }
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