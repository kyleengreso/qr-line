<?php
require_once __DIR__ . '/./../base.php';
require_once __DIR__ . '/./../includes/system_auth.php';

header("Content-Type: application/json");

global $conn;

if (!$conn) {
    echo json_encode(array(
        "status" => "error",
        "message" => "Database connection error"));
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $data = json_decode(file_get_contents("php://input"));

    if (!$data) {
        echo json_encode(array(
            "status" => "error",
            "message" => "Please provide data"));
        exit;
    }

    if (!isset($data->mode)) {
        echo json_encode(array(
            "status" => "error",
            "message" => "Mode is required"));
        exit;
    }

    $mode = $data->mode;

    // AUTHENICATION: LOGIN
    if ($mode == "login") {
        if (!isset($data->username) || !isset($data->password)) {
            echo json_encode(array(
                "status" => "error",
                "message" => "Username and password are required"));
            exit;
        }
        $username = $data->username;
        $password = $data->password;

        $sql_cmd = "SELECT id, username, password, role_type FROM employees WHERE username = ?";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $role_type = $user['role_type'];
                $date_logged_in = date('Y-m-d H:i:s');
                $date_expiration = date('Y-m-d H:i:s', strtotime('+1 day'));

                // Update the last login date
                $sql_cmd = "UPDATE employees SET employee_last_login = ? WHERE id = ?";
                $stmt = $conn->prepare($sql_cmd);
                $stmt->bind_param("ss", $date_logged_in, $user_id);
                $stmt->execute();
                $stmt->close();

                $user_id = $user['id'];
                $_SESSION['user_id'] = $user_id;
                $_SESSION['username'] = $username;
                $_SESSION['role_type'] = $role_type;
                $data_arr = array(
                    "user_id" => $user_id,
                    "username" => $username,
                    "role_type" => $role_type,
                    "date_logged_in" => $date_logged_in,
                    "date_expiration" => $date_expiration
                );

                $token_prepare = $data_arr;

                $encryptToken = encryptToken($token_prepare, $master_key);
                // Cookie setup for authenication
                setcookie("token", $encryptToken, strtotime($date_expiration), "/", "", $enable_secure, $enable_http);
                // // Feedback the Login Success
                echo json_encode(array(
                    "status" => "success",
                    "message" => "User authenticated",
                    "token" => $encryptToken,
                    // "data" => $data_arr
                ));
            } else {
                echo json_encode(array(
                    "status" => "error",
                    "message" => "Invalid username or password"));
            }
        } else {
            echo json_encode(array(
                "status" => "error",
                "message" => "Invalid username or password"));
        } 

    // AUTHENTICATION: LOGOUT
    } else if ($mode == "logout") {
        checkAuth();
        session_unset();
        session_destroy();
        // Delete cookie
        setcookie("token", "", time() - 3600, "/", "", $enable_secure, $enable_http);
        echo json_encode(array(
            "status" => "success",
            "message" => "User logged out"));

    // AUTHENTICATION: DAEMON

    } else if ($mode == "register") {
        if ($disable_registration) {
            echo json_encode(array(
                "status" => "error",
                "message" => "Registration is disabled"
            ));
            exit;
        }

        if (!isset($data->username) || !isset($data->password)) {
            echo json_encode(array(
                "status" => "error",
                "message" => "Username, password are required."));
            exit;
        }

        $username = $data->username;
        $password = $data->password;

        $sql_cmd = "SELECT username FROM employees WHERE username = ?";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo json_encode(array(
                "status" => "error",
                "message" => "Username already exists"));
            exit;
        }

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $sql_cmd = "INSERT INTO employees (username, password, role_type) VALUES (?, ?, 'employee')";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->bind_param("ss", $username, $hashed_password);
        $stmt->execute();
        $stmt->close();

        echo json_encode(array(
            "status" => "success",
            "message" => "User registered successfully"
        ));
        exit;

    } else if ($mode == "daemon") {

    // Employees
    } else if ($mode == "employeeCreate") {
        $username = $data->username;
        $password = $data->password;
        $role_type = $data->role_type;
        $email = $data->email;
        $active = $data->active;

        $conn->begin_transaction();
        try {
            // Check if username exists
            $stmt = $conn->prepare("SELECT id FROM employees WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();
            if ($result->num_rows > 0) {
                echo json_encode(array(
                    "status" => "error",
                    "message" => "Username already exists"
                ));
                exit;
            }
            $sql_cmd = "INSERT INTO employees (username, password, role_type, email, active) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql_cmd);
            $stmt->bind_param("sssss", $username, $password, $role_type, $email, $active);
            $stmt->execute();
            $stmt->close();
            $conn->commit();
            echo json_encode(array(
                "status" => "success",
                "message" => "Employee created successfully"
            ));
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(array(
                "status" => "error",
                "message" => "Error: " . $e->getMessage()
            ));
        }
        exit;
    } else if ($mode == "employeeEdit") {
        if (!isset($data->employee_id)) {
            echo json_encode(array(
                "status" => "error",
                "message" => "Employee ID is required"
            ));
            exit;
        }
        if (!isset($data->username) || !isset($data->password) || !isset($data->role_type) || !isset($data->email) || !isset($data->active)) {
            echo json_encode(array(
                "status" => "error",
                "message" => "Username, password, role type, email, active are required"
            ));
            exit;
        }
        $employee_id = $data->employee_id;
        $username = $data->username;
        $password = $data->password;
        $role_type = $data->role_type;
        $email = $data->email;
        $active = $data->active;
        // Check if username exists
        $sql_cmd = "SELECT username, role_type, email, active FROM employees WHERE id = ? LIMIT 1";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->bind_param("s", $employee_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $employee = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        if ($result->num_rows == 1) {
            try {
                $sql_cmd = "UPDATE employees SET username = ?, password = ?, role_type = ?, email = ?, active = ? WHERE id = ?";
                $stmt = $conn->prepare($sql_cmd);
                $stmt->bind_param("ssssss", $username, $password, $role_type, $email, $active, $employee_id);
                $stmt->execute();
                $stmt->close();
                echo json_encode(array(
                    "status" => "success",
                    "message" => "Employee updated successfully"
                ));
            } catch (Exception $e) {
                echo json_encode(array(
                    "status" => "error",
                    "message" => "Error: " . $e->getMessage()
                ));
            }
        } else {
            echo json_encode(array(
                "status" => "error",
                "message" => "Employee not found"
            ));
        }
        exit;
    } else if ($mode == "employeeDelete") {
        if (!isset($data->employee_id)) {
            echo json_encode(array(
                "status" => "error",
                "message" => "Employee ID is required"
            ));
            exit;
        }
        $employee_id = $data->employee_id;

        // Check if username exists
        $sql_cmd = "SELECT username FROM employees WHERE id = ? LIMIT 1";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->bind_param("s", $employee_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $employee = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        if ($result->num_rows == 0) {
            echo json_encode(array(
                "status" => "error",
                "message" => "Employee not found"
            ));
            exit;
        }
        $conn->begin_transaction();
        try {
            $sql_cmd = "DELETE FROM employees WHERE id = ?";
            $stmt = $conn->prepare($sql_cmd);
            $stmt->bind_param("s", $employee_id);
            $stmt->execute();
            $stmt->close();
            $conn->commit();
            echo json_encode(array(
                "status" => "success",
                "message" => "Employee deleted successfully"
            ));
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(array(
                "status" => "error",
                "message" => "Error: " . $e->getMessage()
            ));
        }
    } else {
        echo json_encode(array(
            "status" => "error",
            "message" => "Invalid mode"));
        exit;
    }

} else if ($_SERVER["REQUEST_METHOD"] === "GET") {

    // Get all employees
    if (isset($_GET['employees'])) {
        $sql_cmd = "SELECT id, username, role_type, email, active, created_at FROM employees";
        if (isset($_GET['id'])) {
            $sql_cmd .= " WHERE id = ?";
        }
        if (isset($_GET['page']) && isset($_GET['paginate'])) {
            $sql_cmd .= " LIMIT ?, ?";
        }
        $stmt = $conn->prepare($sql_cmd);
        if (isset($_GET['page']) && isset($_GET['paginate'])) {
            $page = $_GET['page'];
            $limit = $_GET['paginate'];
            $offset = ($page - 1) * $limit;
            $stmt->bind_param("ss", $offset, $limit);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $employees = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        if ($result->num_rows > 0) {
            echo json_encode(array(
                "status" => "success",
                "data" => $employees
            ));
        } else {
            echo json_encode(array(
                "status" => "error",
                "message" => "Employees not found"
            ));
        }
        exit;
    } else if (isset($_GET['employee_total_count'])) {
        // Get total employees from setup_system
        $sql_cmd = "SELECT setup_value_int FROM setup_system WHERE setup_key = 'employees_total'";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->execute();
        $result = $stmt->get_result();
        $employees_compare = $result->fetch_all(MYSQLI_ASSOC)[0];
        $stmt->close();

        // Get total employees from employees
        $sql_cmd = "SELECT COUNT(id) as total_employees FROM employees";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->execute();
        $result = $stmt->get_result();
        $employees = $result->fetch_all(MYSQLI_ASSOC)[0];
        $stmt->close();

        // Compare the total employees
        if ($employees_compare['setup_value_int'] != $employees['total_employees']) {
            $sql_cmd = "UPDATE setup_system SET setup_value_int = ? WHERE setup_key = 'employees_total'";
            $stmt = $conn->prepare($sql_cmd);
            $stmt->bind_param("s", $employees['total_employees']);
            $stmt->execute();
            $stmt->close();
        }

        echo json_encode(array(
            "status" => "success",
            "data" => $employees
        ));
        exit;
    } else if (isset($_GET['employee_search'])) {
        $search = "%" . $_GET['employee_search'] . "%";
        $sql_cmd = "SELECT id, username, role_type, email, active FROM employees WHERE username LIKE ?";
        if (isset($_GET['page']) && isset($_GET['paginate'])) {
            $sql_cmd .= " LIMIT ?, ?";
        }
        $stmt = $conn->prepare($sql_cmd);
        if (isset($_GET['page']) && isset($_GET['paginate'])) {
            $page = $_GET['page'];
            $limit = $_GET['paginate'];
            $offset = ($page - 1) * $limit;
            $stmt->bind_param("sss", $search, $offset, $limit);
        } else {
            $stmt->bind_param("s", $search);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $employees = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        if ($result->num_rows > 0) {
            echo json_encode(array(
                "status" => "success",
                "data" => $employees
            ));
        } else {
            echo json_encode(array(
                "status" => "error",
                "message" => "Employee not found"
            ));
        }
        exit;
    } else if (isset($_GET['employee_id'])) {
        if (!isset($_GET['employee_id'])) {
            echo json_encode(array(
                "status" => "error",
                "message" => "Employee ID is required"
            ));
            exit;
        }
        $employee_id = $_GET['employee_id'];
        $sql_cmd = "SELECT id, username, role_type, email, active FROM employees WHERE id = ?";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->bind_param("s", $employee_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $employee = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        if ($result->num_rows > 0) {
            echo json_encode(array(
                "status" => "success",
                "data" => $employee[0]
            ));
        } else {
            echo json_encode(array(
                "status" => "error",
                "message" => "Employee not found"
            ));
        }
        exit;


    // TRANSACTIONS
    } else if (isset($_GET['transactions_all'])) {
        $sql_cmd = "SELECT t.queue_number, t.transaction_time, t.status, r.payment FROM transactions t
                    LEFT JOIN requesters r ON t.idrequester = r.id ORDER BY t.transaction_time DESC";
        if (isset($_GET['page']) && isset($_GET['paginate'])) {
            $sql_cmd .= " LIMIT ?, ?";
        }
        $stmt = $conn->prepare($sql_cmd);
        if (isset($_GET['page']) && isset($_GET['paginate'])) {
            $page = $_GET['page'];
            $paginate = $_GET['paginate'];
            $offset = ($page - 1) * $paginate;
            $stmt->bind_param("ss", $offset, $paginate);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $transactions = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        if ($result->num_rows > 0) {
            echo json_encode(array(
                "status" => "success",
                "data" => $transactions
            ));
        } else {
            echo json_encode(array(
                "status" => "error",
                "message" => "Transactions not found"
            ));
        }
        exit;
    } else if (isset($_GET['transactions_total'])) {
        $sql_cmd = "SELECT COUNT(idtransaction) as total_transactions FROM transactions";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->execute();
        $result = $stmt->get_result();
        $transactions = $result->fetch_all(MYSQLI_ASSOC)[0];
        $stmt->close();

        // Get total transactions from setup_system
        $sql_cmd = "SELECT setup_value FROM setup_system WHERE setup_key = 'transactions_total'";
        
        echo json_encode(array(
            "status" => "success",
            "data" => $transactions
        ));
    } else if (isset($_GET['transactions_today'])) {
        $sql_cmd = "SELECT 
                    (SELECT COUNT(idtransaction) FROM transactions WHERE transaction_time = CURDATE()) AS transactions_total_today,
                    (SELECT COUNT(idtransaction) FROM transactions WHERE transaction_time = CURDATE() AND status = 'completed') AS transactions_completed,
                    (SELECT COUNT(idtransaction) FROM transactions WHERE transaction_time = CURDATE() AND status = 'pending') AS transactions_pending,
                    (SELECT COUNT(idtransaction) FROM transactions WHERE transaction_time = CURDATE() AND status = 'cancelled') AS transactions_cancelled";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->execute();
        $result = $stmt->get_result();
        $transactions = $result->fetch_all(MYSQLI_ASSOC)[0];
        $stmt->close();
        if ($result->num_rows > 0) {
            echo json_encode(array(
                "status" => "success",
                "data" => $transactions
            ));
        } else {
            echo json_encode(array(
                "status" => "error",
                "message" => "Transactions not found"
            ));
        }
        exit;
    } else if (isset($_GET['transactions_filter'])) {
        $sql_cmd = "SELECT * FROM requesters r 
                    LEFT JOIN transactions t ON r.id = t.idrequester
                    WHERE 1=1";
        $params = [];
        $types = '';
        
        if (isset($_GET['payment'])) {
            if ($_GET['payment'] != 'none') {
                $sql_cmd .= " AND payment = ?";
                $params[] = $_GET['payment'];
                $types .= 's';
            }
        }
        
        if (isset($_GET['specific_email'])) {
            if ($_GET['specific_email'] != 'none') {
                $sql_cmd .= " AND email LIKE ?";
                $params[] = '%' . $_GET['specific_email'] . '%';
                $types .= 's';
            }
        }
        
        $sql_cmd .= " ORDER BY t.transaction_time DESC";

        if (isset($_GET['page']) && isset($_GET['paginate'])) {
            $page = (int)$_GET['page'];
            $paginate = (int)$_GET['paginate'];
            $offset = ($page - 1) * $paginate;
            $sql_cmd .= " LIMIT ?, ?";
            $params[] = $offset;
            $params[] = $paginate;
            $types .= 'ss';
        }
        
        $stmt = $conn->prepare($sql_cmd);
        
        if ($types) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $transactions = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        if ($result->num_rows > 0) {
            echo json_encode(array(
                "status" => "success",
                "data" => $transactions
            ));
        } else {
            echo json_encode(array(
                "status" => "error",
                "data" => [],
                "message" => "Transactions not found"
            ));
        }
        exit;
    
    // COUNTERS
    } else if (isset($_GET['counters_all'])) {
        $sql_cmd = "SELECT * FROM counter c
                    LEFT JOIN employees e ON c.idemployee = e.id
                    WHERE 1=1 ";
        $params = [];
        $types = '';
        if (isset($_GET['search'])) {
            $sql_cmd .= " AND e.username LIKE ?";
            $params[] = '%' . $_GET['search'] . '%';
            $types .= 's';
        }
        if (isset($_GET['page']) && isset($_GET['paginate'])) {
            $sql_cmd .= " LIMIT ?, ?";
            $limit = ($_GET['page'] - 1) * $_GET['paginate'];
            $params[] = $limit;
            $params[] = $_GET['paginate'];
            $types .= 'ss';
        }
        $stmt = $conn->prepare($sql_cmd);
        if ($types) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $counters = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        if ($result->num_rows > 0) {
            echo json_encode(array(
                "status" => "success",
                "data" => $counters
            ));
        } else {
            echo json_encode(array(
                "status" => "error",
                "message" => "Counters not found"
            ));
        }
        exit;
    } else if (isset($_GET['counters_available'])) {
        $sql_cmd = "SELECT e.username, e.id, 
                    CASE WHEN c.queue_count IS NULL THEN 'Available'
                    ELSE 'Busy' END as availability
                    FROM employees e
                    LEFT JOIN counter c ON e.id = c.idemployee
                    WHERE e.role_type = 'employee' AND e.active = 1";
        if (isset($_GET['page']) && isset($_GET['paginate'])) {
            $sql_cmd .= " LIMIT ?, ?";
        }
        $stmt = $conn->prepare($sql_cmd);
        if (isset($_GET['page']) && $_GET['paginate']) {
            $page = $_GET['page'];
            $paginate = $_GET['paginate'];
            $offset = ($page - 1) * $paginate;
            $stmt->bind_param("ss", $offset, $paginate);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $counters = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        if ($result->num_rows > 0) {
            echo json_encode(array(
                "status" => "success",
                "data" => $counters
            ));
        } else {
            echo json_encode(array(
                "status" => "error",
                "message" => "Counters not found"
            ));
        }
        exit;
    } else if (isset($_GET['counter_id'])) {
        $sql_cmd = "SELECT c.idcounter, c.counterNumber, c.idemployee, c.queue_count, e.username 
                    FROM counter c 
                    LEFT JOIN employees e ON c.idemployee = e.id 
                    WHERE c.idcounter = ? AND e.role_type = 'employee'";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->bind_param("s", $_GET['counter_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $counter = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        if ($result->num_rows > 0) {
            echo json_encode(array(
                "status" => "success",
                "data" => $counter[0]
            ));
        } else {
            echo json_encode(array(
                "status" => "error",
                "message" => "Counter not found"
            ));
        }
        exit;
    } else if (isset($_GET['counter_total'])) {
        $sql_cmd = "SELECT COUNT(idcounter) as total_counters FROM counter";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->execute();
        $result = $stmt->get_result();
        $counter = $result->fetch_all(MYSQLI_ASSOC)[0];
        $stmt->close();
        if ($result->num_rows > 0) {
            echo json_encode(array(
                "status" => "success",
                "data" => $counter
            ));
        } else {
            echo json_encode(array(
                "status" => "error",
                "message" => "Counter not found"
            ));
        }
        exit;




    // REQUESTERS
    }


} else {
    echo json_encode(array(
        "status" => "error",
        "message" => "Invalid request method"));
}