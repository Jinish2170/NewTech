<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/db_connect.php';
header('Content-Type: application/json');
// Validate request
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !is_logged_in() || !validate_csrf_token($_POST['csrf_token'] ?? '')) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request']);
    exit;
}
$thread_id = intval($_POST['thread_id']);
$user_id = current_user_id();
// Prevent duplicate like
$stmt = $conn->prepare("SELECT 1 FROM thread_likes WHERE user_id = ? AND thread_id = ?");
$stmt->bind_param('ii', $user_id, $thread_id);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows === 0) {
    $stmt = $conn->prepare("INSERT INTO thread_likes (user_id, thread_id) VALUES (?, ?)");
    $stmt->bind_param('ii', $user_id, $thread_id);
    $stmt->execute();
    $stmt = $conn->prepare("UPDATE threads SET like_count = like_count + 1 WHERE id = ?");
    $stmt->bind_param('i', $thread_id);
    $stmt->execute();
}
// Return new count
$stmt = $conn->prepare("SELECT like_count FROM threads WHERE id = ?");
$stmt->bind_param('i', $thread_id);
$stmt->execute();
$stmt->bind_result($count);
$stmt->fetch();
echo json_encode(['like_count' => $count]);
