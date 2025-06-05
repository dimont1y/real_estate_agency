<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'connect_to_db.php';
include 'header.php';

$user_id = $_SESSION['user_id'];

// Generate CSRF token for security
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $new_username = trim($_POST['username']);
    $new_email = trim($_POST['email']);
    $new_phone = trim($_POST['phone']);
    $new_password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;

    $stmt = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE user_id = ?");
    $stmt->bind_param("ssi", $new_username, $new_email, $user_id);
    $stmt->execute();

    $stmt2 = $conn->prepare("UPDATE userprofiles SET phone = ? WHERE user_id = ?");
    $stmt2->bind_param("si", $new_phone, $user_id);
    $stmt2->execute();

    if ($new_password) {
        $stmt3 = $conn->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
        $stmt3->bind_param("si", $new_password, $user_id);
        $stmt3->execute();
    }

    header("Location: profile.php");
    exit();
}

$stmt = $conn->prepare("SELECT u.username, u.email, up.phone FROM users u LEFT JOIN userprofiles up ON u.user_id = up.user_id WHERE u.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$userResult = $stmt->get_result();
$user = $userResult->fetch_assoc();

$adsStmt = $conn->prepare("SELECT property_id, address, area, rooms, price, type_id FROM properties WHERE owner_id = ?");
$adsStmt->bind_param("i", $user_id);
$adsStmt->execute();
$adsResult = $adsStmt->get_result();

$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']); // Clear the message after displaying
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мій профіль</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .property {
            border: 1px solid #ddd;
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .property a, .delete-form button {
            margin-left: 10px;
            padding: 5px 10px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 0.9rem;
        }
        .property a {
            background-color: #007bff;
            color: white;
        }
        .property a:hover {
            background-color: #0056b3;
        }
        .delete-form {
            display: inline;
        }
        .delete-form button {
            background-color: #a30000;
            color: white;
            border: none;
            cursor: pointer;
        }
        .delete-form button:hover {
            background-color: #c82333;
        }
        .message {
            color: #a30000;
            font-weight: bold;
            margin-bottom: 1rem;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Мій профіль</h2>

        <?php if ($message): ?>
            <p class="message"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="action" value="update_profile">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            <label>Ім’я:
                <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
            </label>
            <label>Email:
                <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
            </label>
            <label>Номер телефону:
                <input type="tel" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" pattern="\+380\d{9}" required>
            </label>
            <label>Новий пароль:
                <input type="password" name="password" placeholder="Залиште порожнім, щоб не змінювати">
            </label>
            <button type="submit">Оновити профіль</button>
            <a href="logout.php" class="btn" style="margin-top: 1rem; display: inline-block; background-color: #a30000; color: white; padding: 10px 20px; border-radius: 6px; text-decoration: none;">Вийти з акаунту</a>
        </form>

        <h3>Мої оголошення</h3>
        <?php if ($adsResult->num_rows > 0): ?>
            <?php while ($ad = $adsResult->fetch_assoc()): ?>
                <div class="property">
                    <div>
                        <strong><?= htmlspecialchars($ad['address']) ?></strong><br>
                        Площа: <?= htmlspecialchars($ad['area']) ?> м²<br>
                        Кімнат: <?= htmlspecialchars($ad['rooms']) ?><br>
                        Ціна: <?= number_format($ad['price'], 0, '.', ' ') ?> $
                    </div>
                    <div>
                        <a href="property_details.php?id=<?= $ad['property_id'] ?>">Переглянути</a>
                        <a href="<?= $ad['type_id'] == 1 ? 'edit_flat.php?id=' . $ad['property_id'] : 'edit_house.php?id=' . $ad['property_id'] ?>">Редагувати</a>
                        <form class="delete-form" action="delete_property.php" method="POST" onsubmit="return confirmDelete();">
                            <input type="hidden" name="property_id" value="<?= $ad['property_id'] ?>">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                            <button type="submit">Видалити</button>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>Оголошень ще немає.</p>
        <?php endif; ?>
    </div>

    <script>
        function confirmDelete() {
            return confirm('Ви впевнені, що хочете видалити це оголошення? Цю дію неможливо скасувати.');
        }
    </script>

    <?php include 'footer.php'; ?>
</body>
</html>