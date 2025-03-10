<?php
include "./../includes/db_conn.php";
header("Content-Type: application/json");
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $stmt = $conn->prepare("SELECT t.queue_number, c.counterNumber, t.idcounter
                            FROM transactions t
                            LEFT JOIN counter c ON t.idcounter = c.idcounter
                            ORDER BY t.idtransaction DESC LIMIT 1");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    $queueNumber = $row['queue_number'] ?? "N/A";
    $counterNumber = $row['counterNumber'] ?? "N/A";
    $idcounter = $row['idcounter'] ?? "N/A";
    // Get the smallest "pending" queue number for the assigned counter (i.e., the current number being served)
    $stmt = $conn->prepare("SELECT MIN(queue_number) AS current_queue FROM transactions WHERE idcounter = ? AND status = 'pending'");
    $stmt->bind_param("i", $idcounter);
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