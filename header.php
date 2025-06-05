<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$isLoggedIn = isset($_SESSION['user_id']);
?>
<link rel="stylesheet" href="style.css">

<header>
  <nav>
    <div class="logo">
      <a href="index.php" style="text-decoration: none; color: inherit;">Нерухомість Львівщини</a>
    </div>
    <div class="menu">
      <div class="dropdown">
        <a href="#" class="nav-btn">Купити ▾</a>
        <div class="dropdown-content">
          <a href="buy_house.php">Будинок</a>
          <a href="buy_flat.php">Квартира</a>
        </div>
      </div>
      <a href="sell.php" class="nav-btn">Продати</a>

      <?php if ($isLoggedIn): ?>
        <a href="profile.php" class="nav-btn filled">Профіль</a>
      <?php else: ?>
        <a href="login.php" class="nav-btn filled">Увійти</a>
        <a href="register.php" class="nav-btn">Реєстрація</a>
      <?php endif; ?>
    </div>
  </nav>
</header>
