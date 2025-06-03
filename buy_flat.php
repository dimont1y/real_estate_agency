<?php
include 'connect_to_db.php';
include 'header.php';

$result = $conn->query("SELECT * FROM properties WHERE type_id = 1");
?>

<!DOCTYPE html>
<html lang="uk">
<head>
  <meta charset="UTF-8">
  <title>Купити квартиру</title>
  <link rel="stylesheet" href="style.css">
</head>

<body>
  <h2 style="text-align:center; margin-top:2rem;">Квартири на продаж</h2>
  <div class="container">
    <?php while ($row = $result->fetch_assoc()):
      $photo_stmt = $conn->prepare("SELECT file_path FROM property_photos WHERE property_id = ? LIMIT 1");
      $photo_stmt->bind_param("i", $row['property_id']);
      $photo_stmt->execute();
      $photo_result = $photo_stmt->get_result()->fetch_assoc();
      $main_photo = $photo_result ? $photo_result['file_path'] : 'placeholder.jpg';
    ?>
    <div class="card">
      <img src="<?= htmlspecialchars($main_photo) ?>" alt="Фото">
      <div class="info">
        <h3><?= htmlspecialchars($row['address']) ?></h3>
        <p><strong>Ціна:</strong> <?= number_format($row['price'], 0, '.', ' ') ?> $</p>
        <p><strong>Площа:</strong> <?= $row['area'] ?> м²</p>
        <p><strong>Кімнат:</strong> <?= $row['rooms'] ?></p>
        <p><strong>Поверх:</strong> <?= $row['floor'] ?></p>
        <a class="btn" href="property_details.php?id=<?= $row['property_id'] ?>">Детальніше</a>
      </div>
    </div>
    <?php endwhile; ?>
  </div>
</body>
</html>
<?php include 'footer.php'; ?>
