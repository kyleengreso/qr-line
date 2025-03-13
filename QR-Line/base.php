<?php

$project_name = "QR-Line";

// PATHS
$root_path = "/QR-Line/";
$auth_path = $root_path . "auth/";
$admin_path = $root_path . "admin/";
$employee_path = $root_path . "employee/";
$api_path = $root_path . "apif/";

function login_as_employee() {
    // global $auth_path, $admin_path, $employee_path;
    // if (isset($_SESSION['username']) && isset($_SESSION['role_type'])) {
    //     if ($_SESSION['role_type'] == "admin" && basename($_SERVER['PHP_SELF']) != "dashboard.php") {
    //         header("Location: " . $admin_path . "dashboard.php");
    //         exit();
    //     } else if ($_SESSION['role_type'] == "employee" && basename($_SERVER['PHP_SELF']) != "counter.php") {
    //         header("Location: " . $employee_path . "counter.php");
    //         exit();
    //     }
    // } else if (basename($_SERVER['PHP_SELF']) != "login.php") {
    //     header("Location: " . $auth_path . "login.php");
    //     exit();
    // }
}

// function restrict_admin_access() {
//     global $auth_path;
//     if (!isset($_SESSION['username']) || $_SESSION['role_type'] != "admin") {
//         header("Location: " . $auth_path . "login.php");
//         exit();
//     }
// }

// function restrict_employee_access() {
//     global $auth_path;
//     if (!isset($_SESSION['username']) || $_SESSION['role_type'] != "employee") {
//         header("Location: " . $auth_path . "login.php");
//         exit();
//     }
// }

function head_icon() {
    echo '<link rel="icon" href="./../asset/images/favicon.png">';
}
function head_css() {
    echo '
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css">
        <link rel="stylesheet" href="./../asset/css/bootstrap.css">
        <link rel="stylesheet" href="./../asset/css/theme.css">
    ';
}
function before_js() {
    echo '<script src="./../asset/js/0aa2c3c0f4.js"  ></script>';
}

function after_js() {
    echo '
    <script src="./../asset/js/jquery-3.7.1.js"></script>
    <script src="./../asset/js/popper.min.js" ></script>
    <script src="./../asset/js/bootstrap.bundle.js"></script>';
    return;
}
?>