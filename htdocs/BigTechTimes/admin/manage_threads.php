<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db_connect.php';
require_once __DIR__ . '/../includes/header.php';
ensure_logged_in();
ensure_admin();

// Handle actions
if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && validate_csrf_token($_POST['csrf_token'] ?? '')
    && isset($_POST['action'])
) {
    if ($_POST['action'] === 'update_thread') {
        $tid = intval($_POST['thread_id']);
        $title = trim($_POST['title']);
        $body = trim($_POST['body']);
        $stmt = $conn->prepare("UPDATE threads SET title = ?, body = ? WHERE id = ?");
        $stmt->bind_param('ssi', $title, $body, $tid);
        $stmt->execute();
    } elseif ($_POST['action'] === 'delete_thread') {
        $tid = intval($_POST['thread_id']);
        $stmt = $conn->prepare("DELETE FROM threads WHERE id = ?");
        $stmt->bind_param('i', $tid);
        $stmt->execute();
    }
    header('Location: manage_threads.php');
    exit;
}

// Fetch threads
$result = $conn->prepare("SELECT t.id, t.title, t.body, u.name, t.created_at FROM threads t JOIN users u ON t.user_id = u.id ORDER BY t.created_at DESC");
$result->execute();
$threads = $result->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<h2>Manage Threads</h2>
<table class="table table-bordered">
  <thead><tr><th>ID</th><th>Title</th><th>Body</th><th>Author</th><th>Created</th><th>Actions</th></tr></thead>
  <tbody>
    <?php foreach ($threads as $thread): ?>
    <tr>
      <form method="post" class="row gx-2 gy-1 align-items-center">
        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
        <input type="hidden" name="thread_id" value="<?= $thread['id'] ?>">
        <td><?= $thread['id'] ?></td>
        <td>
          <input type="text" name="title" class="form-control form-control-sm" value="<?= htmlspecialchars($thread['title']) ?>">
        </td>
        <td>
          <textarea name="body" class="form-control form-control-sm" rows="2"><?= htmlspecialchars($thread['body']) ?></textarea>
        </td>
        <td><?= htmlspecialchars($thread['name']) ?></td>
        <td><?= $thread['created_at'] ?></td>
        <td>
          <button type="submit" name="action" value="update_thread" class="btn btn-sm btn-success">Update</button>
          <button type="submit" name="action" value="delete_thread" class="btn btn-sm btn-danger" onclick="return confirm('Delete this thread?');">Delete</button>
        </td>
      </form>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?php require_once __DIR__ . '/../includes/footer.php';?>