<?php
header('Content-Type: application/json');

// Small endpoint that accepts JSON { token: "..." } and sets a cookie on this origin.
// The login page posts to this after receiving the token from the Python API.

include_once __DIR__ . '/../includes/system_auth.php';

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!$data || !isset($data['token'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing token']);
    exit;
}

$token = $data['token'];

// Store cookie (simple pass-through). If you want to encrypt it before storing,
// call encryptToken($payload, $master_key) and store the result instead.
set_token_cookie_from_value($token);

echo json_encode(['status' => 'success', 'message' => 'Token stored']);
exit;

?>
