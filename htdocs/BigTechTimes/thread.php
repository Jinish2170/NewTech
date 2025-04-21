<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/config/db_connect.php';
ensure_logged_in();

$thread_id = $_GET['id'] ?? null;
if ($thread_id) {
    // Fetch thread and posts
    $stmt = $conn->prepare("SELECT t.title, t.body, u.name, t.created_at FROM threads t JOIN users u ON t.user_id=u.id WHERE t.id=?");
    $stmt->bind_param('i', $thread_id);
    $stmt->execute();
    $thread = $stmt->get_result()->fetch_assoc();

    $stmt = $conn->prepare("SELECT p.body, u.name, p.created_at FROM posts p JOIN users u ON p.user_id=u.id WHERE p.thread_id=? ORDER BY p.created_at");
    $stmt->bind_param('i', $thread_id);
    $stmt->execute();
    $posts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Handle new thread or reply form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && validate_csrf_token($_POST['csrf_token'] ?? '')) {
    if (!$thread_id) {
        // Create new thread
        $title = trim($_POST['title']);
        $body = trim($_POST['body']);
        $user_id = current_user_id();
        $stmt = $conn->prepare("INSERT INTO threads (user_id, title, body) VALUES (?, ?, ?)");
        $stmt->bind_param('iss', $user_id, $title, $body);
        if ($stmt->execute()) {
            $new_id = $stmt->insert_id;
            header('Location: thread.php?id=' . $new_id);
            exit;
        }
    } else {
        // Post reply
        $body = trim($_POST['body']);
        $user_id = current_user_id();
        $stmt = $conn->prepare("INSERT INTO posts (thread_id, user_id, body) VALUES (?, ?, ?)");
        $stmt->bind_param('iis', $thread_id, $user_id, $body);
        if ($stmt->execute()) {
            header('Location: thread.php?id=' . $thread_id);
            exit;
        }
    }
}
?>
<?php if (!$thread_id): ?>
<h2>Create New Thread</h2>
<form method="post" action="thread.php">
  <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
  <div class="mb-3">
    <label class="form-label">Title</label>
    <input type="text" class="form-control" name="title" required>
  </div>
  <div class="mb-3">
    <label class="form-label">Body</label>
    <textarea class="form-control" name="body" rows="5" required></textarea>
  </div>
  <button type="submit" class="btn btn-primary">Post Thread</button>
</form>
<?php else: ?>
<h2><?= htmlspecialchars($thread['title']) ?></h2>
<p><?= nl2br(htmlspecialchars($thread['body'])) ?></p>
<small>by <?= htmlspecialchars($thread['name']) ?> on <?= $thread['created_at'] ?></small>
<hr>
<h4>Replies</h4>
<?php foreach ($posts as $post): ?>
  <div class="card mb-2">
    <div class="card-body">
      <p><?= nl2br(htmlspecialchars($post['body'])) ?></p>
      <small>by <?= htmlspecialchars($post['name']) ?> on <?= $post['created_at'] ?></small>
    </div>
  </div>
<?php endforeach; ?>
<h4>Reply</h4>
<form method="post" action="thread.php?id=<?= $thread_id ?>">
  <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
  <div class="mb-3">
    <textarea class="form-control" name="body" rows="3" required></textarea>
  </div>
  <button type="submit" class="btn btn-primary">Post Reply</button>
</form>
<?php endif; ?>
<?php require_once __DIR__ . '/includes/footer.php'; ?>