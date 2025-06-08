<?php
session_start();
include 'connect_to_db.php';
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: login.php");
    exit();
}

// Витягуємо всі унікальні діалоги user <-> (admin/moderator)
$dialogs_sql = "
    SELECT 
        u.user_id,
        u.username,
        SUM(m.receiver_id = u.user_id AND m.is_read = 0) as unread_count,
        MAX(m.created_at) as last_msg_time
    FROM messages m
    JOIN users u ON (u.user_id = m.sender_id AND u.role = 'user') OR (u.user_id = m.receiver_id AND u.role = 'user')
    WHERE m.sender_id IN (SELECT user_id FROM users WHERE role IN ('admin','moderator'))
       OR m.receiver_id IN (SELECT user_id FROM users WHERE role IN ('admin','moderator'))
    GROUP BY u.user_id, u.username
    ORDER BY last_msg_time DESC
";
$dialogs = $conn->query($dialogs_sql);
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Чати з користувачами</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .admin-container { max-width: 900px; margin: 30px auto; padding: 20px; background: #fff; border-radius: 10px; box-shadow: 0 2px 8px #0001; }
        .admin-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .admin-table { width: 100%; border-collapse: collapse; margin-top: 10px; background: #fff; color: #222; }
        .admin-table th, .admin-table td { padding: 10px; border: 1px solid #ddd; text-align: left; background: #fff; color: #222; }
        .admin-table th { background-color: #f5f5f5; color: #222; }
        .action-btn { display: inline-block; min-width: 120px; padding: 10px 18px; font-size: 1rem; border: none; border-radius: 8px; cursor: pointer; text-align: center; font-weight: bold; transition: background 0.2s, color 0.2s; }
        .edit-btn { background-color: #007bff; color: white; text-decoration: none; }
        .edit-btn:hover { background-color: #0056b3; }
        .unread-dot { color: red; font-size: 1.2em; vertical-align: middle; }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1>Чати з користувачами</h1>
            <a href="admin_dashboard.php" class="action-btn" style="background:#dc3545; color:#fff;">← До адмін-панелі</a>
        </div>
        <?php if ($dialogs && $dialogs->num_rows > 0): ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Користувач</th>
                        <th>Останнє повідомлення</th>
                        <th>Непрочитані</th>
                        <th>Дія</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dialogs as $dialog): ?>
                        <tr>
                            <td><?= htmlspecialchars($dialog['username']) ?></td>
                            <td><?= date('d.m.Y H:i', strtotime($dialog['last_msg_time'])) ?></td>
                            <td><?= $dialog['unread_count'] > 0 ? '<span class="unread-dot">●</span> <span style="color:red; font-weight:bold;">'.$dialog['unread_count'].'</span>' : '0' ?></td>
                            <td><a href="chat.php?user_id=<?= $dialog['user_id'] ?>" class="action-btn edit-btn">Відкрити чат</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Немає чатів з користувачами.</p>
        <?php endif; ?>
    </div>
</body>
</html> 