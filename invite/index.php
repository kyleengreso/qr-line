<?php
$target = realpath(__DIR__ . "/../public/requester/requester_invite.php");

if ($target && strpos($target, realpath(__DIR__ . "/../public")) === 0) {
    header("Location: /public/requester/requester_invite.php");
    exit();
} else {
    http_response_code(400);
    echo "Invalid redirect.";
    exit();
}
?>