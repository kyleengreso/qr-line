<?php
include "./../includes/db_conn.php";
header("Content-Type: application/json");
if ($_SERVER["REQUEST_METHOD"] == "GET") {

    $data = json_decode(file_get_contents("php://input"));

    if (!isset($_GET['requester_token'])) {
        echo json_encode(array(
            "status" => "error",
            "message" => "Please provide the requester token that you submitted."
        ));
        exit;
    }

    $requester_token = $_GET['requester_token'];

    $stmt = $conn->prepare("SELECT t.queue_number, c.counterNumber, t.idcounter, c.queue_count
                            FROM transactions t
                            LEFT JOIN counter c ON t.idcounter = c.idcounter
                            WHERE t.token_number = ?
                            ORDER BY t.idtransaction DESC LIMIT 1");
    $stmt->bind_param("s", $requester_token);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    // Get the highest queue_count from counter table
    $sql_cmd = "SELECT MAX(queue_count) AS queue_count FROM counter";
    $stmt = $conn->prepare($sql_cmd);
    $stmt->execute();
    $result = $stmt->get_result();
    $setup_row = $result->fetch_assoc();
    $queue_count_int = $setup_row['queue_count'];
    $stmt->close();

    echo json_encode(array(
        "status" => "success",
        "queueNumber" => $row['queue_number'] ?? "N/A",
        "counterNumber" => $row['counterNumber'] ?? "N/A",
        "currentQueueNumber" => $queue_count_int ?? "N/A"
    ));
    exit;
    $queueNumber = $row['queue_number'] ?? "N/A";
    $counterNumber = $row['counterNumber'] ?? "N/A";
    $idcounter = $row['idcounter'] ?? "N/A";
    // Get the smallest "pending" queue number for the assigned counter (i.e., the current number being served)
    $stmt = $conn->prepare("SELECT MIN(queue_number) AS current_queue FROM transactions WHERE idcounter = ? AND status = 'pending'");
    $stmt->bind_param("s", $idcounter);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    $currentQueueNumber = $row['queue_count'] ?? "N/A";

    echo json_encode(array(
        "status" => "success",
        "queueNumber" => $queueNumber,
        "counterNumber" => $counterNumber,
        "currentQueueNumber" => $currentQueueNumber
    ));
} else if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    if (!isset($_GET['cancel'])) {
        echo json_encode(array(
            "status" => "error",
            "message" => "Please tell that transaction is you want to cancel."
        ));
        exit;
    } else {
        $requester_token = $_GET['requester_token'];

        // Checking if that transaction was already cancelled
        $sql_cmd = "SELECT status FROM transactions WHERE token_number = ?";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->bind_param("s", $requester_token);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        if ($row['status'] == "cancelled") {
            echo json_encode(array(
                "status" => "error",
                "message" => "Transaction has already cancelled."
            ));
            exit;
        }
        $sql_cmd = "UPDATE transactions SET status = 'cancelled' WHERE token_number = ?";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->bind_param("s", $requester_token);
        $stmt->execute();
        $stmt->close();

        echo json_encode(array(
            "status" => "success",
            "message" => "Transaction has been cancelled."
        ));
    }
} else {
    echo json_encode(array(
        "status" => "error",
        "message" => "Method not allowed"
    ));
}
?>