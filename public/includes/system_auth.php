<?php
// Token helpers for the public web UI.

function encryptToken($payload, $key = '') {
    if (is_string($payload)) return $payload;
    return base64_encode(json_encode($payload));
}

function decryptToken($token, $key = '') {
    if (!$token) return null;

    if (substr_count($token, '.') === 2) {
        $parts = explode('.', $token);
        $payload = $parts[1];
        $remainder = strlen($payload) % 4;
        if ($remainder) $payload .= str_repeat('=', 4 - $remainder);
        $decoded = base64_decode(str_replace(['-', '_'], ['+', '/'], $payload));
        $json = json_decode($decoded, true);
        return is_array($json) ? $json : null;
    }

    $decoded = base64_decode($token, true);
    if ($decoded !== false) {
        $json = json_decode($decoded, true);
        if (is_array($json)) return $json;
    }

    $json = json_decode($token, true);
    if (is_array($json)) return $json;

    return null;
}

function set_token_cookie_from_value($tokenValue) {
    $expires = time() + (86400 * 30);
    setcookie('token', $tokenValue, $expires, "/");
}


function getDecodedTokenPayload() {
    if (!isset($_COOKIE['token'])) return null;
    global $master_key;
    $payload = decryptToken($_COOKIE['token'], $master_key ?? '');
    return $payload ?: null;
}

function isAuthenticated() {
    return getDecodedTokenPayload() !== null;
}

function restrictCheckLoggedIn() {
    $payload = getDecodedTokenPayload();
    if (!$payload) return;
    $role = $payload['role_type'] ?? ($payload['role'] ?? null);
    if (!$role && isset($_COOKIE['role_type'])) {
        $role = $_COOKIE['role_type'];
    }
    $norm = is_string($role) ? strtolower($role) : null;
    if ($norm === 'admin' || $norm === 'administrator' || $norm === 'superadmin') {
        header('Location: /public/admin/index.php');
        exit();
    }
    if ($norm === 'employee' || $norm === 'cashier' || $norm === 'attendant') {
        header('Location: /public/employee/index.php');
        exit();
    }
    return;
}

function requireRole(string $role) {
    $payload = getDecodedTokenPayload();
    if (!$payload) {
        if (session_status() == PHP_SESSION_NONE) session_start();
        $_SESSION['auth_notice'] = 'You need to login first';
        header('Location: /public/auth/login.php');
        exit();
    }   
    $current = $payload['role_type'] ?? ($payload['role'] ?? null);
    if (!$current && isset($_COOKIE['role_type'])) {
        $current = $_COOKIE['role_type'];
    }
    $norm_current = is_string($current) ? strtolower($current) : null;
    $norm_required = strtolower($role);

    $admin_aliases = ['admin', 'administrator', 'superadmin'];

    $allowed = false;
    if ($norm_required === 'admin') {
        $allowed = in_array($norm_current, $admin_aliases, true);
    } else {
        $allowed = ($norm_current === $norm_required);
    }

    if (!$allowed) {
        if (session_status() == PHP_SESSION_NONE) session_start();
        $_SESSION['auth_notice'] = 'You need to login first';
        header('Location: /public/auth/login.php');
        exit();
    }
}

function requireAdmin() {
    requireRole('admin');
}

function restrictAdminMode() {
    requireAdmin();
}

function restrictEmployeeMode() {
    $payload = getDecodedTokenPayload();
    if (!$payload) {
        if (session_status() == PHP_SESSION_NONE) session_start();
        $_SESSION['auth_notice'] = 'You need to login first';
        header('Location: /public/auth/login.php');
        exit();
    }
    $current = $payload['role_type'] ?? ($payload['role'] ?? null);
    if (!$current && isset($_COOKIE['role_type'])) {
        $current = $_COOKIE['role_type'];
    }
    $norm = is_string($current) ? strtolower($current) : null;
    $allowed = in_array($norm, ['employee', 'cashier'], true);

    if (!$allowed) {
        if (session_status() == PHP_SESSION_NONE) session_start();
        $_SESSION['auth_notice'] = 'You need to login first';
        header('Location: /public/auth/login.php');
        exit();
    }
}


if (php_sapi_name() !== 'cli' && realpath(__FILE__) === realpath($_SERVER['SCRIPT_FILENAME'])) {
    header('Content-Type: application/json');

    $action = $_GET['action'] ?? null;
    if (!$action) {
        $raw = file_get_contents('php://input');
        $json = json_decode($raw, true);
        if (is_array($json) && isset($json['action'])) $action = $json['action'];
    }

    if ($action === 'set_token') {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        $token = null;
        if (is_array($data) && isset($data['token'])) {
            $token = $data['token'];
        } else {
            $token = $_POST['token'] ?? null;
        }
        if (!$token) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Missing token']);
            exit;
        }
        set_token_cookie_from_value($token);
        if (session_status() == PHP_SESSION_NONE) session_start();
        if (isset($_SESSION['auth_notice'])) unset($_SESSION['auth_notice']);
        $roleValue = null;
        if (is_array($data) && isset($data['role'])) {
            $roleValue = $data['role'];
        } elseif (isset($_POST['role'])) {
            $roleValue = $_POST['role'];
        }
        if ($roleValue) {
            $expires = time() + (86400 * 30);
            setcookie('role_type', $roleValue, $expires, '/');
        }
        echo json_encode(['status' => 'success', 'message' => 'Token stored']);
        exit;
    }

    if ($action === 'clear_token') {
        setcookie('token', '', time() - 3600, '/');
        setcookie('role_type', '', time() - 3600, '/');
        echo json_encode(['status' => 'success', 'message' => 'Local token cleared']);
        exit;
    }

    if ($action === 'status') {
        $payload = getDecodedTokenPayload();
        $has_cookie = isset($_COOKIE['token']);
        echo json_encode([
            'status' => 'success',
            'has_cookie' => $has_cookie,
            'decoded' => $payload,
            'raw_cookie' => $has_cookie ? $_COOKIE['token'] : null
        ]);
        exit;
    }

    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Unknown action']);
    exit;
}

?>
