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
    if ($_POST['action'] === 'update_blog') {
        $bid = intval($_POST['blog_id']);
        $title = trim($_POST['title']);
        $body = trim($_POST['body']);
        $stmt = $conn->prepare("UPDATE blogs SET title = ?, body = ? WHERE id = ?");
        $stmt->bind_param('ssi', $title, $body, $bid);
        $stmt->execute();
    } elseif ($_POST['action'] === 'delete_blog') {
        $bid = intval($_POST['blog_id']);
        $stmt = $conn->prepare("DELETE FROM blogs WHERE id = ?");
        $stmt->bind_param('i', $bid);
        $stmt->execute();
    }
    header('Location: manage_blogs.php');
    exit;
}

// Fetch blogs
$result = $conn->prepare("SELECT b.id, b.title, b.body, u.name, b.created_at FROM blogs b JOIN users u ON b.author_id = u.id ORDER BY b.created_at DESC");
$result->execute();
$blogs = $result->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<h2>Manage Blogs</h2>
<table class="table table-bordered">
  <thead><tr><th>ID</th><th>Title</th><th>Body</th><th>Author</th><th>Created</th><th>Actions</th></tr></thead>
  <tbody>
    <?php foreach ($blogs as $blog): ?>
    <tr>
      <form method="post" class="row gx-2 gy-1 align-items-center">
        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
        <input type="hidden" name="blog_id" value="<?= $blog['id'] ?>">
        <td><?= $blog['id'] ?></td>
        <td><input type="text" name="title" class="form-control form-control-sm" value="<?= htmlspecialchars($blog['title']) ?>"></td>
        <td><textarea name="body" class="form-control form-control-sm" rows="2"><?= htmlspecialchars($blog['body']) ?></textarea></td>
        <td><?= htmlspecialchars($blog['name']) ?></td>
        <td><?= $blog['created_at'] ?></td>
        <td>
          <button type="submit" name="action" value="update_blog" class="btn btn-sm btn-success">Update</button>
          <button type="submit" name="action" value="delete_blog" class="btn btn-sm btn-danger" onclick="return confirm('Delete this post?');">Delete</button>
        </td>
      </form>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?php require_once __DIR__ . '/../includes/footer.php';?>