<?php
// Lightweight token helpers for the public web UI.
// Provides encrypt/decrypt helpers and a cookie setter helper.

// NOTE: This file is intentionally simple for the web front-end. It does NOT
// implement production-grade encryption. If you use encrypted tokens in the
// backend, update these helpers to match the backend encryption scheme.

/**
 * If $payload is an array, returns a reversible string representation.
 * If $payload is already a string (e.g. a JWT), return as-is.
 */
function encryptToken($payload, $key = '') {
    if (is_string($payload)) return $payload;
    return base64_encode(json_encode($payload));
}

/**
 * Attempts to decode a token previously created with encryptToken or a JWT-like string.
 * Returns associative array on success or null on failure.
 */
function decryptToken($token, $key = '') {
    if (!$token) return null;

    // If it looks like a JWT (contains two dots), parse the middle part
    if (substr_count($token, '.') === 2) {
        $parts = explode('.', $token);
        $payload = $parts[1];
        // Add padding and decode
        $remainder = strlen($payload) % 4;
        if ($remainder) $payload .= str_repeat('=', 4 - $remainder);
        $decoded = base64_decode(str_replace(['-', '_'], ['+', '/'], $payload));
        $json = json_decode($decoded, true);
        return is_array($json) ? $json : null;
    }

    // Try base64 decode then json decode (our encryptToken uses this)
    $decoded = base64_decode($token, true);
    if ($decoded !== false) {
        $json = json_decode($decoded, true);
        if (is_array($json)) return $json;
    }

    // Try plain json
    $json = json_decode($token, true);
    if (is_array($json)) return $json;

    // Nothing matched; return null but keep original token string for callers that want it
    return null;
}

/**
 * Helper to set the token cookie on this origin.
 * Use this from a small POST endpoint that receives JSON { token: "..." }.
 */
function set_token_cookie_from_value($tokenValue) {
    // Cookie lifetime: 30 days
    $expires = time() + (86400 * 30);
    // Use path=/ so cookie is available across the site. Not setting Secure/HttpOnly here
    // allows the site to read it server-side via $_COOKIE. If you need HttpOnly, set it
    // and adjust client behavior (JS can't read HttpOnly cookies).
    setcookie('token', $tokenValue, $expires, "/");
}


/**
 * Return the decoded token payload if present, otherwise null.
 */
function getDecodedTokenPayload() {
    if (!isset($_COOKIE['token'])) return null;
    global $master_key;
    $payload = decryptToken($_COOKIE['token'], $master_key ?? '');
    return $payload ?: null;
}

/**
 * Returns true if a valid token cookie is present.
 */
function isAuthenticated() {
    return getDecodedTokenPayload() !== null;
}

/**
 * If user is already logged in, redirect them away from auth pages.
 * Redirects to admin or employee dashboard depending on role_type.
 */
function restrictCheckLoggedIn() {
    $payload = getDecodedTokenPayload();
    if (!$payload) return;
    $role = $payload['role_type'] ?? ($payload['role'] ?? null);
    if ($role === 'admin') {
        header('Location: /public/admin/index.php');
        exit();
    }
    if ($role === 'employee') {
        header('Location: /public/employee/index.php');
        exit();
    }
    // If authenticated but role unknown, redirect to admin dashboard by default
    // (redirecting to /public/index.php causes a loop because index.php redirects to login)
    header('Location: /public/admin');
    exit();
}


// If this file is requested directly (not included), expose small HTTP actions
// so we can set/clear the token cookie without separate wrapper files.
if (php_sapi_name() !== 'cli' && realpath(__FILE__) === realpath($_SERVER['SCRIPT_FILENAME'])) {
    header('Content-Type: application/json');

    // Determine action via query param or JSON body { action: 'set_token' }
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
        echo json_encode(['status' => 'success', 'message' => 'Token stored']);
        exit;
    }

    if ($action === 'clear_token') {
        setcookie('token', '', time() - 3600, '/');
        echo json_encode(['status' => 'success', 'message' => 'Local token cleared']);
        exit;
    }

    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Unknown action']);
    exit;
}

?>
