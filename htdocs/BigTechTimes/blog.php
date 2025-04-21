<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/config/db_connect.php';
ensure_logged_in();
// Fetch blog posts
$stmt = $conn->prepare("SELECT b.id, b.title, b.created_at, u.name FROM blogs b JOIN users u ON b.author_id=u.id ORDER BY b.created_at DESC");
$stmt->execute();
$blogs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<h2>Blog</h2>
<?php if (is_admin()): ?>
  <a href="post.php" class="btn btn-primary mb-3">Create New Post</a>
<?php endif; ?>
<ul class="list-group">
<?php foreach ($blogs as $blog): ?>
  <li class="list-group-item">
    <a href="post.php?id=<?= $blog['id'] ?>"><?= htmlspecialchars($blog['title']) ?></a>
    <br><small>by <?= htmlspecialchars($blog['name']) ?> on <?= $blog['created_at'] ?></small>
  </li>
<?php endforeach; ?>
</ul>
<?php require_once __DIR__ . '/includes/footer.php'; ?>