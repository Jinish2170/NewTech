<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/config/db_connect.php';
ensure_logged_in();
$blog_id = $_GET['id'] ?? null;
if ($blog_id) {
    // Fetch blog post and comments
    $stmt = $conn->prepare("SELECT b.title, b.body, u.name, b.created_at FROM blogs b JOIN users u ON b.author_id=u.id WHERE b.id=?");
    $stmt->bind_param('i', $blog_id);
    $stmt->execute();
    $post = $stmt->get_result()->fetch_assoc();

    $stmt = $conn->prepare("SELECT c.body, u.name, c.created_at FROM comments c JOIN users u ON c.user_id=u.id WHERE c.blog_id=? ORDER BY c.created_at");
    $stmt->bind_param('i', $blog_id);
    $stmt->execute();
    $comments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Handle new post or comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && validate_csrf_token($_POST['csrf_token'] ?? '')) {
    if (!$blog_id && is_admin()) {
        // Create new blog post
        $title = trim($_POST['title']);
        $body = trim($_POST['body']);
        $author_id = current_user_id();
        $stmt = $conn->prepare("INSERT INTO blogs (author_id, title, body) VALUES (?, ?, ?)");
        $stmt->bind_param('iss', $author_id, $title, $body);
        if ($stmt->execute()) {
            header('Location: post.php?id=' . $stmt->insert_id);
            exit;
        }
    } elseif ($blog_id) {
        // Add comment
        $body = trim($_POST['body']);
        $user_id = current_user_id();
        $stmt = $conn->prepare("INSERT INTO comments (blog_id, user_id, body) VALUES (?, ?, ?)");
        $stmt->bind_param('iis', $blog_id, $user_id, $body);
        if ($stmt->execute()) {
            header('Location: post.php?id=' . $blog_id);
            exit;
        }
    }
}
?>
<?php if (!$blog_id && is_admin()): ?>
<h2>Create New Blog Post</h2>
<form method="post" action="post.php">
  <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
  <div class="mb-3">
    <label class="form-label">Title</label>
    <input type="text" class="form-control" name="title" required>
  </div>
  <div class="mb-3">
    <label class="form-label">Body</label>
    <textarea class="form-control" name="body" rows="6" required></textarea>
  </div>
  <button type="submit" class="btn btn-primary">Publish</button>
</form>
<?php elseif ($blog_id): ?>
<h2><?= htmlspecialchars($post['title']) ?></h2>
<p><?= nl2br(htmlspecialchars($post['body'])) ?></p>
<small>by <?= htmlspecialchars($post['name']) ?> on <?= $post['created_at'] ?></small>
<hr>
<h4>Comments</h4>
<?php foreach ($comments as $comment): ?>
  <div class="card mb-2"><div class="card-body">
    <p><?= nl2br(htmlspecialchars($comment['body'])) ?></p>
    <small>by <?= htmlspecialchars($comment['name']) ?> on <?= $comment['created_at'] ?></small>
  </div></div>
<?php endforeach; ?>
<h4>Add Comment</h4>
<form method="post" action="post.php?id=<?= $blog_id ?>">
  <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
  <div class="mb-3">
    <textarea class="form-control" name="body" rows="3" required></textarea>
  </div>
  <button type="submit" class="btn btn-primary">Submit Comment</button>
</form>
<?php endif; ?>
<?php require_once __DIR__ . '/includes/footer.php'; ?>