<?php

$allowed_origins = [
    'http://127.0.0.1:8080',
    'http://localhost',
    'https://qrline.miceff.com',
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

if ($origin !== '') {
    if (in_array('*', $allowed_origins, true) || in_array($origin, $allowed_origins, true)) {
        header('Access-Control-Allow-Origin: ' . (in_array('*', $allowed_origins, true) ? '*' : $origin));
    }
} else {
    if (in_array('*', $allowed_origins, true)) {
        header('Access-Control-Allow-Origin: *');
    }
}

header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}
