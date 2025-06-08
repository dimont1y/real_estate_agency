<?php
session_start();
include 'connect_to_db.php';
include 'header.php'; 

// Check if user is admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: /real_estate/login.php");
    exit();
}

// Handle ad deletion
if (isset($_POST['delete_ad'])) {
    $ad_id = $_POST['ad_id'];
    
    // Delete from property_photos first
    $stmt = $conn->prepare("DELETE FROM property_photos WHERE property_id = ?");
    $stmt->bind_param("i", $ad_id);
    $stmt->execute();
    
    // Delete from flat_details or house_details based on type
    $stmt = $conn->prepare("SELECT type_id FROM properties WHERE property_id = ?");
    $stmt->bind_param("i", $ad_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $property = $result->fetch_assoc();
    
    if ($property && isset($property['type_id'])) {
        if ($property['type_id'] == 1) { // Flat
            $stmt = $conn->prepare("DELETE FROM flat_details WHERE property_id = ?");
        } else { // House
            $stmt = $conn->prepare("DELETE FROM house_details WHERE property_id = ?");
        }
        $stmt->bind_param("i", $ad_id);
        $stmt->execute();
    }
    
    // Finally delete from properties
    $stmt = $conn->prepare("DELETE FROM properties WHERE property_id = ?");
    $stmt->bind_param("i", $ad_id);
    $stmt->execute();
}

// Handle approve/reject actions
if (isset($_POST['approve_ad'])) {
    $ad_id = (int)$_POST['ad_id'];
    $stmt = $conn->prepare("UPDATE properties SET status = 'approved' WHERE property_id = ?");
    $stmt->bind_param("i", $ad_id);
    $stmt->execute();
}
if (isset($_POST['reject_ad'])) {
    $ad_id = (int)$_POST['ad_id'];
    $stmt = $conn->prepare("UPDATE properties SET status = 'rejected' WHERE property_id = ?");
    $stmt->bind_param("i", $ad_id);
    $stmt->execute();
}

// Handle user management actions
if (isset($_POST['block_user'])) {
    $user_id = (int)$_POST['user_id'];
    $stmt = $conn->prepare("UPDATE users SET is_blocked = 1 WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
}
if (isset($_POST['unblock_user'])) {
    $user_id = (int)$_POST['user_id'];
    $stmt = $conn->prepare("UPDATE users SET is_blocked = 0 WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
}
if (isset($_POST['change_role'])) {
    $user_id = (int)$_POST['user_id'];
    $new_role = $_POST['new_role'];
    if (in_array($new_role, ['user', 'admin', 'moderator'])) {
        $stmt = $conn->prepare("UPDATE users SET role = ? WHERE user_id = ?");
        $stmt->bind_param("si", $new_role, $user_id);
        $stmt->execute();
    }
}

// Handle top/highlight actions
if (isset($_POST['make_top'])) {
    $ad_id = (int)$_POST['ad_id'];
    $stmt = $conn->prepare("UPDATE properties SET is_top = 1 WHERE property_id = ?");
    $stmt->bind_param("i", $ad_id);
    $stmt->execute();
}
if (isset($_POST['remove_top'])) {
    $ad_id = (int)$_POST['ad_id'];
    $stmt = $conn->prepare("UPDATE properties SET is_top = 0 WHERE property_id = ?");
    $stmt->bind_param("i", $ad_id);
    $stmt->execute();
}
if (isset($_POST['make_highlighted'])) {
    $ad_id = (int)$_POST['ad_id'];
    $stmt = $conn->prepare("UPDATE properties SET is_highlighted = 1 WHERE property_id = ?");
    $stmt->bind_param("i", $ad_id);
    $stmt->execute();
}
if (isset($_POST['remove_highlighted'])) {
    $ad_id = (int)$_POST['ad_id'];
    $stmt = $conn->prepare("UPDATE properties SET is_highlighted = 0 WHERE property_id = ?");
    $stmt->bind_param("i", $ad_id);
    $stmt->execute();
}

// Fetch all properties with their details (top first)
$properties = $conn->query("
    SELECT p.*, pt.type_name, u.username as owner_name 
    FROM properties p 
    JOIN propertytypes pt ON p.type_id = pt.type_id 
    JOIN users u ON p.owner_id = u.user_id 
    ORDER BY p.is_top DESC, p.property_id DESC
");

// Fetch pending properties for moderation
$pending_properties = $conn->query("
    SELECT p.*, pt.type_name, u.username as owner_name 
    FROM properties p 
    JOIN propertytypes pt ON p.type_id = pt.type_id 
    JOIN users u ON p.owner_id = u.user_id 
    WHERE p.status = 'pending' 
    ORDER BY p.property_id DESC
");

// Fetch all users
$all_users = $conn->query("SELECT user_id, username, email, phone, role, is_blocked FROM users ORDER BY user_id DESC");

// Fetch all chat dialogs (для модератора/адміна)
$dialogs = [];
if ($_SESSION['is_admin']) {
    $admin_id = $_SESSION['user_id'];
    $dialogs_sql = "
        SELECT 
            IF(u.role IN ('admin','moderator'), m.sender_id, m.receiver_id) as user_id,
            u2.username,
            SUM(m.receiver_id = u2.user_id AND m.is_read = 0) as unread_count,
            MAX(m.created_at) as last_msg_time
        FROM messages m
        JOIN users u ON m.sender_id = u.user_id OR m.receiver_id = u.user_id
        JOIN users u2 ON u2.user_id = IF(u.role IN ('admin','moderator'), m.receiver_id, m.sender_id)
        WHERE (u.role = 'user' AND (m.sender_id IN (SELECT user_id FROM users WHERE role IN ('admin','moderator')) OR m.receiver_id IN (SELECT user_id FROM users WHERE role IN ('admin','moderator'))))
        GROUP BY user_id, u2.username
        ORDER BY last_msg_time DESC
    ";
    $dialogs = $conn->query($dialogs_sql);
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Адмін панель</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .admin-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
        }
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .admin-section {
            margin-bottom: 30px;
        }
        .admin-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            background: #fff !important;
            color: #222 !important;
        }
        .admin-table th, .admin-table td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
            background: #fff !important;
            color: #222 !important;
        }
        .admin-table th {
            background-color: #f5f5f5 !important;
            color: #222 !important;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        .action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 120px;
            padding: 12px 18px;
            font-size: 1rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-align: center;
            font-weight: bold;
            transition: background 0.2s, color 0.2s;
            box-sizing: border-box;
            line-height: 1.2;
        }
        .action-btn:active, .action-btn:focus {
            outline: none;
        }
        .action-buttons form {
            margin: 0;
        }
        /* .action-buttons button.action-btn {
            background: none;
            border: none;
            padding: 0;
        } */
        .edit-btn {
            background-color: #007bff;
            color: white;
            text-decoration: none;
        }
        .edit-btn:hover {
            background-color: #0056b3;
        }
        .delete-btn {
            background-color: #dc3545;
            color: white;
        }
        .delete-btn:hover {
            background-color: #b71c1c;
        }
        .admin-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        .admin-tab {
            padding: 10px 24px;
            background: #f5f5f5;
            border: 1px solid #ddd;
            border-radius: 8px 8px 0 0;
            cursor: pointer;
            font-weight: bold;
            color: #333;
            transition: background 0.2s, color 0.2s;
        }
        .admin-tab.active {
            background: #007bff;
            color: #fff;
            border-bottom: 2px solid #fff;
        }
        .admin-section { display: none; }
        .admin-section.active { display: block; }
        .highlighted-row {
            background: #fffde7 !important;
            border-left: 6px solid #ffeb3b !important;
        }
        .admin-tab[data-tab="chats"] span { font-size: 1.2em; vertical-align: middle; }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1>Адмін панель</h1>
            <a href="logout.php" class="btn">Вийти</a>
        </div>

        <div class="admin-tabs">
            <div class="admin-tab active" data-tab="ads">Оголошення</div>
            <div class="admin-tab" data-tab="moderation">Модерація</div>
            <div class="admin-tab" data-tab="users">Користувачі</div>
            <a href="admin_messages.php" class="admin-tab" style="background:#ffc107; color:#222; text-decoration:none;">Чати</a>
        </div>

        <div id="ads" class="admin-section active">
            <h2>Всі оголошення</h2>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Тип</th>
                        <th>Адреса</th>
                        <th>Площа</th>
                        <th>Кімнат</th>
                        <th>Ціна</th>
                        <th>Власник</th>
                        <th>Дії</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($property = $properties->fetch_assoc()): ?>
                        <?php if ($property['status'] !== 'approved') continue; ?>
                        <tr
                            <?php if ($property['is_top']): ?> style="background: #fffbe6; border-left: 6px solid #ffc107;"<?php endif; ?>
                            <?php if ($property['is_highlighted']): ?> class="highlighted-row"<?php endif; ?>
                        >
                            <td><?php echo htmlspecialchars($property['property_id']); ?></td>
                            <td><?php echo htmlspecialchars($property['type_name']); ?></td>
                            <td><?php echo htmlspecialchars($property['address']); ?></td>
                            <td><?php echo htmlspecialchars($property['area']); ?> м²</td>
                            <td><?php echo htmlspecialchars($property['rooms']); ?></td>
                            <td><?php echo number_format($property['price'], 0, '.', ' '); ?> $</td>
                            <td><?php echo htmlspecialchars($property['owner_name']); ?></td>
                            <td class="action-buttons">
                                <a href="<?php echo $property['type_id'] == 1 ? 'edit_flat.php' : 'edit_house.php'; ?>?id=<?php echo $property['property_id']; ?>" class="action-btn edit-btn">Редагувати</a>
                                <form method="post" style="display: inline;">
                                    <input type="hidden" name="ad_id" value="<?php echo $property['property_id']; ?>">
                                    <button type="submit" name="delete_ad" class="action-btn delete-btn" onclick="return confirm('Ви впевнені, що хочете видалити це оголошення?')">Видалити</button>
                                </form>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="ad_id" value="<?php echo $property['property_id']; ?>">
                                    <?php if ($property['is_top']): ?>
                                        <button type="submit" name="remove_top" class="action-btn" style="background:#ffe082; color:#333;">Зняти з топу</button>
                                    <?php else: ?>
                                        <button type="submit" name="make_top" class="action-btn" style="background:#ffc107; color:#333;">Підняти в топ</button>
                                    <?php endif; ?>
                                </form>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="ad_id" value="<?php echo $property['property_id']; ?>">
                                    <?php if ($property['is_highlighted']): ?>
                                        <button type="submit" name="remove_highlighted" class="action-btn" style="background:#ffe082; color:#333;">Зняти виділення</button>
                                    <?php else: ?>
                                        <button type="submit" name="make_highlighted" class="action-btn" style="background:#ffeb3b; color:#333;">Виділити</button>
                                    <?php endif; ?>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div id="moderation" class="admin-section">
            <h2>Оголошення на модерації</h2>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Тип</th>
                        <th>Адреса</th>
                        <th>Площа</th>
                        <th>Кімнат</th>
                        <th>Ціна</th>
                        <th>Власник</th>
                        <th>Дії</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($property = $pending_properties->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($property['property_id']); ?></td>
                        <td><?php echo htmlspecialchars($property['type_name']); ?></td>
                        <td><?php echo htmlspecialchars($property['address']); ?></td>
                        <td><?php echo htmlspecialchars($property['area']); ?> м²</td>
                        <td><?php echo htmlspecialchars($property['rooms']); ?></td>
                        <td><?php echo number_format($property['price'], 0, '.', ' '); ?> $</td>
                        <td><?php echo htmlspecialchars($property['owner_name']); ?></td>
                        <td class="action-buttons">
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="ad_id" value="<?php echo $property['property_id']; ?>">
                                <button type="submit" name="approve_ad" class="action-btn edit-btn">Схвалити</button>
                            </form>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="ad_id" value="<?php echo $property['property_id']; ?>">
                                <button type="submit" name="reject_ad" class="action-btn delete-btn">Відхилити</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div id="users" class="admin-section">
            <h2>Керування користувачами</h2>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Ім'я</th>
                        <th>Email</th>
                        <th>Телефон</th>
                        <th>Роль</th>
                        <th>Статус</th>
                        <th>Дії</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user = $all_users->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['user_id']) ?></td>
                        <td><?= htmlspecialchars($user['username']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><?= htmlspecialchars($user['phone']) ?></td>
                        <td>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                                <select name="new_role" onchange="this.form.submit()" class="action-btn edit-btn" style="min-width:90px; padding:4px 8px; font-size:1rem;">
                                    <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>User</option>
                                    <option value="moderator" <?= $user['role'] === 'moderator' ? 'selected' : '' ?>>Moderator</option>
                                    <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                </select>
                                <input type="hidden" name="change_role" value="1">
                            </form>
                        </td>
                        <td><?= $user['is_blocked'] ? '<span style="color:red;">Заблокований</span>' : '<span style="color:green;">Активний</span>' ?></td>
                        <td>
                            <?php if ($user['is_blocked']): ?>
                                <form method="post" style="display:inline;"><input type="hidden" name="user_id" value="<?= $user['user_id'] ?>"><button type="submit" name="unblock_user" class="action-btn edit-btn">Розблокувати</button></form>
                            <?php else: ?>
                                <form method="post" style="display:inline;"><input type="hidden" name="user_id" value="<?= $user['user_id'] ?>"><button type="submit" name="block_user" class="action-btn delete-btn">Заблокувати</button></form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
    // JS для перемикання вкладок
    document.querySelectorAll('.admin-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            document.querySelectorAll('.admin-tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.admin-section').forEach(s => s.classList.remove('active'));
            this.classList.add('active');
            document.getElementById(this.dataset.tab).classList.add('active');
        });
    });
    </script>
</body>
</html> 