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
<h2>Dashboard</h2>
<div class="row">
  <div class="col-md-4">
    <h4>Recent Threads</h4>
    <ul class="list-group">
      <?php foreach ($threads as $thread): ?>
        <li class="list-group-item">
          <a href="thread.php?id=<?= $thread['id'] ?>"><?= htmlspecialchars($thread['title']) ?></a>
          <br><small>by <?= htmlspecialchars($thread['name']) ?> on <?= $thread['created_at'] ?></small>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
  <div class="col-md-4">
    <h4>Latest Blogs</h4>
    <ul class="list-group">
      <?php foreach ($blogs as $blog): ?>
        <li class="list-group-item">
          <a href="post.php?blog_id=<?= $blog['id'] ?>"><?= htmlspecialchars($blog['title']) ?></a>
          <br><small>by <?= htmlspecialchars($blog['name']) ?> on <?= $blog['created_at'] ?></small>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
  <div class="col-md-4">
    <h4>Upcoming Events</h4>
    <ul class="list-group">
      <?php foreach ($events as $event): ?>
        <li class="list-group-item">
          <?= htmlspecialchars($event['title']) ?>
          <br><small><?= $event['event_date'] ?></small>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php';?>