<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/config/db_connect.php';
ensure_logged_in();

$q = trim($_GET['q'] ?? '');
if ($q !== '') {
    $search = "%{$q}%";
    $stmt = $conn->prepare(
        "SELECT t.id, t.title, u.name, t.created_at, t.like_count
         FROM threads t
         JOIN users u ON t.user_id = u.id
         WHERE t.title LIKE ? OR t.body LIKE ?
         ORDER BY t.created_at DESC"
    );
    $stmt->bind_param('ss', $search, $search);
} else {
    $stmt = $conn->prepare(
        "SELECT t.id, t.title, u.name, t.created_at, t.like_count
         FROM threads t
         JOIN users u ON t.user_id = u.id
         ORDER BY t.created_at DESC"
    );
}
$stmt->execute();
$threads = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<div class="row">
  <div class="col-lg-12">
    <div class="card shadow-sm mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0"><i class="fa fa-comments me-2"></i>Discussion Forum</h4>
        <a href="thread.php" class="btn btn-primary btn-sm">New Thread</a>
      </div>
      <div class="card-body">
        <form class="d-flex mb-3" method="get" action="forum.php">
          <input class="form-control me-2" type="search" name="q" placeholder="Search threads..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
          <button class="btn btn-outline-secondary" type="submit"><i class="fa fa-search"></i></button>
        </form>
        <?php if (empty($threads)): ?>
          <p class="text-muted">No threads found.</p>
        <?php else: ?>
          <?php foreach ($threads as $thread): ?>
            <div class="card mb-2">
              <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                  <a href="thread.php?id=<?= $thread['id'] ?>" class="h6 mb-1 d-block"><?= htmlspecialchars($thread['title']) ?></a>
                  <small class="text-muted">by <?= htmlspecialchars($thread['name']) ?> on <?= $thread['created_at'] ?></small>
                </div>
                <div class="text-end">
                  <button class="btn btn-outline-primary btn-sm like-btn me-1" data-thread-id="<?= $thread['id'] ?>">
                    <i class="fa fa-thumbs-up"></i>
                  </button>
                  <span class="badge bg-secondary like-count"><?= $thread['like_count'] ?></span>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
