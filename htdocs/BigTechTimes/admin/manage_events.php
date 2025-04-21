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
    $eid = intval($_POST['event_id']);
    if ($_POST['action'] === 'update_event') {
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $event_date = $_POST['event_date'];
        $location = trim($_POST['location']);
        $stmt = $conn->prepare("UPDATE events SET title = ?, description = ?, event_date = ?, location = ? WHERE id = ?");
        $stmt->bind_param('ssssi', $title, $description, $event_date, $location, $eid);
        $stmt->execute();
    } elseif ($_POST['action'] === 'delete_event') {
        $stmt = $conn->prepare("DELETE FROM events WHERE id = ?");
        $stmt->bind_param('i', $eid);
        $stmt->execute();
    }
    header('Location: manage_events.php');
    exit;
}

// Fetch events
$stmt = $conn->prepare("SELECT id, title, description, event_date, location FROM events ORDER BY event_date DESC");
$stmt->execute();
events = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<h2>Manage Events</h2>
<table class="table table-bordered">
  <thead><tr><th>ID</th><th>Title</th><th>Description</th><th>Date & Time</th><th>Location</th><th>Actions</th></tr></thead>
  <tbody>
  <?php foreach ($events as $ev): ?>
  <tr>
    <form method="post" class="row gx-2 gy-1 align-items-center">
      <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
      <input type="hidden" name="event_id" value="<?= $ev['id'] ?>">
      <td><?= $ev['id'] ?></td>
      <td><input type="text" name="title" class="form-control form-control-sm" value="<?= htmlspecialchars($ev['title']) ?>"></td>
      <td><textarea name="description" class="form-control form-control-sm" rows="2"><?= htmlspecialchars($ev['description']) ?></textarea></td>
      <td><input type="datetime-local" name="event_date" class="form-control form-control-sm" value="<?= date('Y-m-d\TH:i', strtotime($ev['event_date'])) ?>"></td>
      <td><input type="text" name="location" class="form-control form-control-sm" value="<?= htmlspecialchars($ev['location']) ?>"></td>
      <td>
        <button type="submit" name="action" value="update_event" class="btn btn-sm btn-success">Update</button>
        <button type="submit" name="action" value="delete_event" class="btn btn-sm btn-danger" onclick="return confirm('Delete this event?');">Delete</button>
      </td>
    </form>
  </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php require_once __DIR__ . '/../includes/footer.php';?>