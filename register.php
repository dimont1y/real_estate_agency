<?php
session_start();
include 'connect_to_db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    if (!preg_match("/^\\+380\\d{9}$/", $phone)) {
        $error = "Невірний формат номеру телефону. Використовуйте формат +380XXXXXXXXX";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (username, email, password_hash, phone) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username, $email, $password, $phone);

        if ($stmt->execute()) {
            $user_id = $stmt->insert_id;
            $_SESSION['user_id'] = $user_id;
            header("Location: /real_estate/index.php");
            exit();
        } else {
            $error = "Такий Email вже зареєстрований.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="uk">

<head>
    <meta charset="UTF-8">
    <title>Реєстрація</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <div class="register-box">
        <h2>Реєстрація</h2>
        <?php if (!empty($error)) echo "<p class='error'>" . htmlspecialchars($error) . "</p>"; ?>
        <form method="post">
            <label for="username">Ім'я користувача:</label>
            <input type="text" name="username" id="username" required>

            <label for="email">Email:</label>
            <input type="email" name="email" id="email" required>

            <label for="phone">Номер телефону:</label>
            <input type="tel" name="phone" id="phone" required pattern="\+380\d{9}" placeholder="+380XXXXXXXXX" value="+380">

            <label for="password">Пароль:</label>
            <input type="password" name="password" id="password" required>

            <button type="submit">Зареєструватися</button>
        </form>
        <a href="index.php" style="
    display: block;
    margin-top: 1rem;
    text-align: center;
    text-decoration: none;
    background-color: #007bff;
    color: white;
    padding: 10px;
    border-radius: 6px;
    font-weight: bold;
    transition: background-color 0.3s;">
    На головну сторінку</a>

    </div>

</body>

</html>