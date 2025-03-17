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

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
require "./../../vendor/autoload.php";

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

}   if (isset($_GET['cashier']) && isset($_GET['employee_id'])) {

        // Employee's counter data
        try {
            $sql_cmd = "SELECT * FROM counter WHERE idemployee = ?";
            $stmt = $conn->prepare($sql_cmd);
            $stmt->bind_param("s", $_GET['employee_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $counter = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            $counder_id = NULL;
            $counter_counterNumber = NULL;
            $counter_queue_count = NULL;

            if ($result->num_rows == 0) {
                echo json_encode(array(
                    "status" => "error",
                    "message" => "Employee is not assigned for counter."
                ));
                exit;
            } else {
                $counter = $counter[0];
                $counter_id = $counter['idcounter'];
                $counter_counterNumber = $counter['counterNumber'];
                $counter_queue_count = $counter['queue_count'];
                // echo json_encode(array(
                //     "status" => "success",
                //     "data" => $counter,
                //     "message" => "Counter found."
                // ));
                // exit;
            }

            // echo json_encode(array(
            //     "status" => "success",
            //     "counter_id" => $counter_id,
            //     "counter_counterNumber" => $counter_counterNumber,
            //     "counter_queue_count" => $counter_queue_count,

            //     "message" => "Counter foundXX."
            // ));
            // exit;
            // Checking if the transaction was already assigned to the cashier
            $sql_cmd = "SELECT *
                        FROM transactions
                        WHERE idcounter = ? AND idemployee = ? AND status = 'serve'";
            $stmt = $conn->prepare($sql_cmd);
            $stmt->bind_param("ss", $counter_id, $_GET['employee_id']);
            $stmt->execute();
            $result= $stmt->get_result();
            $transaction = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            if ($result->num_rows == 0) {
                // $transaction = NULL;
                // echo json_encode(array(
                //     "status" => "error",
                //     "message" => "No really, assign time."
                // ));
                // exit;
            } else {
                $transaction = $transaction[0];
                echo json_encode(array(
                    "status" => "success",
                    "data" => $transaction,
                    "message" => "Transaction found already."
                ));
                exit;
            }

            // Find the new one transaction
            if (!$transaction) {
                $sql_cmd = "SELECT *
                            FROM transactions t
                            WHERE t.status = 'pending' AND t.idcounter IS NULL AND t.idemployee IS NULL
                            ORDER BY t.transaction_time ASC
                            LIMIT 1";
                $stmt = $conn->prepare($sql_cmd);
                $stmt->execute();
                $result = $stmt->get_result();
                $transaction = $result->fetch_all(MYSQLI_ASSOC);
                $stmt->close();

                if ($result->num_rows == 0) {
                    $transaction = NULL;
                    echo json_encode(array(
                        "status" => "empty",
                        "message" => "No transaction available."
                    ));
                    exit;
                } else {
                    $transaction = $transaction[0];
                    // echo json_encode(array(
                    //     "status" => "success",
                    //     "data" => $transaction,
                    //     "message" => "Transaction will be assign soon."
                    // ));
                    // exit;
                }

                // Then assign the transaction to the cashier
                $sql_cmd = "UPDATE transactions SET idcounter = ?, idemployee = ?, status = 'serve' WHERE idtransaction = ?";
                $stmt = $conn->prepare($sql_cmd);
                $stmt->bind_param("sss", $counter_id, $_GET['employee_id'], $transaction['idtransaction']);
                $stmt->execute();
                $stmt->close();

                // Check again if the transaction was assigned to the cashier
                $sql_cmd = "SELECT *
                            FROM transactions t
                            WHERE t.idcounter = ? AND t.idemployee = ? AND t.status = 'serve'";
                $stmt = $conn->prepare($sql_cmd);
                $stmt->bind_param("ss", $counter_id, $_GET['employee_id']);
                $stmt->execute();
                $result = $stmt->get_result();
                $transaction = $result->fetch_all(MYSQLI_ASSOC);
                $stmt->close();
                if ($result->num_rows == 0) {
                    $transaction = NULL;
                    echo json_encode(array(
                        "status" => "empty",
                        "message" => "No transaction found."
                    ));
                    exit;
                } else {
                    $transaction = $transaction[0];
                    echo json_encode(array(
                        "status" => "success",
                        "data" => $transaction,
                        "message" => "Transaction assigned"
                    ));
                    // exit;

                    // Reminder for the transaction before cashier
                    $sql_cmd = "SELECT *
                                FROM requesters r
                                WHERE r.created_at > ?
                                ORDER BY r.created_at ASC
                                LIMIT 4,1";
                    $stmt = $conn->prepare($sql_cmd);
                    $stmt->bind_param("s", $transaction['transaction_time']);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $request_get = $result->fetch_all(MYSQLI_ASSOC);
                    $stmt->close();            
                    if ($result->num_rows == 0) {
                        // echo json_encode(array(
                        //     "status" => "empty",
                        //     "message" => "No request found."
                        // ));
                        exit;
                    }

                    $requester = $request_get[0];

                    // Get related from this above about requester's transaction
                    $sql_cmd = "SELECT *
                                FROM transactions
                                WHERE idrequester = ? AND status = 'pending'";
                    $stmt = $conn->prepare($sql_cmd);
                    $stmt->bind_param("s", $requester['id']);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $transaction_get = $result->fetch_all(MYSQLI_ASSOC);
                    $stmt->close();

                    $transaction_get = $transaction_get[0];
                    // echo json_encode(array(
                    //     "status" => "success",
                    //     "data" => $transaction_get,
                    //     "message" => "Request's transaction found."
                    // ));
                    // exit;
                    // echo json_encode(array(
                    //     "status" => "success",
                    //     "data" => $request_get,
                    //     "message" => "Transaction found."
                    // ));
                    // exit;
                    include "./email_content.php";
        
                    // $requester = $requester[0];

                    $requester_name = $requester['name'];
                    $requester_email = $requester['email'];
                    $requester_payment = $requester['payment'];

                    $request_data = array(
                        "name" => $requester_name,
                        "email" => $requester_email,
                        "payment" => $requester_payment,
                        "transaction_id" => $transaction_get['idtransaction'],
                        "queue_count_int" => $transaction_get['queue_number']
                    );
                    send_email_notify_before_5($request_data);
                }
                exit;  
            }   
            exit;
        } catch (Exception $e) {
            $conn->rollback();
        }
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

?>