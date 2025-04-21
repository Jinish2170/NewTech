<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db_connect.php';
require_once __DIR__ . '/../includes/header.php';
ensure_logged_in();
ensure_admin();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && validate_csrf_token($_POST['csrf_token'] ?? '')) {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'update_role') {
            $uid = intval($_POST['user_id']);
            $role = $_POST['role'];
            $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
            $stmt->bind_param('si', $role, $uid);
            $stmt->execute();
        } elseif ($_POST['action'] === 'delete_user') {
            $uid = intval($_POST['user_id']);
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param('i', $uid);
            $stmt->execute();
        }
    }
    header('Location: manage_users.php');
    exit;
}

// Fetch users
$result = $conn->query("SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC");
?>
<h2>Manage Users</h2>
<table class="table table-striped">
  <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Joined</th><th>Actions</th></tr></thead>
  <tbody>
    <?php while ($user = $result->fetch_assoc()): ?>
      <tr>
        <td><?= $user['id'] ?></td>
        <td><?= htmlspecialchars($user['name']) ?></td>
        <td><?= htmlspecialchars($user['email']) ?></td>
        <td>
          <form method="post" class="d-inline">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
            <input type="hidden" name="action" value="update_role">
            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
            <select name="role" class="form-select form-select-sm d-inline w-auto" onchange="this.form.submit()">
              <?php foreach (['student','pro','admin'] as $r): ?>
                <option value="<?= $r ?>" <?= $user['role'] === $r ? 'selected' : '' ?>><?= ucfirst($r) ?></option>
              <?php endforeach; ?>
            </select>
          </form>
        </td>
        <td><?= $user['created_at'] ?></td>
        <td>
          <?php if ($user['id'] != current_user_id()): ?>
            <form method="post" class="d-inline" onsubmit="return confirm('Delete this user?');">
              <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
              <input type="hidden" name="action" value="delete_user">
              <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
              <button class="btn btn-sm btn-danger">Delete</button>
            </form>
          <?php endif; ?>
        </td>
      </tr>
    <?php endwhile; ?>
  </tbody>
</table>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>