<?php
session_start();
include 'connect_to_db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if ($email === "admin@admin" && $password === "admin") {
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->bind_result($admin_id);
        if ($stmt->fetch()) {
            $_SESSION['user_id'] = $admin_id;
            $_SESSION['is_admin'] = true;
            header("Location: /real_estate/admin_dashboard.php");
            exit();
        } else {
            $error = "Адмін не знайдений у базі.";
        }
    }

    $stmt = $conn->prepare("SELECT user_id, password_hash FROM users WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($user_id, $hash);

    if ($stmt->fetch() && password_verify($password, $hash)) {
        $_SESSION['user_id'] = $user_id;
        $_SESSION['is_admin'] = false;
        header("Location: /real_estate/index.php");
        exit();
    } else {
        $error = "Невірний email або пароль.";
    }
}
?>

<!DOCTYPE html>
<html lang="uk">

<head>
    <meta charset="UTF-8">
    <title>Увійти</title>
    <link rel="stylesheet" href="style.css">
    </style>
</head>

<body>

    <div class="login-box">
        <h2>Вхід</h2>
        <?php if (!empty($error)) echo "<p class='error'>" . htmlspecialchars($error) . "</p>"; ?>
        <form method="post">
            <label for="email">Email:</label>
            <input type="email" name="email" id="email" required>

            <label for="password">Пароль:</label>
            <input type="password" name="password" id="password" required>

            <button type="submit">Увійти</button>
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