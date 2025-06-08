<?php
session_start();
include 'connect_to_db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$property_id = isset($_GET['property_id']) ? (int)$_GET['property_id'] : 0;

// Якщо адмін/модератор і передано user_id, то це чат з цим користувачем
if ((isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) && isset($_GET['user_id'])) {
    $other_id = (int)$_GET['user_id'];
    $admin_id = $_SESSION['user_id'];
    // Витягуємо історію повідомлень між цим юзером і адміном
    $msg_stmt = $conn->prepare("SELECT m.*, u.username FROM messages m JOIN users u ON m.sender_id = u.user_id WHERE (m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?) ORDER BY m.created_at ASC");
    $msg_stmt->bind_param("iiii", $other_id, $admin_id, $admin_id, $other_id);
    $msg_stmt->execute();
    $msgs = $msg_stmt->get_result();
    $user_id = $other_id;
    $moderator_id = $admin_id;
} else {
    // Знайти модератора (або адміна)
    $mod_stmt = $conn->query("SELECT user_id FROM users WHERE role IN ('admin','moderator') ORDER BY role='admin' DESC, user_id ASC LIMIT 1");
    $moderator = $mod_stmt->fetch_assoc();
    $moderator_id = $moderator ? $moderator['user_id'] : null;
    if (!$moderator_id) {
        die('Модератор не знайдений.');
    }
    // Витягуємо історію повідомлень між цим юзером і модератором
    $msg_stmt = $conn->prepare("SELECT m.*, u.username FROM messages m JOIN users u ON m.sender_id = u.user_id WHERE (m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?) ORDER BY m.created_at ASC");
    $msg_stmt->bind_param("iiii", $user_id, $moderator_id, $moderator_id, $user_id);
    $msg_stmt->execute();
    $msgs = $msg_stmt->get_result();
}

// Надсилання повідомлення
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $text = trim($_POST['message']);
    if ($text !== '') {
        if ((isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) && isset($_GET['user_id'])) {
            // Адмін пише користувачу
            $sender = $_SESSION['user_id'];
            $receiver = (int)$_GET['user_id'];
        } else {
            // Юзер пише модератору/адміну
            $sender = $_SESSION['user_id'];
            $receiver = $moderator_id;
        }
        $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, text) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $sender, $receiver, $text);
        $stmt->execute();
    }
    if ((isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) && isset($_GET['user_id'])) {
        header("Location: chat.php?user_id=" . (int)$_GET['user_id']);
    } else {
        header("Location: chat.php?property_id=$property_id");
    }
    exit();
}

?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Чат з модератором</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .chat-box { max-width: 600px; margin: 30px auto; background: #fff; border-radius: 10px; box-shadow: 0 2px 8px #0001; padding: 20px; }
        .chat-history { max-height: 350px; overflow-y: auto; margin-bottom: 20px; }
        .msg { margin-bottom: 12px; }
        .msg.me { text-align: right; }
        .msg .bubble { display: inline-block; padding: 8px 16px; border-radius: 16px; background: #f5f5f5; }
        .msg.me .bubble { background: #e3f2fd; color: #1565c0; }
        .msg .meta { font-size: 0.8em; color: #888; margin-top: 2px; }
        .chat-form { display: flex; gap: 10px; align-items: flex-end; }
        .chat-form textarea { flex: 1 1 auto; min-width: 0; border-radius: 8px; border: 1px solid #ccc; padding: 8px; resize: vertical; font-size: 1rem; }
        .chat-form button { background: #007bff; color: #fff; border: none; border-radius: 8px; padding: 10px 24px; font-weight: bold; cursor: pointer; font-size: 1rem; white-space: nowrap; }
        @media (max-width: 600px) {
            .chat-box { padding: 8px; }
            .chat-form { flex-direction: column; gap: 6px; }
            .chat-form button { width: 100%; }
        }
    </style>
</head>
<body>
    <div class="chat-box">
        <h2>Чат з модератором</h2>
        <div class="chat-history">
            <?php while ($msg = $msgs->fetch_assoc()): ?>
                <div class="msg<?= $msg['sender_id'] == $user_id ? ' me' : '' ?>">
                    <div class="bubble">
                        <?= htmlspecialchars($msg['text']) ?>
                    </div>
                    <div class="meta">
                        <?= htmlspecialchars($msg['username']) ?>, <?= date('d.m.Y H:i', strtotime($msg['created_at'])) ?>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
        <form class="chat-form" method="post">
            <textarea name="message" rows="2" placeholder="Ваше повідомлення..." required></textarea>
            <button type="submit">Відправити</button>
        </form>
        <a href="profile.php" style="display:block; margin-top:15px; text-align:center;">← До профілю</a>
    </div>
</body>
</html> 