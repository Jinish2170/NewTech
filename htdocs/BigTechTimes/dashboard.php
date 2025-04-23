<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/config/db_connect.php';
ensure_logged_in();

// Fetch latest threads
$stmt = $conn->prepare("SELECT t.id, t.title, t.created_at, u.name FROM threads t JOIN users u ON t.user_id = u.id ORDER BY t.created_at DESC LIMIT 5");
$stmt->execute();
$threads = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch latest blogs
$stmt = $conn->prepare("SELECT b.id, b.title, b.created_at, u.name FROM blogs b JOIN users u ON b.author_id = u.id ORDER BY b.created_at DESC LIMIT 5");
$stmt->execute();
$blogs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch upcoming events
$stmt = $conn->prepare("SELECT id, title, event_date FROM events WHERE event_date >= NOW() ORDER BY event_date ASC LIMIT 5");
$stmt->execute();
$events = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<h2 class="mb-4"><i class="fa fa-tachometer-alt me-2"></i>Dashboard</h2>
<div class="row g-4">
  <div class="col-md-4">
    <div class="card h-100 shadow-sm">
      <div class="card-header bg-primary text-white">
        <i class="fa fa-comments me-2"></i>Recent Threads
      </div>
      <ul class="list-group list-group-flush">
        <?php foreach ($threads as $thread): ?>
          <li class="list-group-item">
            <a href="thread.php?id=<?= $thread['id'] ?>"><?= htmlspecialchars($thread['title']) ?></a>
            <div class="small text-muted">by <?= htmlspecialchars($thread['name']) ?> on <?= $thread['created_at'] ?></div>
          </li>
        <?php endforeach; ?>
      </ul>
      <div class="card-footer text-end">
        <a href="forum.php" class="btn btn-sm btn-outline-light">View All</a>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card h-100 shadow-sm">
      <div class="card-header bg-success text-white">
        <i class="fa fa-newspaper me-2"></i>Latest Blogs
      </div>
      <ul class="list-group list-group-flush">
        <?php foreach ($blogs as $blog): ?>
          <li class="list-group-item">
            <a href="post.php?id=<?= $blog['id'] ?>"><?= htmlspecialchars($blog['title']) ?></a>
            <div class="small text-muted">by <?= htmlspecialchars($blog['name']) ?> on <?= $blog['created_at'] ?></div>
          </li>
        <?php endforeach; ?>
      </ul>
      <div class="card-footer text-end">
        <a href="blog.php" class="btn btn-sm btn-outline-light">View All</a>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card h-100 shadow-sm">
      <div class="card-header bg-warning text-dark">
        <i class="fa fa-calendar-alt me-2"></i>Upcoming Events
      </div>
      <ul class="list-group list-group-flush">
        <?php foreach ($events as $event): ?>
          <li class="list-group-item">
            <?= htmlspecialchars($event['title']) ?>
            <div class="small text-muted"><?= $event['event_date'] ?></div>
          </li>
        <?php endforeach; ?>
      </ul>
      <div class="card-footer text-end">
        <a href="events.php" class="btn btn-sm btn-outline-dark">View Calendar</a>
      </div>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php';?>