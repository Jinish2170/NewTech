<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/db_connect.php';
ensure_logged_in();
ensure_admin();

// Fetch counts
$tables = ['users','threads','blogs','resources','events'];
$counts = [];
foreach ($tables as $t) {
    $stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM {$t}");
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $counts[$t] = $res['cnt'];
}
?>
<h2>Admin Dashboard</h2>
<div class="row">
  <div class="col-md-3">
    <div class="card text-center mb-3"><div class="card-body">
      <h5 class="card-title">Users</h5>
      <p class="card-text"><?= $counts['users'] ?></p>
      <a href="manage_users.php" class="btn btn-primary">Manage Users</a>
    </div></div>
  </div>
  <div class="col-md-3">
    <div class="card text-center mb-3"><div class="card-body">
      <h5 class="card-title">Threads</h5>
      <p class="card-text"><?= $counts['threads'] ?></p>
      <a href="manage_threads.php" class="btn btn-primary">Manage Threads</a>
    </div></div>
  </div>
  <div class="col-md-3">
    <div class="card text-center mb-3"><div class="card-body">
      <h5 class="card-title">Blogs</h5>
      <p class="card-text"><?= $counts['blogs'] ?></p>
      <a href="manage_blogs.php" class="btn btn-primary">Manage Blogs</a>
    </div></div>
  </div>
  <div class="col-md-3">
    <div class="card text-center mb-3"><div class="card-body">
      <h5 class="card-title">Resources</h5>
      <p class="card-text"><?= $counts['resources'] ?></p>
      <a href="manage_resources.php" class="btn btn-primary">Manage Resources</a>
    </div></div>
  </div>
  <div class="col-md-3">
    <div class="card text-center mb-3"><div class="card-body">
      <h5 class="card-title">Events</h5>
      <p class="card-text"><?= $counts['events'] ?></p>
      <a href="manage_events.php" class="btn btn-primary">Manage Events</a>
    </div></div>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php';?>