<?php
include "./../includes/db_conn.php";
header("Content-Type: application/json");
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $stmt = $conn->prepare("SELECT queue_number, idcounter FROM transactions ORDER BY idtransaction DESC LIMIT 1");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    $queueNumber = $row['queue_number'] ?? "N/A";
    $counterNumber = $row['idcounter'] ?? "N/A";

    // Get the smallest "pending" queue number for the assigned counter (i.e., the current number being served)
    $stmt = $conn->prepare("SELECT MIN(queue_number) AS current_queue FROM transactions WHERE idcounter = ? AND status = 'pending'");
    $stmt->bind_param("i", $counterNumber);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    $currentQueueNumber = $row['current_queue'] ?? "N/A";

    echo json_encode(array(
        "status" => "success",
        "queueNumber" => $queueNumber,
        "counterNumber" => $counterNumber,
        "currentQueueNumber" => $currentQueueNumber
    ));
} else {
    echo json_encode(array(
        "status" => "error",
        "message" => "Method not allowed"
    ));
}
?>