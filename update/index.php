<?php
include_once './../public/base.php';
$patch_version = "0.199.2";
$patch_version_current = null;

if (isset($_GET['install'])) {
    echo "nyaa!<br>";

    /*
        Patch Version: 0.199.2
    */

    // Install new table
    try {
        $sql_cmd = "CREATE TABLE IF NOT EXISTS `scheduler` (
                    `schedule_id` int NOT NULL AUTO_INCREMENT,
                    `enable` tinyint NOT NULL DEFAULT (0),
                    `schedule_key` varchar(50) NOT NULL DEFAULT '0',
                    `date_start` date DEFAULT NULL,
                    `date_end` date DEFAULT NULL,
                    `time_start` time DEFAULT NULL,
                    `time_end` time DEFAULT NULL,
                    `comment` text,
                    `repeat` enum('daily','weekly','month','Sun','Mon','Tue','Wed','Thu','Fri','Sat') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
                    `everyday` text,
                    `schedule_type` enum('requester','maintenance') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
                    `managed_by` int DEFAULT NULL,
                    PRIMARY KEY (`schedule_id`)
                ) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='Schedule for whole operation';
        ";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            echo "Scheduler table is created.<br>";
        } else if ($stmt->affected_rows == 0) {
            echo "Scheduler table is already created. Skipping...<br>";
        }

        // Check if the default schedule exists
        $sql_cmd = "SELECT * FROM scheduler WHERE schedule_key = 'requester_form'";
        $stmt = $conn->prepare($sql_cmd);
        $stmt->execute();
        $result = $stmt->get_result();
        $schedule = $result->fetch_assoc();
        if (!$schedule) {
            // Insert default schedule for requester_form
            $sql_cmd = "INSERT INTO scheduler (schedule_key, time_start, time_end, schedule_type, `repeat`, `everyday`, `enable`) 
                        VALUES ('requester_form', '08:00:00', '17:00:00', 'requester', 'daily', 'mon;tue;wed;thu;fri', 1)";
            $stmt = $conn->prepare($sql_cmd);
            $stmt->execute();
            if ($stmt->affected_rows > 0) {
                echo "Default schedule for requester_form is created.<br>";
            } else if ($stmt->affected_rows == 0) {
                echo "Default schedule for requester_form is already created. Skipping...<br>";
            }
        } else {
            echo "Default schedule for requester_form is already created. Skipping...<br>";
        }
        $conn->commit();
        $stmt->close();
    } catch (Exception $e) {
        $conn->rollback();
        echo "Error: " . $e->getMessage() . "<br>";
    }
}
// Patch Versioning
$sql_cmd = "SELECT *
            FROM setup_system
            WHERE setup_key = 'patch_version'";
$stmt = $conn->prepare($sql_cmd);
$stmt->execute();
$result_patch = $stmt->get_result();
$setup_patch = $result_patch->fetch_assoc();
if ($setup_patch) {
    // Reserved
    $patch_version_current = $setup_patch['setup_value'];
} else {
    $sql_cmd = "INSERT INTO setup_system (setup_key, setup_value) 
                VALUES ('patch_version', '0.199.1')";
    $stmt = $conn->prepare($sql_cmd);
    $stmt->execute();
    if ($stmt->affected_rows > 0) {
        echo "Patch system is installed. You can reload the page.";
    } else {
        echo "Failed to install the patch system. Please contact the developer.";
    }
    exit;
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $patch_version?> | QR-Line</title>
    <link rel="stylesheet" href="/public/asset/css/bootstrap.css">
    <link rel="stylesheet" href="/public/asset/css/bootstrap-icon.css">
    <link rel="stylesheet" href="/public/asset/css/theme.css">
</head>
<body>
    <div class="container-sm" style="margin-top:50px;max-width: 500px;">
        <div class="card shadow">
            <div class="card-header text-center">
                <h5 class="fw-bold">QR-Line</h5>
                <p>Patch Version: <?php echo $patch_version?></p>
            </div>
            <div class="card-body">
                <p class="text-center">This is a patch version. Please check the details below.</p>
                <p class="text-center">Your current patch version is <span class="fw-bold"><?php echo $patch_version_current?></span></p>
                <div class="d-flex justify-content-center">
                    <div class="row">
                        <div class="col-12">
                            <span class="fw-bold">
                                Changes:
                            </span>
                        </div>
                        <div class="col-12">
                            <ul>
                                <li>Added patch versioning system.</li>
                                <li>Added scheduler table</li>
                                <li>Added scheduler page (Admin only)</li>
                            </ul>
                        </div>
                        <div class="col-12">
                            <?php if ($patch_version_current < $patch_version) : ?>
                            <p class="text-danger">Your patch version is outdated. You can update this patch version.</p>
                            <a class="btn btn-success" href="/update/?install">Install Update</a>
                            <?php elseif ($patch_version_current == $patch_version) : ?>
                            <p class="text-success">Your patch version is up to date.</p>
                            <?php else : ?>
                            <p class="text-success">Your patch version is not compatible.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<script src="/public/asset/js/jquery-3.7.1.js"></script>
<script src="/public/asset/js/bootstrap.bundle.js"></script>
</body>
</html>

