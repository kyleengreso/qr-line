<?php

include_once __DIR__ . "/../base.php";

function encryptToken(array $data, string $key = ''): string {
    if (!isset($key) || empty($key)) {
        $key = getenv('ENCRYPTION_KEY');
    }
    $iv = openssl_random_pseudo_bytes(16);
    $ciphertext = openssl_encrypt(json_encode($data), 'aes-256-cbc', $key, 0, $iv);
    return base64_encode($iv . $ciphertext);
}

function decryptToken(string $token, string $key): array {
    if (!isset($key) || empty($key)) {
        $key = getenv('ENCRYPTION_KEY');
    }
    $token = base64_decode($token);
    $iv = substr($token, 0, 16);
    $ciphertext = substr($token, 16);
    $data = openssl_decrypt($ciphertext, 'aes-256-cbc', $key, 0, $iv);
    return json_decode($data, true);
}

// $test = "sample";

// $e = encryptToken($test);
// echo $e . "\n";
// $d = decryptToken($e);
// echo $d . "\n"; 

