<?php
header('Content-Type: application/json');
include_once __DIR__ . '/../includes/system_auth.php';

// Clear the token cookie on this origin
setcookie('token', '', time() - 3600, '/');

echo json_encode(['status' => 'success', 'message' => 'Local token cleared']);
exit;

?>
