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
                // Generate verification token
                $userId = $stmt->insert_id;
                $token = bin2hex(random_bytes(16));
                $stmt2 = $conn->prepare('UPDATE users SET verify_token = ? WHERE id = ?');
                $stmt2->bind_param('si', $token, $userId);
                $stmt2->execute();
                // Send verification email
                $verifyLink = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . "/verify.php?token=$token";
                $subject = 'Verify your BigTechTimes account';
                $message = "Hi $name,\n\nPlease click the link below to verify your email address and activate your account:\n$verifyLink\n\nThanks!";
                $headers = 'From: no-reply@bigtechtimes.local' . "\r\n";
                mail($email, $subject, $message, $headers);
                // Redirect to login with message
                header('Location: login.php?verify=sent');
                exit;
            } else {
                $errors[] = 'Registration failed, please try again';
            }
        }
    }
}
?>
<div class="row justify-content-center">
  <div class="col-md-6">
    <div class="card shadow-sm">
      <div class="card-header bg-primary text-white">
        <h4 class="mb-0">Register</h4>
      </div>
      <div class="card-body">
        <?php if ($errors): ?>
          <div class="alert alert-danger">
            <ul class="mb-0">
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
            <input type="password" class="form-control" id="password" name="password" required minlength="8">
          </div>
          <div class="mb-3">
            <label for="password_confirm" class="form-label">Confirm Password</label>
            <input type="password" class="form-control" id="password_confirm" name="password_confirm" required minlength="8">
          </div>
          <button type="submit" class="btn btn-primary w-100">Create Account</button>
        </form>
      </div>
      <div class="card-footer text-center">
        Already have an account? <a href="login.php">Login here</a>
      </div>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>