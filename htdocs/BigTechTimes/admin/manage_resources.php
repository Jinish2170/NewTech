<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db_connect.php';
require_once __DIR__ . '/../includes/header.php';
en sure_logged_in();
en sure_admin();

// Handle actions
if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && validate_csrf_token($_POST['csrf_token'] ?? '')
    && isset($_POST['action'])
) {
    $rid = intval($_POST['resource_id']);
    if ($_POST['action'] === 'update_resource') {
        $title = trim($_POST['title']);
        $stmt = $conn->prepare("UPDATE resources SET title = ? WHERE id = ?");
        $stmt->bind_param('si', $title, $rid);
        $stmt->execute();
    } elseif ($_POST['action'] === 'delete_resource') {
        // fetch file path
        $stmt = $conn->prepare("SELECT file_path FROM resources WHERE id = ?");
        $stmt->bind_param('i', $rid);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        if ($row) {
            @unlink(__DIR__ . '/../' . $row['file_path']);
        }
        $stmt = $conn->prepare("DELETE FROM resources WHERE id = ?");
        $stmt->bind_param('i', $rid);
        $stmt->execute();
    }
    header('Location: manage_resources.php');
    exit;
}

// Fetch resources
$stmt = $conn->prepare("SELECT r.id, r.title, r.file_path, u.name, r.created_at FROM resources r JOIN users u ON r.uploader_id = u.id ORDER BY r.created_at DESC");
$stmt->execute();
$resources = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<h2>Manage Resources</h2>
<table class="table table-bordered">
  <thead><tr><th>ID</th><th>Title</th><th>File</th><th>Uploader</th><th>Uploaded</th><th>Actions</th></tr></thead>
  <tbody>
    <?php foreach ($resources as $res): ?>
    <tr>
      <form method="post" class="row gx-2 gy-1 align-items-center">
        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
        <input type="hidden" name="resource_id" value="<?= $res['id'] ?>">
        <td><?= $res['id'] ?></td>
        <td><input type="text" name="title" class="form-control form-control-sm" value="<?= htmlspecialchars($res['title']) ?>"></td>
        <td><a href="../<?= htmlspecialchars($res['file_path']) ?>" download><?= basename($res['file_path']) ?></a></td>
        <td><?= htmlspecialchars($res['name']) ?></td>
        <td><?= $res['created_at'] ?></td>
        <td>
          <button type="submit" name="action" value="update_resource" class="btn btn-sm btn-success">Update</button>
          <button type="submit" name="action" value="delete_resource" class="btn btn-sm btn-danger" onclick="return confirm('Delete this resource?');">Delete</button>
        </td>
      </form>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?php require_once __DIR__ . '/../includes/footer.php';?>