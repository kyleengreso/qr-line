<?php
include "./../includes/db_conn.php";


header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Can tell if one of the three keys are available

    $data = json_decode(file_get_contents("php://input"));

    if (!isset($data->name) || !isset($data->email) || !isset($data->purpose)) {
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
        $purpose = $data->purpose;

        // Insert user data into the database
        $stmt = $conn->prepare("INSERT INTO users (name, email, purpose) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $purpose);
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
        $stmt = $conn->prepare("INSERT INTO transactions (iduser, idcounter, queue_number, token_number, status, purpose) VALUES (?, ?, ?, ?, 'pending', ?)");
        $stmt->bind_param("iiiss", $user_id, $counter_id, $queue_number, $token_number, $purpose);
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
    if (isset($_GET['search'])) {
        $search = "%" . $_GET['search'] . "%";
        $stmt = $conn->prepare("SELECT * FROM transactions WHERE purpose LIKE ?");
        $stmt->bind_param("s", $search);
        $stmt->execute();
        $result = $stmt->get_result();
        $employees = $result->fetch_all(MYSQLI_ASSOC);
    } else if (isset($_GET['id'])) {
        $id = $_GET['id'];
        $stmt = $conn->prepare("SELECT * FROM transactions WHERE idtransaction = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $employees = $result->fetch_all(MYSQLI_ASSOC)[0];
    } else if (isset($_GET['page']) && isset($_GET['paginate'])) {
        $page = $_GET['page'];
        $paginate = $_GET['paginate'];
        $stmt = $conn->prepare("SELECT * FROM transactions LIMIT ?, ?");
        $stmt->bind_param("ii", $page, $paginate);
        $stmt->execute();
        $result = $stmt->get_result();
        $employees = $result->fetch_all(MYSQLI_ASSOC);
    } else {
        $stmt = $conn->prepare("SELECT * FROM transactions");
        $stmt->execute();
        $result = $stmt->get_result();
        $employees = $result->fetch_all(MYSQLI_ASSOC);
    }

    if ($employees) {
        echo json_encode(array(
            "status" => "success",
            "data" => $employees,
            "message" => "Transactions found."
        ));
    } else {
        echo json_encode(array(
            "status" => "error",
            "message" => "No transaction found."
        ));
    }
}