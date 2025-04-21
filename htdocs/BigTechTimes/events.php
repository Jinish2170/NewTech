<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/config/db_connect.php';
ensure_logged_in();

// Handle event creation by admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action']==='create_event' && is_admin() && validate_csrf_token($_POST['csrf_token'] ?? '')) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $event_date = $_POST['event_date'];
    $location = trim($_POST['location']);
    $stmt = $conn->prepare("INSERT INTO events (title, description, event_date, location) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('ssss', $title, $description, $event_date, $location);
    $stmt->execute();
    header('Location: events.php');
    exit;
}
// Handle RSVP
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action']==='rsvp' && validate_csrf_token($_POST['csrf_token'] ?? '')) {
    $event_id = intval($_POST['event_id']);
    $user_id = current_user_id();
    $stmt = $conn->prepare("INSERT IGNORE INTO rsvps (user_id, event_id) VALUES (?, ?)");
    $stmt->bind_param('ii', $user_id, $event_id);
    $stmt->execute();
    header('Location: events.php');
    exit;
}

// Fetch events for calendar
$stmt = $conn->prepare("SELECT id, title, event_date AS start FROM events");
$stmt->execute();
$events = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$events_json = json_encode($events);
?>
<h2>Events Calendar</h2>
<?php if (is_admin()): ?>
<h4>Create Event</h4>
<form method="post" action="events.php">
  <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
  <input type="hidden" name="action" value="create_event">
  <div class="mb-3">
    <label class="form-label">Title</label>
    <input type="text" class="form-control" name="title" required>
  </div>
  <div class="mb-3">
    <label class="form-label">Description</label>
    <textarea class="form-control" name="description" rows="3"></textarea>
  </div>
  <div class="mb-3">
    <label class="form-label">Date & Time</label>
    <input type="datetime-local" class="form-control" name="event_date" required>
  </div>
  <div class="mb-3">
    <label class="form-label">Location</label>
    <input type="text" class="form-control" name="location">
  </div>
  <button type="submit" class="btn btn-primary">Add Event</button>
</form>
<?php endif; ?>
<div id="calendar"></div>
<hr>
<h4>Upcoming Events</h4>
<ul class="list-group">
<?php
$stmt = $conn->prepare("SELECT e.id, e.title, e.description, e.event_date, e.location, COUNT(r.user_id) AS attendees FROM events e LEFT JOIN rsvps r ON e.id=r.event_id WHERE e.event_date >= NOW() GROUP BY e.id ORDER BY e.event_date ASC");
$stmt->execute();
$events = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
foreach ($events as $ev): ?>
  <li class="list-group-item">
    <h5><?= htmlspecialchars($ev['title']) ?></h5>
    <p><?= htmlspecialchars($ev['description']) ?></p>
    <small><?= $ev['event_date'] ?> at <?= htmlspecialchars($ev['location']) ?></small>
    <p>Attendees: <?= $ev['attendees'] ?></p>
    <form method="post" action="events.php" class="d-inline">
      <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
      <input type="hidden" name="action" value="rsvp">
      <input type="hidden" name="event_id" value="<?= $ev['id'] ?>">
      <button type="submit" class="btn btn-sm btn-success">RSVP</button>
    </form>
  </li>
<?php endforeach; ?>
</ul>
<script src="js/fullcalendar/main.min.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
      initialView: 'dayGridMonth',
      events: <?= $events_json ?>
    });
    calendar.render();
  });
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>