<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/config/db_connect.php';
ensure_logged_in();

// Fetch all threads (skeleton)
$stmt = $conn->prepare("SELECT t.id, t.title, u.name, t.created_at FROM threads t JOIN users u ON t.user_id = u.id ORDER BY t.created_at DESC");
$stmt->execute();
$threads = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<h2>Discussion Forum</h2>
<a href="thread.php" class="btn btn-primary mb-3">Create New Thread</a>
<ul class="list-group">
<?php foreach ($threads as $thread): ?>
  <li class="list-group-item">
    <a href="thread.php?id=<?= $thread['id'] ?>"><?= htmlspecialchars($thread['title']) ?></a>
    <br><small>by <?= htmlspecialchars($thread['name']) ?> on <?= $thread['created_at'] ?></small>
  </li>
<?php endforeach; ?>
</ul>
<?php require_once __DIR__ . '/includes/footer.php';
