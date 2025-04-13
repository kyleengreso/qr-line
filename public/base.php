<?php
// Session Control: Start Session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/./includes/db_conn.php';
require_once __DIR__ . '/./includes/system_auth.php';

// Project Information
$project_name = "QR-Line";
$project_name_alt = "qr-line";
$project_name_full = "QR-Line: Palawan State University";
$project_description = "QR-Line is a web-based queue management system utilizing QR codes for efficient and streamlined queue handling.";
$project_release = false;
$project_version = "1.0.0";

// Project Support
$project_address = "Tiniguiban Heights, Puerto Princesa City, Palawan, Philippines";
$project_email = "marcsysman@gmail.com";
$project_phone = "+63909-123-4567";

// Website Security Feature
$enable_http = true;
$enable_secure = true;
$master_key = "master";
$disable_registration = false;

// Email Feature setup
$email_feature = TRUE;

// SMTP AUTH
$smtp_host = "smtp.gmail.com";
$smtp_port = 465;
$smtp_email = "marcsysman@gmail.com";
$smtp_password = "zgojyaysdylvdlnh";

// PATHS
$root_path = "/public/";
$auth_path = $root_path . "/auth/";
$admin_path = $root_path . "/admin/";
$employee_path = $root_path . "/employee/";
$api_path = $root_path . "/api/";

// System Website Control
$system_development_mode = true;
$enable_register_employee = false;


// Website tweaks
$form_label_state = "hidden";





// Session Control: Redirect
function checkAuth() {
    global $auth_path, $master_key;
    if (isset($_COOKIE['token'])) {
        $encryptToken = $_COOKIE['token'];
        $decryptToken = decryptToken($encryptToken, $master_key);
        // $token = json_decode($decryptToken);
    }
    if (!isset($_COOKIE['token'])) {
        header("Location: ./../auth/login.php");
        exit();
    }
}

function restrictAdminMode() {
    // Action for admin only
    global $auth_path, $admin_path, $employee_path;
    if (isset($_COOKIE['token'])) {
        $encryptToken = $_COOKIE['token'];
        $decryptToken = decryptToken($encryptToken, $master_key);
        $token = json_decode($decryptToken);
        if ($token->role_type != "admin") {
            header("Location: ./../employee/counter.php");
            exit();
        }
    } else {
        header("Location: ./../auth/login.php");
        exit();
    }
}

function restrictEmployeeMode() {
    // Action for employee only
    global $auth_path, $admin_path, $employee_path, $master_key;
    if (isset($_COOKIE['token'])) {
        $encryptToken = $_COOKIE['token'];
        $decryptToken = decryptToken($encryptToken, $master_key);
        $token = json_decode($decryptToken);
        if ($token->role_type != "employee") {
            header("Location: ./../admin/dashboard.php");
            exit();
        }
    } else {
        header("Location: ./../auth/login.php");
        exit();
    }
}
// checkAuth();

// Transaction System
$transaction_cancelled_yesterday = true;

// if (isset($_COOKIE['token'])) {
//     $page_admin = array(

//     );
//     $data = decryptToken($_COOKIE['token'], $master_key);
//     if (empty($data)) {
//         header("Location: " . __DIR__ . "/auth/login.php");
//         exit();
//     }
// }

function head_icon() {
    echo '<link rel="icon" href="./../asset/images/favicon.png">';
}
function head_css() {
    echo '
        <link rel="stylesheet" href="./../asset/css/bootstrap.css">
        <link rel="stylesheet" href="./../asset/css/theme.css">
    ';
}
function before_js() {
    echo '<script src="./../asset/js/0aa2c3c0f4.js"></script>';
}

function after_js() {
    echo '
    <script src="./../asset/js/jquery-3.7.1.js"></script>
    <script src="./../asset/js/popper.min.js" ></script>
    <script src="./../asset/js/bootstrap.bundle.js"></script>
    <script src="./../asset/js/message.js"></script>';

    return;
}

function project_year() {
    // Get year for today
    $today = date("Y-m-d"); // Gets today's date in "YYYY-MM-DD" format
    $year = date("Y", strtotime($today)); // Extracts the year from the date
    return $year; // Returns the year
}
?>