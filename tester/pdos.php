<?php
// /var/home/mac/src/qr-line/tester/pdos.php
// Persistent PDO connection test. Configure via env vars or edit defaults below.

$host   = getenv('DB_HOST') ?: '77.37.35.51';
$port   = getenv('DB_PORT') ?: '3306';
$dbname = trim(getenv('DB_NAME') ?: 'u273960544_qrline');
$user   = getenv('DB_USER') ?: 'u273960544_qrliners';
$pass   = getenv('DB_PASS') ?: 'QRlin3rs*';
$driver = getenv('DB_DRIVER') ?: 'mysql';

$dsn = sprintf('%s:host=%s;port=%s;dbname=%s;charset=utf8mb4', $driver, $host, $port, $dbname);

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_PERSISTENT        => true,
    PDO::ATTR_DEFAULT_FETCH_MODE=> PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    echo "Persistent PDO connection established.\n";

    // Simple test query
    $stmt = $pdo->query('SELECT 1 AS ok');
    $result = $stmt->fetch();
    echo "Test query returned: " . json_encode($result) . PHP_EOL;

    // Prepare and run INSERT into `wisher`
    // created_at is TINYINT(4) in your DDL; use time() % 256 to fit into 0-255
    $created_at = time() % 256;
    $insert = $pdo->prepare('INSERT INTO wisher (created_at) VALUES (:created_at)');
    $insert->execute([':created_at' => $created_at]);
    $lastId = $pdo->lastInsertId();

    echo "Inserted wisher id: {$lastId}, created_at: {$created_at}\n";

    // Optionally fetch the inserted row to verify
    $check = $pdo->prepare('SELECT * FROM wisher WHERE id = :id');
    $check->execute([':id' => $lastId]);
    $row = $check->fetch();
    echo "Inserted row: " . json_encode($row) . PHP_EOL;

    // exit(0);
} catch (PDOException $e) {
    $msg = $e->getMessage();
    // Detect the MySQL resource limit error and try a fallback or print a clear instruction.
    if (stripos($msg, 'max_connections_per_hour') !== false) {
        $altUser = getenv('DB_FALLBACK_USER');
        $altPass = getenv('DB_FALLBACK_PASS');

        if ($altUser !== false && $altPass !== false) {
            try {
                $pdo = new PDO($dsn, $altUser, $altPass, $options);
                echo "Connected using fallback DB credentials (DB_FALLBACK_USER).\n";

                // Minimal verification to avoid duplicating the whole try block.
                $stmt = $pdo->query('SELECT 1 AS ok');
                $result = $stmt->fetch();
                echo "Test query returned: " . json_encode($result) . PHP_EOL;

                // exit(0);
            } catch (PDOException $e2) {
                fwrite(STDERR, "Fallback connection failed: " . $e2->getMessage() . PHP_EOL);
                // exit(1);
            }
        }

        fwrite(STDERR, "PDO connection failed: user has exceeded 'max_connections_per_hour'.\n");
        fwrite(STDERR, "If you control the DB server, run an ALTER USER command to raise or remove the limit, or provide alternate credentials via DB_FALLBACK_USER and DB_FALLBACK_PASS environment variables.\n");
        // exit(2);
    }

    fwrite(STDERR, "PDO connection failed: " . $msg . PHP_EOL);
    // exit(1);
}