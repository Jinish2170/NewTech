<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/config/db_connect.php';
ensure_logged_in();
// Fetch blog posts
$stmt = $conn->prepare("SELECT b.id, b.title, b.created_at, u.name FROM blogs b JOIN users u ON b.author_id=u.id ORDER BY b.created_at DESC");
$stmt->execute();
$blogs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<div class="row justify-content-center">
  <div class="col-md-8">
    <div class="card shadow-sm mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0"><i class="fa fa-newspaper me-2"></i>Blog</h4>
        <?php if (is_admin()): ?>
          <a href="post.php" class="btn btn-success btn-sm">New Post</a>
        <?php endif; ?>
      </div>
      <div class="card-body">
        <?php if (empty($blogs)): ?>
          <p class="text-muted">No blog posts available.</p>
        <?php else: ?>
          <?php foreach ($blogs as $blog): ?>
            <div class="card mb-2">
              <div class="card-body">
                <h5 class="card-title"><a href="post.php?id=<?= $blog['id'] ?>"><?= htmlspecialchars($blog['title']) ?></a></h5>
                <p class="card-text"><small class="text-muted">by <?= htmlspecialchars($blog['name']) ?> on <?= $blog['created_at'] ?></small></p>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>