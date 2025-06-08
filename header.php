<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$isLoggedIn = isset($_SESSION['user_id']);
$isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
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
      <div class="dropdown">
        <a href="#" class="nav-btn">Продати ▾</a>
        <div class="dropdown-content">
          <a href="sell_house.php">Будинок</a>
          <a href="sell_flat.php">Квартира</a>
        </div>
      </div>

      <?php if ($isAdmin): ?>
        <a href="admin_dashboard.php" class="nav-btn" style="background-color: #dc3545; color: white;">Адмін панель</a>
        <a href="admin_messages.php" class="nav-btn" style="background-color: #ffc107; color: #222;">Чати</a>
      <?php endif; ?>

      <?php if ($isLoggedIn): ?>
        <a href="profile.php" class="nav-btn filled">Профіль</a>
      <?php else: ?>
        <a href="login.php" class="nav-btn filled">Увійти</a>
        <a href="register.php" class="nav-btn">Реєстрація</a>
      <?php endif; ?>
    </div>
  </nav>
</header>
