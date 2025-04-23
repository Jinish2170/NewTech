<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/config/db_connect.php';

$errors = [];
$info = [];
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
        $stmt = $conn->prepare('SELECT id, password_hash, role, name, is_verified FROM users WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows === 1) {
            $stmt->bind_result($id, $hash, $role, $name, $isVerified);
            $stmt->fetch();
            if (password_verify($password, $hash)) {
                if (!$isVerified) {
                    $errors[] = 'Please verify your email address before logging in.';
                } else {
                    $_SESSION['user_id'] = $id;
                    $_SESSION['role'] = $role;
                    $_SESSION['name'] = $name;
                    // Remember me cookie
                    if (!empty($_POST['remember_me'])) {
                        setcookie('remember_user', $id, time() + 7*24*3600, "/", "", false, true);
                    }
                    header('Location: dashboard.php');
                    exit;
                }
            } else {
                $errors[] = 'Incorrect password';
            }
        } else {
            $errors[] = 'User not found';
        }
    }
}

if (isset($_GET['verify']) && $_GET['verify']=='sent') {
    $info[] = 'Verification email sent. Please check your inbox.';
}
if (isset($_GET['verified']) && $_GET['verified']=='1') {
    $info[] = 'Email verified! You can now log in.';
}
?>
<div class="row justify-content-center">
  <div class="col-md-5">
    <div class="card shadow-sm">
      <div class="card-header bg-primary text-white">
        <h4 class="mb-0">Login</h4>
      </div>
      <div class="card-body">
        <?php if (isset($_GET['registered'])): ?>
          <div class="alert alert-success">Registration successful! Please log in.</div>
        <?php endif; ?>
        <?php if ($errors): ?>
          <div class="alert alert-danger">
            <ul class="mb-0">
              <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>
        <?php if (!empty($info)): ?>
          <div class="alert alert-info">
            <ul class="mb-0">
              <?php foreach ($info as $msg): ?>
                <li><?= htmlspecialchars($msg) ?></li>
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
            <input type="checkbox" class="form-check-input" id="remember_me" name="remember_me" value="1" <?= !empty($_POST['remember_me']) ? 'checked' : '' ?>>
            <label class="form-check-label" for="remember_me">Remember Me</label>
          </div>
          <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>
      </div>
      <div class="card-footer text-center">
        Don't have an account? <a href="register.php">Register here</a>
      </div>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>