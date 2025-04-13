<?php
include "./../base.php";
include "./../includes/db_conn.php";
header("Content-Type: application/json");   
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Similar for the api_authenticate.php but this time it has token
    $data = json_decode(file_get_contents("php://input"));


    // method: "create", "update", "delete"
    if (isset($data->set_method)) {

        $method = $data->set_method;

        if ($method == "create") {
            $username = $data->username;
            $password = $data->password;
            $confirm_password = $data->confirm_password;

            if ($password != $confirm_password) {
                echo json_encode(array(
                    "status" => "error",
                    "message" => "Password does not match."
                ));
                exit;
            }
            
            $stmt = $conn->prepare("SELECT id FROM employees WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                echo json_encode(array(
                    "status" => "error",
                    "message" => "Username already exists."
                ));
                exit;
            } else {
                $hash_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO employees (username, password) VALUES (?, ?)");
                $stmt->bind_param("ss", $username, $hash_password);

                if ($stmt->execute()) {
                    echo json_encode(array(
                        "status" => "success",
                        "message" => "Employee registered successfully!"
                    ));
                } else {
                    echo json_encode(array(
                        "status" => "error",
                        "message" => "Error: " . $conn->error
                    ));
                }
            }

        } else if ($method == "update") {
            $id = $_GET['id'];
            $password = $data->password;
            $confirm_password = $data->confirm_password;

            if ($password != $confirm_password) {
                echo json_encode(array(
                    "status" => "error",
                    "message" => "Password does not match."
                ));
                exit;
            }


            try {
                if (empty($password) || empty($confirm_password)) {
                    echo json_encode(array(
                        "status" => "error",
                        "message" => "Nothing changed."
                    ));
                    exit;
                }
                $hash_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE employees SET password = ? WHERE id = ?");
                $stmt->bind_param("ss", $hash_password, $id);
                $stmt->execute();

                if ($stmt->affected_rows > 0) {
                    echo json_encode(array(
                        "status" => "success",
                        "message" => "Employee updated successfully!"
                    ));
                } else {
                    echo json_encode(array(
                        "status" => "error",
                        "message" => "No password changed."
                    ));
                }
            } catch (Exception $e) {
                echo json_encode(array(
                    "status" => "error",
                    "message" => "Error: " . $conn->error
                ));
            }
        } else if ($method == "delete") {
            $id = $_GET['id'];

            $stmt = $conn->prepare("DELETE FROM employees WHERE id = ?");
            $stmt->bind_param("s", $id);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                echo json_encode(array(
                    "status" => "success",
                    "message" => "Employee deleted successfully!"
                ));
            } else {
                echo json_encode(array(
                    "status" => "error",
                    "message" => "Employee not found."
                ));
            }
        }
    } else {
        echo json_encode(array(
            "status" => "error",
            "message" => "Method for employee not set."
        ));
    }

} else if ($_SERVER["REQUEST_METHOD"] == "GET") {

    // ID first
    if (isset($_GET['id'])) {
        $id = $_GET['id'];
        $stmt = $conn->prepare("SELECT id, username, created_at FROM employees WHERE id = ?");
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $employees = $result->fetch_all(MYSQLI_ASSOC)[0];
    } else if (isset($_GET['total_count'])) {
        $stmt = $conn->prepare("SELECT COUNT(id) as total FROM employees");
        $stmt->execute();
        $result = $stmt->get_result();
        $employees = $result->fetch_all(MYSQLI_ASSOC)[0];
    } else if (isset($_GET['search'])) {
        $search = "%" . $_GET['search'] . "%";
        $stmt = $conn->prepare("SELECT id, username, created_at FROM employees WHERE username LIKE ?");
        $stmt->bind_param("s", $search);
        $stmt->execute();
        $result = $stmt->get_result();
        $employees = $result->fetch_all(MYSQLI_ASSOC);
    } else if (isset($_GET['page']) && isset($_GET['paginate'])) {
        $page = $_GET['page'];
        $limit = $_GET['paginate'];
        $offset = ($page - 1) * $limit;
        $stmt = $conn->prepare("SELECT id, username, created_at, active, email, role_type, employee_last_login FROM employees LIMIT ? OFFSET ?");
        $stmt->bind_param("ii", $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        $employees = $result->fetch_all(MYSQLI_ASSOC);
    } else {
        $stmt = $conn->prepare("SELECT id, username, created_at FROM employees");
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
            "message" => "No employees found."
        ));
    }
}
?>