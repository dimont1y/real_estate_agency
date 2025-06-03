<?php
include 'connect_to_db.php';
include 'header.php';

$type_id = 2;

$conditions = ["type_id = $type_id"];
$params = [];



if (!empty($_GET['price'])) {
    if ($_GET['price'] === '100000+') {
        $conditions[] = "price >= 100000";
    } else {
        [$min, $max] = explode('-', $_GET['price']);
        $conditions[] = "price BETWEEN ? AND ?";
        $params[] = (int)$min;
        $params[] = (int)$max;
    }
}

if (!empty($_GET['rooms'])) {
    if ($_GET['rooms'] === '4+') {
        $conditions[] = "rooms >= 4";
    } else {
        $conditions[] = "rooms = ?";
        $params[] = (int)$_GET['rooms'];
    }
}

$sql = "SELECT * FROM properties WHERE " . implode(' AND ', $conditions);
$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $types = '';
    foreach ($params as $param) {
        $types .= is_int($param) ? 'i' : (is_float($param) ? 'd' : 's');
    }
 
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="uk">
<head>
  <meta charset="UTF-8">
  <title>Купити будинок</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

<h2 style="text-align:center;">Доступні будинки</h2>

<?php while ($row = mysqli_fetch_assoc($result)): ?>
  <?php
    $property_id = $row['property_id'];
    $photos_query = mysqli_prepare($conn, "SELECT file_path FROM property_photos WHERE property_id = ?");
    mysqli_stmt_bind_param($photos_query, "i", $property_id);
    mysqli_stmt_execute($photos_query);
    $photos_result = mysqli_stmt_get_result($photos_query);
  ?>
  <div class="property">
    <h3><?= htmlspecialchars($row['address']) ?></h3>
    <div class="details">
      Площа: <?= $row['area'] ?> м²<br>
      Поверх: <?= $row['floor'] ?><br>
      Кімнати: <?= $row['rooms'] ?>
    </div>
    <div class="gallery">
      <?php while ($photo = mysqli_fetch_assoc($photos_result)): ?>
        <img src="<?= htmlspecialchars($photo['file_path']) ?>" alt="Фото нерухомості">
      <?php endwhile; ?>
    </div>
  </div>
<?php endwhile; ?>

</body>
</html>
<?php include 'footer.php'; ?>
