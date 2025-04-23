<?php
require_once __DIR__ . '/auth.php';
$current = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>BigTechTimes Community Portal</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="css/styles.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <meta name="csrf-token" content="<?= generate_csrf_token() ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto+Mono:wght@400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <div class="container-fluid">
    <a class="navbar-brand" href="dashboard.php">BigTechTimes</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link <?= $current=='dashboard.php'?'active':'' ?>" href="dashboard.php">Home</a></li>
        <li class="nav-item"><a class="nav-link <?= $current=='forum.php'?'active':'' ?>" href="forum.php">Forum</a></li>
        <li class="nav-item"><a class="nav-link <?= $current=='blog.php'?'active':'' ?>" href="blog.php">Blog</a></li>
        <li class="nav-item"><a class="nav-link <?= $current=='resources.php'?'active':'' ?>" href="resources.php">Resources</a></li>
        <li class="nav-item"><a class="nav-link <?= $current=='events.php'?'active':'' ?>" href="events.php">Events</a></li>
        <li class="nav-item"><a class="nav-link <?= $current=='chat.php'?'active':'' ?>" href="chat.php">Chat</a></li>
      </ul>
      <form class="d-flex me-3" method="get" action="forum.php">
        <input class="form-control me-2" type="search" name="q" placeholder="Search Threads" aria-label="Search" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
        <button class="btn btn-outline-success" type="submit">Search</button>
      </form>
      <ul class="navbar-nav">
        <?php if (is_logged_in()): ?>
          <li class="nav-item"><a class="nav-link" href="profile.php">Profile</a></li>
          <?php if (is_admin()): ?>
            <li class="nav-item"><a class="nav-link" href="admin/index.php">Admin</a></li>
          <?php endif; ?>
          <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
          <li class="nav-item"><a class="nav-link" href="register.php">Register</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
<div class="container mt-4 animate__animated animate__fadeIn">
