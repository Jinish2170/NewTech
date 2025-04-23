<?php
require_once __DIR__ . '/config/db_connect.php';

$token = $_GET['token'] ?? '';
if ($token) {
    $stmt = $conn->prepare('SELECT id FROM users WHERE verify_token = ?');
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows === 1) {
        $stmt->bind_result($userId);
        $stmt->fetch();
        $stmt2 = $conn->prepare('UPDATE users SET is_verified = 1, verify_token = NULL WHERE id = ?');
        $stmt2->bind_param('i', $userId);
        $stmt2->execute();
        header('Location: login.php?verified=1');
        exit;
    }
}
// Invalid or missing token
header('Location: login.php');
exit;
?>