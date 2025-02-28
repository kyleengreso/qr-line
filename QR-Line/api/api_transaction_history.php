<?php
include "./../includes/db_conn.php";


header("Content-Type: application/json");
if ($_SERVER["REQUEST_METHOD"] == "GET") {
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
            "message" => "Employees found."
        ));
    } else {
        echo json_encode(array(
            "status" => "error",
            "message" => "No employee found."
        ));
    }
}