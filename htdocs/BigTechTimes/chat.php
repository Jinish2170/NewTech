<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/config/db_connect.php';
ensure_logged_in();
$current_id = current_user_id();

// Handle send message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && validate_csrf_token($_POST['csrf_token'] ?? '')) {
    $receiver_id = intval($_POST['receiver_id']);
    $body = trim($_POST['body']);
    if ($body) {
        $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, body) VALUES (?, ?, ?)");
        $stmt->bind_param('iis', $current_id, $receiver_id, $body);
        $stmt->execute();
    }
    header('Location: chat.php?user_id=' . $receiver_id);
    exit;
}

// Handle AJAX polling for new messages
if (isset($_GET['poll']) && $_GET['poll'] == '1' && is_logged_in()) {
    $other = intval($_GET['user_id']);
    header('Content-Type: application/json');
    $stmt = $conn->prepare("SELECT m.sender_id, m.body, m.sent_at, u.name
        FROM messages m JOIN users u ON m.sender_id=u.id
        WHERE (sender_id=? AND receiver_id=?) OR (sender_id=? AND receiver_id=?)
        ORDER BY m.sent_at");
    $stmt->bind_param('iiii', $current_id, $other, $other, $current_id);
    $stmt->execute();
    $msgs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    ob_start();
    foreach ($msgs as $msg) {
        echo '<div class="mb-2 text-'.($msg['sender_id']==$current_id?'end':'start').'">';
        echo '<strong>'.htmlspecialchars($msg['name']).':</strong> '.nl2br(htmlspecialchars($msg['body']));
        echo '<br><small>'.$msg['sent_at'].'</small>';
        echo '</div>';
    }
    $html = ob_get_clean();
    echo json_encode(['html'=>$html]);
    exit;
}

// Fetch users for chat list
$stmt = $conn->prepare("SELECT id, name FROM users WHERE id != ?");
$stmt->bind_param('i', $current_id);
$stmt->execute();
$users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$selected_id = $_GET['user_id'] ?? null;
$messages = [];
$selected_name = '';
if ($selected_id) {
    // Fetch message history
    $stmt = $conn->prepare("SELECT m.sender_id, m.receiver_id, m.body, m.sent_at, u.name FROM messages m JOIN users u ON m.sender_id=u.id WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?) ORDER BY m.sent_at");
    $stmt->bind_param('iiii', $current_id, $selected_id, $selected_id, $current_id);
    $stmt->execute();
    $messages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    // Get receiver name
    foreach ($users as $u) {
        if ($u['id'] == $selected_id) { $selected_name = $u['name']; break; }
    }
}
?>
<h2>Chat</h2>
<div class="row">
  <div class="col-md-4">
    <h5>Contacts</h5>
    <ul class="list-group">
      <?php foreach ($users as $u): ?>
        <li class="list-group-item <?php if ($selected_id == $u['id']) echo 'active'; ?>">
          <a href="chat.php?user_id=<?= $u['id'] ?>" class="text-decoration-none <?php if ($selected_id == $u['id']) echo 'text-white'; ?>"><?= htmlspecialchars($u['name']) ?></a>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
  <div class="col-md-8">
    <?php if ($selected_id): ?>
      <h5>Conversation with <?= htmlspecialchars($selected_name) ?></h5>
      <div class="border rounded p-3 mb-3 chat-messages" style="height:400px; overflow-y:scroll;">
        <?php foreach ($messages as $msg): ?>
          <div class="mb-2 text-<?php echo $msg['sender_id'] == $current_id ? 'end' : 'start'; ?>">
            <strong><?= htmlspecialchars($msg['name']) ?>:</strong> <?= nl2br(htmlspecialchars($msg['body'])) ?>
            <br><small><?= $msg['sent_at'] ?></small>
          </div>
        <?php endforeach; ?>
      </div>
      <form method="post" action="chat.php?user_id=<?= $selected_id ?>">
        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
        <input type="hidden" name="receiver_id" value="<?= $selected_id ?>">
        <div class="input-group">
          <textarea class="form-control" name="body" rows="2" required></textarea>
          <button class="btn btn-primary" type="submit">Send</button>
        </div>
      </form>
    <?php else: ?>
      <p>Select a contact to start chatting.</p>
    <?php endif; ?>
  </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>