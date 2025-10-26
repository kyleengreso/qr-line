<?php

// Local DB
$servername = "127.0.0.1";
$username = "root";
$password = "root";
$dbname = "qr_queue_system";
$port = 3306;

// PROD DB
// $servername = "77.37.35.51";
// $username = "u273960544_qrliners";
// $password = "QRlin3rs*";
// $dbname = "u273960544_qrline";

// Create a persistent PDO connection and provide a small compatibility wrapper
try {
    $dsn = "mysql:host={$servername};port={$port};dbname={$dbname};charset=utf8mb4";

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_PERSISTENT => true,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ];

    // If ca.pem exists in this directory, enable SSL CA option
    $caPath = __DIR__ . './ca.pem';
    if (file_exists($caPath)) {
        $options[PDO::MYSQL_ATTR_SSL_CA] = $caPath;
    }

    $pdo = new PDO($dsn, $username, $password, $options);

} catch (PDOException $e) {
    die("Unable to connect to the database: " . $e->getMessage());
}

if (!defined('MYSQLI_ASSOC')) define('MYSQLI_ASSOC', 1);
if (!defined('MYSQLI_NUM')) define('MYSQLI_NUM', 2);
if (!defined('MYSQLI_BOTH')) define('MYSQLI_BOTH', 3);

class DBResult {
    public $rows = [];
    public $num_rows = 0;

    public function __construct($stmt)
    {
        try {
            $this->rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->rows = [];
        }
        $this->num_rows = count($this->rows);
    }

    public function fetch_all($mode = null)
    {
        return $this->rows;
    }

    public function fetch_assoc()
    {
        return $this->rows ? $this->rows[0] : null;
    }
}

class DBStatement {
    private $stmt;
    private $params = [];
    private $pdo;
    public $affected_rows = 0;
    public $insert_id = null;

    public function __construct($stmt, $pdo)
    {
        $this->stmt = $stmt;
        $this->pdo = $pdo;
    }

    public function bind_param()
    {
        $args = func_get_args();
        if (count($args) == 0) return;
        if (is_string($args[0])) {
            array_shift($args);
        }
        $this->params = $args;
    }

    public function execute()
    {
        try {

            // execute with parameters
            $ok = $this->stmt->execute($this->params);
            $this->affected_rows = $this->stmt->rowCount();

            // set insert_id from PDO lastInsertId if available
            try {
                $last = $this->pdo->lastInsertId();
                if ($last !== "0") {
                    $this->insert_id = $last;
                }
            } catch (Exception $e) {

            }
            return $ok;
        } catch (Exception $e) {
            // store error for compatibility
            $this->error = $e->getMessage();
            return false;
        }
    }

    public function get_result()
    {
        return new DBResult($this->stmt);
    }

    public function close()
    {
        $this->stmt = null;
    }
}

class DBWrapper {

    public $pdo = null;
    public $error = null;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function prepare($sql)
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            return new DBStatement($stmt, $this->pdo);
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            return null;
        }
    }

    public function query($sql)
    {
        try {
            $stmt = $this->pdo->query($sql);
            if ($stmt === false) return false;
            return new DBResult($stmt);
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    public function begin_transaction()
    {
        return $this->pdo->beginTransaction();
    }

    public function commit()
    {
        return $this->pdo->commit();
    }

    public function rollback()
    {
        return $this->pdo->rollBack();
    }

    public function set_charset($cs)
    {
        return true;
    }

    public function close()
    {
        // allow explicit close
        $this->pdo = null;
    }
}

// Provide $conn variable for compatibility with existing code
$conn = new DBWrapper($pdo);

?>