<?php
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="uk">

<head>
  <meta charset="UTF-8">
  <title>Агентство нерухомості</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      background-color: #f6f6f6;
      color: #333;
    }

    header {
      background-color: #005baa;
      padding: 1rem 0;
      color: white;
      position: sticky;
      top: 0;
      z-index: 1000;
    }

    nav {
      display: flex;
      justify-content: space-between;
      align-items: center;
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 1rem;
      flex-wrap: wrap;
    }

    .logo {
      font-size: 1.3rem;
      font-weight: bold;
    }

    .menu {
      display: flex;
      align-items: center;
      gap: 15px;
      position: relative;
      flex-wrap: wrap;
    }

    .nav-btn {
      text-decoration: none;
      padding: 8px 14px;
      border: 2px solid white;
      border-radius: 6px;
      color: white;
      font-weight: bold;
      transition: 0.3s ease;
    }

    .nav-btn:hover {
      background-color: rgba(255, 255, 255, 0.15);
    }

    .filled {
      background-color: white;
      color: #005baa;
    }

    .dropdown {
      position: relative;
    }

    .dropdown-content {
      display: none;
      position: absolute;
      background-color: white;
      top: 100%;
      left: 0;
      min-width: 150px;
      border-radius: 6px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
      overflow: hidden;
      z-index: 1000;
    }

    .dropdown-content a {
      display: block;
      padding: 10px 15px;
      color: #005baa;
      text-decoration: none;
      font-weight: normal;
    }

    .dropdown-content a:hover {
      background-color: #f0f0f0;
    }

    .dropdown:hover .dropdown-content {
      display: block;
    }

    .welcome {
      text-align: center;
      padding: 2rem;
    }

    .hero {
      background: url('pics/lviv_index.jpg') center/cover no-repeat;
      height: 100vh;
      position: relative;
      color: white;
    }

    .overlay {
      background-color: rgba(0, 0, 0, 0.4);
      height: 84.5%;
      padding: 60px 20px;
      text-align: center;
    }

    .hero h1 {
      font-size: 3rem;
      font-weight: bold;
      margin-bottom: 30px;
      line-height: 1.2;
    }

    .tabs {
      display: flex;
      justify-content: center;
      gap: 15px;
      margin-bottom: 25px;
      flex-wrap: wrap;
    }

    .tabs .tab {
      background-color: white;
      color: #444;
      border: none;
      padding: 12px 24px;
      font-weight: bold;
      border-radius: 30px;
      cursor: pointer;
      transition: 0.3s;
    }

    .tabs .tab.active {
      background: linear-gradient(to right, #fce3e3, #fff);
      color: #d50000;
    }

    .tabs .tab:hover {
      opacity: 0.9;
    }

    .search-form {
      max-width: 1000px;
      margin: 0 auto;
      display: flex;
      flex-direction: column;
      gap: 15px;
    }

    .search-form .row {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 15px;
    }

    .search-form select {
      padding: 10px 14px;
      font-size: 1rem;
      border-radius: 10px;
      border: none;
      min-width: 180px;
      box-sizing: border-box;
      background-color: white;
      color: #333;
    }

    .search-form button {
      background-color: #8b0000;
      color: white;
      border: none;
      border-radius: 20px;
      padding: 12px 28px;
      font-size: 1rem;
      font-weight: bold;
      cursor: pointer;
      transition: 0.3s;
    }

    .search-form button:hover {
      background-color: #a30000;
    }

    #house-floors {
      display: none;
    }
  </style>
</head>

<body>
  <?php include 'header.php'; ?>
  <main style="flex: 1;">
    <section class="hero">
      <div class="overlay">
        <h1>Купити нерухомість у Львові</h1>

        <div class="tabs">
          <button class="tab active">Купити</button>
          <button class="tab" onclick="window.location.href='sell.php'">Продати</button>
        </div>

        <form class="search-form" method="GET" action="search_redirect.php">
          <div class="row">
            <select name="type">
              <option value="flat">Квартири</option>
              <option value="house">Будинки</option>
            </select>

            <select name="price">
              <option value="0-20000">до 20 000 $</option>
              <option value="20000-50000">20 000 – 50 000 $</option>
              <option value="50000-100000">50 000 – 100 000 $</option>
              <option value="100000+">100 000 $+</option>
            </select>

            <select name="rooms">
              <option value="1">1 кімната</option>
              <option value="2">2 кімнати</option>
              <option value="3">3 кімнати</option>
              <option value="4+">4 і більше</option>
            </select>

            <div class="row" id="house-floors">
              <select name="floors">
                <option value="">Кількість поверхів</option>
                <option value="1">1 поверх</option>
                <option value="2">2 поверхи</option>
                <option value="3">3 поверхи</option>
                <option value="4">4 поверхи</option>
                <option value="5">5 поверхів</option>
              </select>
            </div>

            <button type="submit">Показати</button>
          </div>
        </form>
      </div>
    </section>
  </main>
  <?php include 'footer.php'; ?>
</body>

</html>

<script>
  const typeSelect = document.querySelector('select[name="type"]');
  const floorsField = document.getElementById('house-floors');

  function toggleFloorsField() {
    if (typeSelect.value === 'house') {
      floorsField.style.display = 'flex';
    } else {
      floorsField.style.display = 'none';
    }
  }

  typeSelect.addEventListener('change', toggleFloorsField);
  document.addEventListener('DOMContentLoaded', toggleFloorsField);
</script>