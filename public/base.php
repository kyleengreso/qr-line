<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

@include_once __DIR__ . '/includes/config.php';

$project_name = "QR-Line";
$project_name_alt = "qr-line";
$project_name_full = "QR-Line: Palawan State University";
$project_description = "QR-Line is a web-based queue management system utilizing QR codes for efficient and streamlined queue handling.";
$project_release = false;
$project_version = "1.0.0";

$serverName = "192.168.1.137:80"; // IP Address version

$project_address = " Tiniguiban Heights, Puerto Princesa City, Palawan, Philippines";
$project_email = " marcsysman@gmail.com";
$project_phone = " (+63)909-123-4567";

$envPath = __DIR__ . '/.env';
if (!file_exists($envPath)) {
    die('.env file missing.');
}

foreach (parse_ini_file($envPath) as $key => $value) {
    $GLOBALS[$key] = $value;
    putenv("$key=$value");
}


$enable_http = true;                // Enable HTTP connection. Default: true
$enable_secure = true;              // Enable HTTPS connection. Default: true
$master_key = "master";             // Master key for encryption and decryption. Default: "master"

require_once __DIR__ . '/./includes/system_auth.php';

$token = null;
if (isset($_COOKIE['token'])) {
    $decoded = decryptToken($_COOKIE['token'], $master_key ?? '');
    if (is_array($decoded)) {
        // Convert to stdClass for object-style access in templates
        $token = json_decode(json_encode($decoded));
    } elseif (is_object($decoded)) {
        $token = $decoded;
    } else {
        $token = null;
    }
}

$email_feature = FALSE;

// SMTP AUTH
$smtp_host = "smtp.gmail.com";
$smtp_port = 465;
$smtp_email = "marcsysman@gmail.com";
$smtp_password = "zgojyaysdylvdlnh";

$root_path = "/public/";
$auth_path = $root_path . "/auth/";
$admin_path = $root_path . "/admin/";
$employee_path = $root_path . "/employee/";
$api_path = $root_path . "/api/";

$system_development_mode = true;
$enable_register_employee = false;


$form_label_state = "hidden";
date_default_timezone_set("Asia/Manila");

$social_media_show = true;          // To show the social media links
$social_facebook_link = null;
$social_twitter_link = null;
$social_direct_link = null;


$transaction_cancelled_yesterday = true;


function head_icon() {
    echo '<link rel="icon" href="./../asset/images/favicon.png">';
}
function head_css() {
    echo '
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
        <link rel="stylesheet" href="./../asset/css/theme.css">
    ';
}
function head_meta() {
    echo "<meta name=\"theme-color\" content=\"#ff6e37\">\n";
}
function before_js() {
    echo '
    <script src="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.2.1/js/all.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
    ';
}

function after_js() {
    $endpoint = isset($GLOBALS['endpoint_server']) ? rtrim($GLOBALS['endpoint_server'], '/') : '';
    $endpoint_json = json_encode($endpoint, JSON_UNESCAPED_SLASHES);
    if ($endpoint_json === false) {
        $endpoint_json = '""';
    }
    echo "<script>window.endpointHost = {$endpoint_json} || '';window.API_BASE = window.endpointHost ? window.endpointHost.replace(/\\/+$/, '') + '/api' : '';</script><script src=\"./../asset/js/base.js\"></script><script src=\"https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js\"></script><script src=\"./../asset/js/message.js\"></script>";
    return;
}

function project_year() {
    
    // Get year for today
    $today = date("Y-m-d");                 // Gets today's date in "YYYY-MM-DD" format
    $year = date("Y", strtotime($today));   // Extracts the year from the date
    return $year;                           // Returns the year
}

// COUNTER TEXTS
function counter_no_assigned() {
    return "No counter assigned";
}

function counter_no_available() {
    return "No counter available";
}
?>
