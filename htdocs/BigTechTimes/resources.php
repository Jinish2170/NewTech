<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/config/db_connect.php';
ensure_logged_in();

// Handle upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && validate_csrf_token($_POST['csrf_token'] ?? '')) {
    $title = trim($_POST['title']);
    if (isset($_FILES['resource_file']) && $_FILES['resource_file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['resource_file'];
        $allowed = ['application/pdf', 'text/plain', 'application/zip', 'text/x-php', 'application/json'];
        if (in_array($file['type'], $allowed) && $file['size'] <= 5 * 1024 * 1024) {
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $ext;
            $dest = 'uploads/' . $filename;
            if (move_uploaded_file($file['tmp_name'], __DIR__ . '/uploads/' . $filename)) {
                $user_id = current_user_id();
                $stmt = $conn->prepare("INSERT INTO resources (uploader_id, title, file_path) VALUES (?, ?, ?)");
                $stmt->bind_param('iss', $user_id, $title, $dest);
                $stmt->execute();
                header('Location: resources.php?uploaded=1');
                exit;
            }
        }
    }
}

// Fetch resources
$stmt = $conn->prepare("SELECT r.id, r.title, r.file_path, u.name, r.created_at FROM resources r JOIN users u ON r.uploader_id=u.id ORDER BY r.created_at DESC");
$stmt->execute();
$resources = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<div class="row justify-content-center">
  <div class="col-md-8">
    <div class="card shadow-sm mb-4">
      <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
        <h4 class="mb-0"><i class="fa fa-folder-open me-2"></i>Resource Library</h4>
      </div>
      <div class="card-body">
        <h5 class="mb-3">Upload New Resource</h5>
        <form method="post" action="resources.php" enctype="multipart/form-data">
          <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
          <div class="mb-3">
            <label class="form-label">Title</label>
            <input type="text" class="form-control" name="title" required>
          </div>
          <div class="mb-3">
            <label class="form-label">File</label>
            <input type="file" class="form-control" name="resource_file" required>
          </div>
          <button type="submit" class="btn btn-info">Upload</button>
        </form>
        <hr>
        <h5 class="mb-3">Available Resources</h5>
        <?php if (empty($resources)): ?>
          <p class="text-muted">No resources uploaded yet.</p>
        <?php else: ?>
          <div class="list-group">
            <?php foreach ($resources as $res): ?>
              <div class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                  <i class="fa fa-file-alt me-2"></i>
                  <strong><?= htmlspecialchars($res['title']) ?></strong>
                  <div class="small text-muted">by <?= htmlspecialchars($res['name']) ?> on <?= $res['created_at'] ?></div>
                </div>
                <a href="<?= htmlspecialchars($res['file_path']) ?>" class="btn btn-sm btn-outline-primary" download>
                  <i class="fa fa-download"></i> Download
                </a>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>