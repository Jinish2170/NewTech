<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/config/db_connect.php';
ensure_logged_in();
$user_id = current_user_id();

// Fetch user and profile
$stmt = $conn->prepare("SELECT u.name, u.email, u.avatar, p.bio, p.interests, p.social_links FROM users u LEFT JOIN profiles p ON u.id = p.user_id WHERE u.id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$errors = [];
$success = isset($_GET['updated']);

// Handle update
if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && validate_csrf_token($_POST['csrf_token'] ?? '')
) {
    // Handle avatar upload
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Error uploading avatar.';
        } else {
            $file = $_FILES['avatar'];
            $allowed = ['image/jpeg','image/png','image/gif'];
            if (!in_array($file['type'], $allowed)) {
                $errors[] = 'Invalid avatar format. Allowed: JPG, PNG, GIF.';
            } elseif ($file['size'] > 2 * 1024 * 1024) {
                $errors[] = 'Avatar must be under 2MB.';
            } else {
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = uniqid('avatar_') . '.' . $ext;
                $dest = 'uploads/' . $filename;
                if (move_uploaded_file($file['tmp_name'], __DIR__ . '/uploads/' . $filename)) {
                    $stmt = $conn->prepare("UPDATE users SET avatar = ? WHERE id = ?");
                    $stmt->bind_param('si', $dest, $user_id);
                    $stmt->execute();
                    // update current user avatar path
                    $user['avatar'] = $dest;
                }
            }
        }
    }
    // Validate name
    if (empty(trim($_POST['name']))) {
        $errors[] = 'Name cannot be empty.';
    }
    $name = trim($_POST['name']);
    $bio = trim($_POST['bio']);
    $interests = trim($_POST['interests']);
    $social_links = trim($_POST['social_links']);
    // If no errors, proceed with saving
    if (empty($errors)) {
        // Update users table
        $stmt = $conn->prepare("UPDATE users SET name = ? WHERE id = ?");
        $stmt->bind_param('si', $name, $user_id);
        $stmt->execute();
        // Insert or update profiles
        $stmt = $conn->prepare("SELECT 1 FROM profiles WHERE user_id = ?");
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $stmt = $conn->prepare("UPDATE profiles SET bio = ?, interests = ?, social_links = ? WHERE user_id = ?");
            $stmt->bind_param('sssi', $bio, $interests, $social_links, $user_id);
        } else {
            $stmt = $conn->prepare("INSERT INTO profiles (user_id, bio, interests, social_links) VALUES (?, ?, ?, ?)");
            $stmt->bind_param('isss', $user_id, $bio, $interests, $social_links);
        }
        $stmt->execute();
        header('Location: profile.php?updated=1');
        exit;
    }
}
?>
<h2>Profile</h2>
<?php if ($success): ?>
  <div class="alert alert-success">Profile updated successfully.</div>
<?php endif; ?>
<?php if (!empty($errors)): ?>
  <div class="alert alert-danger">
    <ul>
      <?php foreach ($errors as $error): ?>
        <li><?= htmlspecialchars($error) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>
<form method="post" action="profile.php" enctype="multipart/form-data">
  <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
  <div class="mb-3">
    <label class="form-label">Avatar</label>
    <div>
      <img src="<?= htmlspecialchars($user['avatar'] ?? 'uploads/default.png') ?>" alt="Avatar" class="img-thumbnail" width="100">
    </div>
    <input type="file" name="avatar" accept="image/*" class="form-control mt-2">
  </div>
  <div class="mb-3">
    <label class="form-label">Name</label>
    <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
  </div>
  <div class="mb-3">
    <label class="form-label">Email</label>
    <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" disabled>
  </div>
  <div class="mb-3">
    <label class="form-label">Bio</label>
    <textarea class="form-control" name="bio"><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
  </div>
  <div class="mb-3">
    <label class="form-label">Interests</label>
    <input type="text" class="form-control" name="interests" value="<?= htmlspecialchars($user['interests'] ?? '') ?>">
  </div>
  <div class="mb-3">
    <label class="form-label">Social Links (JSON)</label>
    <textarea class="form-control" name="social_links"><?= htmlspecialchars($user['social_links'] ?? '') ?></textarea>
  </div>
  <button type="submit" class="btn btn-primary">Save Profile</button>
</form>
<?php
require_once __DIR__ . '/includes/footer.php';
?>