<?php
session_start();
include 'connect_to_db.php';
include 'header.php'; 

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: /real_estate/login.php");
    exit();
}

if (isset($_POST['delete_ad'])) {
    $ad_id = $_POST['ad_id'];
    
    $stmt = $conn->prepare("DELETE FROM property_photos WHERE property_id = ?");
    $stmt->bind_param("i", $ad_id);
    $stmt->execute();
    
    $stmt = $conn->prepare("SELECT type_id FROM properties WHERE property_id = ?");
    $stmt->bind_param("i", $ad_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $property = $result->fetch_assoc();
    
    if ($property && isset($property['type_id'])) {
        if ($property['type_id'] == 1) { 
            $stmt = $conn->prepare("DELETE FROM flat_details WHERE property_id = ?");
        } else { 
            $stmt = $conn->prepare("DELETE FROM house_details WHERE property_id = ?");
        }
        $stmt->bind_param("i", $ad_id);
        $stmt->execute();
    }
    
    $stmt = $conn->prepare("DELETE FROM properties WHERE property_id = ?");
    $stmt->bind_param("i", $ad_id);
    $stmt->execute();
}

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

$search_address = isset($_GET['search_address']) ? $_GET['search_address'] : '';
$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'property_id';
$sort_order = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'DESC';

$filter_type = isset($_GET['filter_type']) ? $_GET['filter_type'] : '';
$area_from = isset($_GET['area_from']) ? (int)$_GET['area_from'] : '';
$area_to = isset($_GET['area_to']) ? (int)$_GET['area_to'] : '';
$rooms_from = isset($_GET['rooms_from']) ? (int)$_GET['rooms_from'] : '';
$rooms_to = isset($_GET['rooms_to']) ? (int)$_GET['rooms_to'] : '';
$price_from = isset($_GET['price_from']) ? (int)$_GET['price_from'] : '';
$price_to = isset($_GET['price_to']) ? (int)$_GET['price_to'] : '';

$properties_query = "
    SELECT p.*, pt.type_name, u.username as owner_name 
    FROM properties p 
    JOIN propertytypes pt ON p.type_id = pt.type_id 
    JOIN users u ON p.owner_id = u.user_id 
    WHERE p.status = 'approved'
";

if (!empty($search_address)) {
    $search_address = $conn->real_escape_string($search_address);
    $properties_query .= " AND p.address LIKE '%$search_address%'";
}
if (!empty($filter_type)) {
    $filter_type = $conn->real_escape_string($filter_type);
    $properties_query .= " AND pt.type_name = '$filter_type'";
}
if ($area_from !== '') {
    $properties_query .= " AND p.area >= $area_from";
}
if ($area_to !== '') {
    $properties_query .= " AND p.area <= $area_to";
}
if ($rooms_from !== '') {
    $properties_query .= " AND p.rooms >= $rooms_from";
}
if ($rooms_to !== '') {
    $properties_query .= " AND p.rooms <= $rooms_to";
}
if ($price_from !== '') {
    $properties_query .= " AND p.price >= $price_from";
}
if ($price_to !== '') {
    $properties_query .= " AND p.price <= $price_to";
}

$allowed_sort_columns = ['property_id', 'type_name', 'address', 'area', 'rooms', 'price'];
$sort_by = in_array($sort_by, $allowed_sort_columns) ? $sort_by : 'property_id';
$sort_order = strtoupper($sort_order) === 'ASC' ? 'ASC' : 'DESC';

$properties_query .= " ORDER BY p.is_top DESC, $sort_by $sort_order";
$properties = $conn->query($properties_query);

$pending_properties = $conn->query("
    SELECT p.*, pt.type_name, u.username as owner_name 
    FROM properties p 
    JOIN propertytypes pt ON p.type_id = pt.type_id 
    JOIN users u ON p.owner_id = u.user_id 
    WHERE p.status = 'pending' 
    ORDER BY p.property_id DESC
");

$user_search = isset($_GET['user_search']) ? $_GET['user_search'] : '';
$all_users_query = "SELECT user_id, username, email, phone, role, is_blocked FROM users";
if (!empty($user_search)) {
    $user_search_esc = $conn->real_escape_string($user_search);
    $all_users_query .= " WHERE username LIKE '%$user_search_esc%' OR email LIKE '%$user_search_esc%' OR phone LIKE '%$user_search_esc%'";
}
$all_users_query .= " ORDER BY user_id DESC";
$all_users = $conn->query($all_users_query);

$dialogs = [];
if ($_SESSION['is_admin']) {
    $admin_id = $_SESSION['user_id'];
    $dialogs_sql = "
        SELECT 
            u.user_id,
            u.username,
            SUM(m.receiver_id = $admin_id AND m.is_read = 0 AND m.sender_id = u.user_id) as unread_count,
            MAX(m.created_at) as last_msg_time
        FROM users u
        LEFT JOIN messages m ON (m.sender_id = u.user_id AND m.receiver_id = $admin_id) OR (m.sender_id = $admin_id AND m.receiver_id = u.user_id)
        WHERE u.role = 'user'
        GROUP BY u.user_id, u.username
        ORDER BY last_msg_time DESC
    ";
    $dialogs = $conn->query($dialogs_sql);
}

$ads_per_day = [];
$ads_days = [];
$res = $conn->query("SELECT DATE(created_at) as day, COUNT(*) as count FROM properties WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 13 DAY) GROUP BY day ORDER BY day ASC");
while ($row = $res->fetch_assoc()) {
    $ads_days[] = $row['day'];
    $ads_per_day[] = (int)$row['count'];
}

$active_users_7d = 0;
$res = $conn->query("SELECT COUNT(DISTINCT user_id) as active_users FROM (\n    SELECT owner_id as user_id FROM properties WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)\n    UNION\n    SELECT sender_id as user_id FROM messages WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)\n    UNION\n    SELECT receiver_id as user_id FROM messages WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)\n) t");
if ($row = $res->fetch_assoc()) $active_users_7d = (int)$row['active_users'];

$top_ads = [];
$res = $conn->query("SELECT property_id, address, views FROM properties ORDER BY views DESC LIMIT 5");
while ($row = $res->fetch_assoc()) $top_ads[] = $row;

$msgs_per_day = [];
$msgs_days = [];
$res = $conn->query("SELECT DATE(created_at) as day, COUNT(*) as count FROM messages WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) GROUP BY day ORDER BY day ASC");
while ($row = $res->fetch_assoc()) {
    $msgs_days[] = $row['day'];
    $msgs_per_day[] = (int)$row['count'];
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Адмін панель</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            <div class="admin-tab" data-tab="analytics">Аналітика</div>
            <a href="admin_messages.php" class="admin-tab" style="background:#ffc107; color:#222; text-decoration:none;">Чати</a>
        </div>

        <div id="ads" class="admin-section active">
            <h2>Всі оголошення</h2>
            <form method="get" class="search-sort-form" style="margin-bottom: 20px; display: flex; flex-wrap: wrap; gap: 10px; align-items: flex-end; width: 100%;">
                <input type="text" name="search_address" value="<?php echo htmlspecialchars($search_address); ?>" placeholder="Пошук за адресою" style="padding: 8px; border: 1px solid #ddd; border-radius: 4px; flex: 1 1 160px; min-width: 120px;">
                <select name="filter_type" style="padding: 8px; border: 1px solid #ddd; border-radius: 4px; flex: 1 1 120px; min-width: 100px;">
                    <option value="">Всі типи</option>
                    <option value="Квартира" <?php echo $filter_type === 'Квартира' ? 'selected' : ''; ?>>Квартира</option>
                    <option value="Будинок" <?php echo $filter_type === 'Будинок' ? 'selected' : ''; ?>>Будинок</option>
                </select>
                <input type="number" name="area_from" value="<?php echo $area_from; ?>" placeholder="Площа від" style="padding:8px; border:1px solid #ddd; border-radius:4px; flex: 1 1 80px; min-width: 70px;">
                <input type="number" name="area_to" value="<?php echo $area_to; ?>" placeholder="Площа до" style="padding:8px; border:1px solid #ddd; border-radius:4px; flex: 1 1 80px; min-width: 70px;">
                <input type="number" name="rooms_from" value="<?php echo $rooms_from; ?>" placeholder="Кімнат від" style="padding:8px; border:1px solid #ddd; border-radius:4px; flex: 1 1 80px; min-width: 70px;">
                <input type="number" name="rooms_to" value="<?php echo $rooms_to; ?>" placeholder="Кімнат до" style="padding:8px; border:1px solid #ddd; border-radius:4px; flex: 1 1 80px; min-width: 70px;">
                <input type="number" name="price_from" value="<?php echo $price_from; ?>" placeholder="Ціна від" style="padding:8px; border:1px solid #ddd; border-radius:4px; flex: 1 1 90px; min-width: 80px;">
                <input type="number" name="price_to" value="<?php echo $price_to; ?>" placeholder="Ціна до" style="padding:8px; border:1px solid #ddd; border-radius:4px; flex: 1 1 90px; min-width: 80px;">
                <button type="submit" class="action-btn edit-btn" style="margin: 0; flex: 1 1 120px; min-width: 100px; max-width: 160px;">Застосувати</button>
                <?php if (!empty($search_address) || !empty($filter_type) || $area_from !== '' || $area_to !== '' || $rooms_from !== '' || $rooms_to !== '' || $price_from !== '' || $price_to !== '' || $sort_by !== 'property_id' || $sort_order !== 'DESC'): ?>
                    <a href="admin_dashboard.php" class="action-btn delete-btn" style="margin: 0; flex: 1 1 120px; min-width: 100px; max-width: 160px; text-decoration: none;">Скинути</a>
                <?php endif; ?>
            </form>
            <form method="get" class="search-sort-form" style="margin-bottom: 20px; display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                <input type="hidden" name="search_address" value="<?php echo htmlspecialchars($search_address); ?>">
                <select name="sort_by" style="padding: 8px; border: 1px solid #ddd; border-radius: 4px; min-width: 180px;">
                    <option value="property_id" <?php echo $sort_by === 'property_id' ? 'selected' : ''; ?>>ID</option>
                    <option value="type_name" <?php echo $sort_by === 'type_name' ? 'selected' : ''; ?>>Тип</option>
                    <option value="address" <?php echo $sort_by === 'address' ? 'selected' : ''; ?>>Адреса</option>
                    <option value="area" <?php echo $sort_by === 'area' ? 'selected' : ''; ?>>Площа</option>
                    <option value="rooms" <?php echo $sort_by === 'rooms' ? 'selected' : ''; ?>>Кімнати</option>
                    <option value="price" <?php echo $sort_by === 'price' ? 'selected' : ''; ?>>Ціна</option>
                </select>
                <select name="sort_order" style="padding: 8px; border: 1px solid #ddd; border-radius: 4px; min-width: 150px;">
                    <option value="ASC" <?php echo $sort_order === 'ASC' ? 'selected' : ''; ?>>За зростанням</option>
                    <option value="DESC" <?php echo $sort_order === 'DESC' ? 'selected' : ''; ?>>За спаданням</option>
                </select>
                <button type="submit" class="action-btn edit-btn" style="margin: 0; min-width: 120px;">Сортувати</button>
            </form>

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
            <form method="get" style="margin-bottom: 20px; display: flex; gap: 10px; align-items: center;">
                <input type="text" name="user_search" value="<?php echo htmlspecialchars($user_search); ?>" placeholder="Пошук за іменем, email або телефоном" style="padding: 8px; border: 1px solid #ddd; border-radius: 4px; min-width: 260px;">
                <button type="submit" class="action-btn edit-btn" style="margin: 0; min-width: 120px;">Знайти</button>
                <?php if (!empty($user_search)): ?>
                    <a href="admin_dashboard.php#users" class="action-btn delete-btn" style="margin: 0; text-decoration: none; min-width: 120px;">Скинути</a>
                <?php endif; ?>
            </form>
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

        <div id="analytics" class="admin-section">
            <h2>Аналітика</h2>
            <div style="margin-bottom: 30px;">
                <h3>📈 Нові оголошення по днях</h3>
                <canvas id="adsPerDayChart" height="80"></canvas>
            </div>
            <div style="margin-bottom: 30px;">
                <h3>👥 Активні користувачі за 7 днів</h3>
                <div id="activeUsers7days" style="font-size:2em; font-weight:bold;"></div>
            </div>
            <div style="margin-bottom: 30px;">
                <h3>🏠 Топові оголошення (по переглядах)</h3>
                <table class="admin-table" id="topAdsTable">
                    <thead><tr><th>ID</th><th>Адреса</th><th>Перегляди</th></tr></thead>
                    <tbody></tbody>
                </table>
            </div>
            <div style="margin-bottom: 30px;">
                <h3>📤 Повідомлення у чатах за останні дні</h3>
                <canvas id="messagesPerDayChart" height="80"></canvas>
            </div>
        </div>
    </div>

    <script>
    document.querySelectorAll('.admin-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            document.querySelectorAll('.admin-tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.admin-section').forEach(s => s.classList.remove('active'));
            this.classList.add('active');
            document.getElementById(this.dataset.tab).classList.add('active');
        });
    });

    const adsDays = <?php echo json_encode($ads_days); ?>;
    const adsPerDay = <?php echo json_encode($ads_per_day); ?>;
    const activeUsers7d = <?php echo json_encode($active_users_7d); ?>;
    const topAds = <?php echo json_encode($top_ads); ?>;
    const msgsDays = <?php echo json_encode($msgs_days); ?>;
    const msgsPerDay = <?php echo json_encode($msgs_per_day); ?>;
    document.querySelector('[data-tab="analytics"]').addEventListener('click', function() {
        if (window.adsChart) window.adsChart.destroy();
        const ctx1 = document.getElementById('adsPerDayChart').getContext('2d');
        window.adsChart = new Chart(ctx1, {
            type: 'bar',
            data: { labels: adsDays, datasets: [{ label: 'Оголошень', data: adsPerDay, backgroundColor: '#007bff' }] },
            options: { scales: { y: { beginAtZero: true } } }
        });
        document.getElementById('activeUsers7days').textContent = activeUsers7d;
        const tbody = document.querySelector('#topAdsTable tbody');
        tbody.innerHTML = '';
        topAds.forEach(ad => {
            const tr = document.createElement('tr');
            tr.innerHTML = `<td>${ad.property_id}</td><td>${ad.address}</td><td>${ad.views}</td>`;
            tbody.appendChild(tr);
        });
        if (window.msgsChart) window.msgsChart.destroy();
        const ctx2 = document.getElementById('messagesPerDayChart').getContext('2d');
        window.msgsChart = new Chart(ctx2, {
            type: 'bar',
            data: { labels: msgsDays, datasets: [{ label: 'Повідомлень', data: msgsPerDay, backgroundColor: '#28a745' }] },
            options: { scales: { y: { beginAtZero: true } } }
        });
    });

    console.log('adsDays', adsDays);
    console.log('adsPerDay', adsPerDay);
    console.log('activeUsers7d', activeUsers7d);
    console.log('topAds', topAds);
    console.log('msgsDays', msgsDays);
    console.log('msgsPerDay', msgsPerDay);
    if (adsDays.length === 0) document.getElementById('adsPerDayChart').insertAdjacentHTML('beforebegin', '<div style="color:#888;">Немає даних для графіка оголошень</div>');
    if (msgsDays.length === 0) document.getElementById('messagesPerDayChart').insertAdjacentHTML('beforebegin', '<div style="color:#888;">Немає даних для графіка повідомлень</div>');
    if (topAds.length === 0) document.querySelector('#topAdsTable tbody').innerHTML = '<tr><td colspan="3" style="text-align:center; color:#888;">Немає даних</td></tr>';
    if (!activeUsers7d) document.getElementById('activeUsers7days').innerHTML = '<span style="color:#888;">Немає даних</span>';
    </script>
</body>
</html> 