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
<div class="row justify-content-center">
  <div class="col-lg-10">
    <?php if (is_admin()): ?>
    <div class="card shadow-sm mb-4 animate__animated animate__fadeInUp">
      <div class="card-header bg-success text-white d-flex align-items-center">
        <i class="fa fa-calendar-plus me-2"></i>
        <h5 class="mb-0">Create Event</h5>
      </div>
      <div class="card-body">
        <!-- Create Event Form -->
        <form method="post" class="row g-3" action="events.php">
          <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
          <input type="hidden" name="action" value="create_event">
          <div class="col-md-6">
            <label class="form-label">Title</label>
            <input type="text" class="form-control" name="title" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Date & Time</label>
            <input type="datetime-local" class="form-control" name="event_date" required>
          </div>
          <div class="col-md-12">
            <label class="form-label">Location</label>
            <input type="text" class="form-control" name="location">
          </div>
          <div class="col-md-12">
            <label class="form-label">Description</label>
            <textarea class="form-control" name="description" rows="3"></textarea>
          </div>
          <div class="col-md-12 text-end">
            <button type="submit" class="btn btn-success animate__animated animate__pulse animate__infinite">Add Event</button>
          </div>
        </form>
      </div>
    </div>
    <?php endif; ?>

    <div class="card shadow-sm mb-4 animate__animated animate__fadeInUp animate__delay-1s">
      <div class="card-header bg-light">
        <i class="fa fa-calendar-alt me-2"></i>
        <h5 class="mb-0">Events Calendar</h5>
      </div>
      <div class="card-body">
        <div id="calendar"></div>
      </div>
    </div>

    <div class="card shadow-sm mb-4 animate__animated animate__fadeInUp animate__delay-2s">
      <div class="card-header bg-warning text-dark">
        <i class="fa fa-users me-2"></i>
        <h5 class="mb-0">Upcoming Events & RSVP</h5>
      </div>
      <ul class="list-group list-group-flush">
        <?php
        $stmt = $conn->prepare("SELECT e.id, e.title, e.description, e.event_date, e.location, COUNT(r.user_id) AS attendees FROM events e LEFT JOIN rsvps r ON e.id=r.event_id WHERE e.event_date >= NOW() GROUP BY e.id ORDER BY e.event_date ASC");
        $stmt->execute();
        $events = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        foreach ($events as $ev): ?>
        <li class="list-group-item d-flex justify-content-between align-items-start">
          <div>
            <h6 class="mb-1"><?= htmlspecialchars($ev['title']) ?></h6>
            <small class="text-muted"><?= $ev['event_date'] ?> at <?= htmlspecialchars($ev['location']) ?></small>
          </div>
          <div>
            <span class="badge bg-secondary me-2">Attendees: <?= $ev['attendees'] ?></span>
            <form method="post" action="events.php" class="d-inline">
              <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
              <input type="hidden" name="action" value="rsvp">
              <input type="hidden" name="event_id" value="<?= $ev['id'] ?>">
              <button type="submit" class="btn btn-sm btn-outline-primary animate__animated animate__heartBeat animate__infinite">RSVP</button>
            </form>
          </div>
        </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
</div>
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