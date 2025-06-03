<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'connect_to_db.php';
include 'header.php';

$user_id = $_SESSION['user_id'];

// Обробка редагування профілю
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

$adsStmt = $conn->prepare("SELECT * FROM properties WHERE owner_id = ?");
$adsStmt->bind_param("i", $user_id);
$adsStmt->execute();
$adsResult = $adsStmt->get_result();
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Мій профіль</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <h2>Мій профіль</h2>

    <form method="POST">
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
    </form>

    <h3>Мої оголошення</h3>
    <?php if ($adsResult->num_rows > 0): ?>
        <?php while ($ad = $adsResult->fetch_assoc()): ?>
            <div class="property">
                <strong><?= htmlspecialchars($ad['address']) ?></strong><br>
                Площа: <?= $ad['area'] ?> м²<br>
                Кімнат: <?= $ad['rooms'] ?><br>
                Ціна: <?= number_format($ad['price'], 0, '.', ' ') ?> $<br>
                <a href="property_details.php?id=<?= $ad['property_id'] ?>">Переглянути</a> |
                <a href="edit_property.php?id=<?= $ad['property_id'] ?>">Редагувати</a> |
                <a href="delete_property.php?id=<?= $ad['property_id'] ?>" onclick="return confirm('Ви впевнені?')">Видалити</a>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>Оголошень ще немає.</p>
    <?php endif; ?>
</div>

</body>
</html>
<?php include 'footer.php'; ?>
