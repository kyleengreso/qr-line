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

// Network Feature
/*
    NOTE: This feature only use for email notification

    $serverName: IP Address or Domain name of your server
        Default: 
            $serverName = getHostByName(getHostName()) . ":80" ..... Result will be 127.0.0.1:80
        Other options:
        $serverName = "qrline.psu.edu.ph:80"; // Domain name version

        [!] If your host is other than port 80, please change the port number at :80
*/
$serverName = getHostByName(getHostName()) . ":80"; // IP Address version
// $serverName = "qrline.psu.edu.ph:80"; // Domain name version

// Project Support
$project_address = " Tiniguiban Heights, Puerto Princesa City, Palawan, Philippines";
$project_email = " marcsysman@gmail.com";
$project_phone = " (+63)909-123-4567";

// Website Security Feature
$enable_http = true;                // Enable HTTP connection. Default: true
$enable_secure = true;              // Enable HTTPS connection. Default: true
$master_key = "master";             // Master key for encryption and decryption. Default: "master"
$disable_registration = false;      // Disable registration. Default: false

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

// Social Media Links
/*
    $social_media_show: show the social media links on the footer website
        Default: true   [true, false]
    $social_facebook_link: Direct to Facebook link
        Default: null   [null, "https://www.facebook.com/yourpage"]
    $social_twitter_link: Direct to Twitter link
        Default: null   [null, "https://www.twitter.com/yourpage"]
    $social_direct_link: Direct to your website link
        Default: null   [null, "https://www.yourwebsite.com"]
*/

$social_media_show = true;      // To show the social media links
$social_facebook_link = null;
$social_twitter_link = null;
$social_direct_link = null;

// Authers
function restrictAdminMode() {
    global $auth_path, $admin_path, $employee_path, $master_key;
    if (isset($_COOKIE['token'])) {
        $encryptToken = $_COOKIE['token'];
        $decryptToken = decryptToken($encryptToken, $master_key);

        // Ensure $decryptToken is a JSON string
        if (is_array($decryptToken)) {
            $decryptToken = json_encode($decryptToken);
        }

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
    global $auth_path, $admin_path, $employee_path, $master_key;
    if (isset($_COOKIE['token'])) {
        $encryptToken = $_COOKIE['token'];
        $decryptToken = decryptToken($encryptToken, $master_key);

        // Ensure $decryptToken is a JSON string
        if (is_array($decryptToken)) {
            $decryptToken = json_encode($decryptToken);
        }

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

function restrictCheckLoggedIn() {
    global $auth_path, $admin_path, $employee_path, $master_key;
    if (isset($_COOKIE['token'])) {
        $encryptToken = $_COOKIE['token'];
        $decryptToken = decryptToken($encryptToken, $master_key);

        // Ensure $decryptToken is a JSON string
        if (is_array($decryptToken)) {
            $decryptToken = json_encode($decryptToken);
        }

        $token = json_decode($decryptToken);
        if ($token->role_type == "admin") {
            header("Location: ./../admin/dashboard.php");
            exit();
        } elseif ($token->role_type == "employee") {
            header("Location: ./../employee/counter.php");
            exit();
        }
    }
}

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
        <link rel="stylesheet" href="/node_modules/bootstrap/dist/css/bootstrap.css">
        <link rel="stylesheet" href="/node_modules/bootstrap-icons/font/bootstrap-icons.css">
        <link rel="stylesheet" href="./../asset/css/theme.css">
    ';
}
function before_js() {
    echo '<script src="./../asset/js/0aa2c3c0f4.js"></script>';
}

function after_js() {
    echo '
    <script src="./../asset/js/base.js"></script>
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

// COUNTER TEXT
function counter_no_assigned() {
    return "No counter assigned";
}

function counter_no_available() {
    return "No counter available";
}
?>
