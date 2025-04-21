<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/config/db_connect.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid CSRF token';
    }

    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';

    if (!$email) {
        $errors[] = 'Valid email is required';
    }
    if (!$password) {
        $errors[] = 'Password is required';
    }

    if (empty($errors)) {
        $stmt = $conn->prepare('SELECT id, password_hash, role, name FROM users WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows === 1) {
            $stmt->bind_result($id, $hash, $role, $name);
            $stmt->fetch();
            if (password_verify($password, $hash)) {
                $_SESSION['user_id'] = $id;
                $_SESSION['role'] = $role;
                $_SESSION['name'] = $name;
                // Remember me cookie
                if (!empty($_POST['remember_me'])) {
                    setcookie('remember_user', $id, time() + 7*24*3600, "/", "", false, true);
                }
                header('Location: dashboard.php');
                exit;
            } else {
                $errors[] = 'Incorrect password';
            }
        } else {
            $errors[] = 'User not found';
        }
    }
}
?>
<h2>Login</h2>
<?php if (isset($_GET['registered'])): ?>
  <div class="alert alert-success">Registration successful! Please log in.</div>
<?php endif; ?>
<?php if ($errors): ?>
  <div class="alert alert-danger">
    <ul>
      <?php foreach ($errors as $error): ?>
        <li><?= htmlspecialchars($error) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>
<form method="post" action="login.php">
  <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
  <div class="mb-3">
    <label for="email" class="form-label">Email</label>
    <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($email ?? '') ?>" required>
  </div>
  <div class="mb-3">
    <label for="password" class="form-label">Password</label>
    <input type="password" class="form-control" id="password" name="password" required>
  </div>
  <div class="mb-3 form-check">
    <input type="checkbox" class="form-check-input" id="remember_me" name="remember_me" value="1"
      <?= !empty($_POST['remember_me']) ? 'checked' : '' ?>>
    <label class="form-check-label" for="remember_me">Remember Me</label>
  </div>
  <button type="submit" class="btn btn-primary">Login</button>
</form>
<?php require_once __DIR__ . '/includes/footer.php'; ?>