<?php
include "./../includes/db_conn.php";


header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Can tell if one of the three keys are available

    $data = json_decode(file_get_contents("php://input"));

    if (!isset($data->name) || !isset($data->email) || !isset($data->payment)) {
        echo json_encode(array(
            "status" => "error",
            "message" => "Please fill up the information."
        ));
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
        $stmt->bind_param("iiiss", $user_id, $counter_id, $queue_number, $token_number, $payment);
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
    $where_trigger = FALSE; // Flag to track if WHERE clause has been added

    function do_where() {
        global $sql_cmd, $where_trigger;
        if (!$where_trigger) {
            $sql_cmd .= " WHERE ";
            $where_trigger = TRUE;
        } else {
            $sql_cmd .= " AND ";
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
}
?>