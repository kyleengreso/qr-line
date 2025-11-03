<?php
// Server-side logout handler. Called by browser when user clicks Logout.
// This script will:
// 1. If a token cookie exists locally, make a server-side POST to the Flask API
//    logout endpoint and include the token as a Cookie header so Flask can clear its session.
// 2. Clear the local PHP-origin token cookie.
// 3. Redirect the browser to the login page.

include_once __DIR__ . '/../base.php';

// Allow only GET/POST for convenience
if ($_SERVER['REQUEST_METHOD'] !== 'GET' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo 'Method Not Allowed';
    exit;
}

// Attempt to call Flask logout server-side to invalidate token on the API side.
$token = $_COOKIE['token'] ?? null;
if ($token) {
    $endpoint = isset($endpoint_server) ? $endpoint_server : null;
    $endpoint = $endpoint ? rtrim($endpoint, '/') : null;
    $apiUrl = $endpoint ? ($endpoint . '/api/logout') : null;
    // Use cURL to POST and include the token as a Cookie header so Flask sees it.
    if ($apiUrl && function_exists('curl_init')) {
        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Cookie: token=' . $token
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(new stdClass()));
        // short timeout
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($ch, CURLOPT_TIMEOUT, 4);
        // execute and ignore response
        $resp = curl_exec($ch);
        curl_close($ch);
    } elseif ($apiUrl) {
        // fallback using file_get_contents
        $opts = [
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\n" . "Cookie: token={$token}\r\n",
                'content' => json_encode(new stdClass()),
                'timeout' => 4
            ]
        ];
        $context = stream_context_create($opts);
        @file_get_contents($apiUrl, false, $context);
    }
}

// Clear PHP-origin token cookie
setcookie('token', '', time() - 3600, '/');

// Redirect to login page
header('Location: /public/auth/login.php');
exit;

?>
