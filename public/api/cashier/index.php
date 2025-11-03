<?php
// Simple PHP proxy to forward /api/cashier requests from the PHP host (8080)
// to the Flask API (default 5000). Keeps existing frontend URLs working without
// requiring a web server reverse proxy.

// Target Flask base URL (can be overridden by env)
$flask_base = getenv('FLASK_PROXY_TARGET') ?: 'http://127.0.0.1:5000';
$target = rtrim($flask_base, '/') . '/api/cashier';

$method = $_SERVER['REQUEST_METHOD'];
$query = isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] !== '' ? ('?' . $_SERVER['QUERY_STRING']) : '';
$url = $target . $query;

$ch = curl_init($url);

// Forward headers selectively
$headers = [];
if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
    $headers[] = 'Authorization: ' . $_SERVER['HTTP_AUTHORIZATION'];
}
// Respect JSON content type if client sent it
$contentType = 'application/json';
if (!empty($_SERVER['CONTENT_TYPE'])) {
    $contentType = $_SERVER['CONTENT_TYPE'];
}
$headers[] = 'Content-Type: ' . $contentType;
// Forward cookies (e.g., token) so Flask can read them if needed
if (!empty($_SERVER['HTTP_COOKIE'])) {
    curl_setopt($ch, CURLOPT_COOKIE, $_SERVER['HTTP_COOKIE']);
}

curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

// Forward body for POST/PUT/PATCH
if (in_array($method, ['POST','PUT','PATCH','DELETE'])) {
    $raw = file_get_contents('php://input');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $raw);
}

// Optional: timeouts
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$errno = curl_errno($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$ct = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
curl_close($ch);

if ($errno) {
    http_response_code(502);
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => 'Upstream error',
        'code' => $errno
    ]);
    exit;
}

// Mirror upstream status and content-type
http_response_code($httpCode ?: 200);
if ($ct) header('Content-Type: ' . $ct);

echo $response !== false ? $response : '';
