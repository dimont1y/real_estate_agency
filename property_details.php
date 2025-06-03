<?php
include 'connect_to_db.php';
include 'header.php';

$property_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($property_id <= 0) {
    echo "<p style='text-align:center;'>Невірний ідентифікатор нерухомості.</p>";
    exit();
}

$stmt = $conn->prepare("SELECT * FROM properties WHERE property_id = ?");
$stmt->bind_param("i", $property_id);
$stmt->execute();
$property = $stmt->get_result()->fetch_assoc();

if (!$property) {
    echo "<p style='text-align:center;'>Оголошення не знайдено.</p>";
    exit();
}

$type_id = $property['type_id'];

if ($type_id == 1) {
    $details_stmt = $conn->prepare("SELECT * FROM flat_details WHERE property_id = ?");
} else {
    $details_stmt = $conn->prepare("SELECT * FROM house_details WHERE property_id = ?");
}

$details_stmt->bind_param("i", $property_id);
$details_stmt->execute();
$details = $details_stmt->get_result()->fetch_assoc();

$property = array_merge($property, $details);
?>

<!DOCTYPE html>
<html lang="uk">
<head>
  <meta charset="UTF-8">
  <title>Деталі нерухомості</title>
  <link rel="stylesheet" href="style.css">
</head>

<body>
  <div class="container">
    <h2><?= htmlspecialchars($property['address']) ?></h2>
    <div class="section details">
      <p><strong>Площа:</strong> <?= $property['area'] ?> м²</p>
      <p><strong>Поверх:</strong> <?= $property['floor'] ?></p>
      <p><strong>Кімнати:</strong> <?= $property['rooms'] ?></p>
      <p><strong>Ціна:</strong> <?= number_format($property['price'], 0, '.', ' ') ?> $</p>
    </div>

    <div class="section">
      <h4>Основна інформація</h4>
      <p><strong>Тип будинку:</strong> <?= $property['building_type'] ?? 'Не вказано' ?></p>
      <p><strong>Рік будівництва / здачі:</strong> <?= $property['build_year'] ?? 'Не вказано' ?></p>
      <p><strong>Ліфт:</strong> <?= isset($property['elevators']) ? "Є, " . $property['elevators'] . " ліфт(ів)" : 'Не вказано' ?></p>
      <p><strong>Опалення:</strong> <?= $property['heating'] ?? 'Не вказано' ?></p>
      <p><strong>Інфраструктура:</strong> <?= $property['infrastructure'] ?? 'Не вказано' ?></p>
      <p><strong>Стан ремонту:</strong> <?= $property['renovation'] ?? 'Не вказано' ?></p>
      <p><strong>Меблі:</strong> <?= $property['furnished'] ?? 'Не вказано' ?></p>
      <p><strong>Побутова техніка:</strong> <?= $property['appliances'] ?? 'Не вказано' ?></p>
      <p><strong>Санвузол:</strong> <?= $property['bathroom'] ?? 'Не вказано' ?><?= isset($property['bathroom_count']) ? ", кількість: " . $property['bathroom_count'] : '' ?></p>
    </div>

    <div class="section">
      <h4>Додаткові опції</h4>
      <p><strong>Інтернет / TV:</strong> <?= isset($property['internet_tv']) ? ($property['internet_tv'] ? 'Підключено' : 'Ні') : 'Не вказано' ?></p>
      <p><strong>Безпека:</strong> <?= $property['security'] ?? 'Не вказано' ?></p>
      <p><strong>Паркінг:</strong> <?= $property['parking'] ?? 'Не вказано' ?></p>
      <p><strong>Тип власності:</strong> <?= $property['ownership'] ?? 'Не вказано' ?></p>
      <p><strong>Підходить під іпотеку:</strong> <?= isset($property['mortgage_available']) ? ($property['mortgage_available'] ? 'Так' : 'Ні') : 'Не вказано' ?></p>
      <p><strong>Балкон / лоджія:</strong> <?= $property['balcony'] ?? ($property['balcony_terrace'] ?? 'Не вказано') ?></p>
    </div>

    <?php if (!empty($property['description'])): ?>
    <div class="section">
      <h4>Опис</h4>
      <p><?= nl2br(htmlspecialchars($property['description'])) ?></p>
    </div>
    <?php endif; ?>

    <div class="section gallery">
      <?php
      $photo_stmt = $conn->prepare("SELECT file_path FROM property_photos WHERE property_id = ?");
      $photo_stmt->bind_param("i", $property_id);
      $photo_stmt->execute();
      $photos = $photo_stmt->get_result();
      while ($photo = $photos->fetch_assoc()): ?>
        <img src="<?= htmlspecialchars($photo['file_path']) ?>" alt="Фото">
      <?php endwhile; ?>
    </div>
  </div>
</body>
</html>
<?php include 'footer.php'; ?>
