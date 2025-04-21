<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/config/db_connect.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid CSRF token';
    }

    $name = trim($_POST['name'] ?? '');
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if (!$name) {
        $errors[] = 'Name is required';
    }
    if (!$email) {
        $errors[] = 'Valid email is required';
    }
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters';
    }
    if ($password !== $password_confirm) {
        $errors[] = 'Passwords do not match';
    }

    if (empty($errors)) {
        // Check if email exists
        $stmt = $conn->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = 'Email already registered';
        } else {
            $password_hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $conn->prepare('INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)');
            $stmt->bind_param('sss', $name, $email, $password_hash);
            if ($stmt->execute()) {
                header('Location: login.php?registered=1');
                exit;
            } else {
                $errors[] = 'Registration failed, please try again';
            }
        }
    }
}
?>
<h2>Register</h2>
<?php if ($errors): ?>
  <div class="alert alert-danger">
    <ul>
      <?php foreach ($errors as $error): ?>
        <li><?= htmlspecialchars($error) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>
<form method="post" action="register.php">
  <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
  <div class="mb-3">
    <label for="name" class="form-label">Name</label>
    <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($name ?? '') ?>" required>
  </div>
  <div class="mb-3">
    <label for="email" class="form-label">Email</label>
    <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($email ?? '') ?>" required>
  </div>
  <div class="mb-3">
    <label for="password" class="form-label">Password</label>
    <input type="password" class="form-control" id="password" name="password" required>
  </div>
  <div class="mb-3">
    <label for="password_confirm" class="form-label">Confirm Password</label>
    <input type="password" class="form-control" id="password_confirm" name="password_confirm" required>
  </div>
  <button type="submit" class="btn btn-primary">Register</button>
</form>
<?php require_once __DIR__ . '/includes/footer.php'; ?>