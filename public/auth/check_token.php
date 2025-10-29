<?php
header('Content-Type: application/json');

// Enhanced check_token: return whether cookie present and return decoded payload
// using existing decryptToken helper so the client can read role/username without
// attempting to parse HttpOnly cookies in JS.

// Include the token helpers. This file may include base.php internally; use include_once
// to avoid circular include problems.
include_once __DIR__ . '/../includes/system_auth.php';

$token = isset($_COOKIE['token']) ? $_COOKIE['token'] : null;
if (!$token) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "token cookie missing"]);
    exit;
}

// decryptToken will attempt AES decrypt, then JWT payload decode as fallback.
$payload = decryptToken($token, '');

// Prepare a minimal safe response: include role-type fields and username/id if present
$safe = [
    'role_type' => isset($payload['role_type']) ? $payload['role_type'] : null,
    'user_role' => isset($payload['user_role']) ? $payload['user_role'] : null,
    'role' => isset($payload['role']) ? $payload['role'] : null,
    'username' => isset($payload['username']) ? $payload['username'] : null,
    'id' => isset($payload['id']) ? $payload['id'] : null,
];

// Log for debugging
error_log(sprintf("[check_token] cookie present host=%s role=%s user=%s", $_SERVER['HTTP_HOST'] ?? '-', $safe['role'] ?? ($safe['role_type'] ?? $safe['user_role']), $safe['username'] ?? '-'));

echo json_encode(["status" => "success", "message" => "token cookie present", "data" => $safe]);
exit;
