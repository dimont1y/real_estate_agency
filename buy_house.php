<?php
include 'connect_to_db.php';
include 'header.php';

$type_id = 2;
$conditions = ["type_id = ?"];
$params = [$type_id];

if (!empty($_GET['price'])) {
    if ($_GET['price'] === '100000+') {
        $conditions[] = "price >= ?";
        $params[] = 100000;
    } else {
        [$min, $max] = explode('-', $_GET['price']);
        $conditions[] = "price BETWEEN ? AND ?";
        $params[] = (int)$min;
        $params[] = (int)$max;
    }
}

if (!empty($_GET['rooms'])) {
    if ($_GET['rooms'] === '4+') {
        $conditions[] = "rooms >= ?";
        $params[] = 4;
    } else {
        $conditions[] = "rooms = ?";
        $params[] = (int)$_GET['rooms'];
    }
}

$sql = "SELECT * FROM properties WHERE " . implode(' AND ', $conditions) . " ORDER BY is_top DESC, property_id DESC";
$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $types = str_repeat('i', count($params)); 
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Купити будинок</title>
    <link rel="stylesheet" href="style.css">
    <style>
        
        .buy-house-container {
            max-width: 1200px !important;
            margin: 2rem auto;
            padding: 0 1rem;
            display: block; 
        }
        .filter-form {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            justify-content: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.08);
        }
        .filter-form label {
            margin: 0 10px 0 0;
            font-weight: bold;
            align-self: center;
        }
        .filter-form select {
            padding: 8px;
            border-radius: 6px;
            border: 1px solid #ccc;
            min-width: 150px;
        }
        .filter-form button {
            padding: 8px 15px;
            background-color: #a30000;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            align-self: center;
        }
        .filter-form button:hover {
            background-color: #630000;
        }
        .properties-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        .property-card {
            display: flex;
            flex-direction: column;
            height: 100%; 
        }
        .property-card .gallery {
            display: flex;
            gap: 10px;
            overflow-x: auto;
            padding: 10px 0;
            background: #f0f0f0;
            min-height: 120px; 
            align-items: center;
            justify-content: center;
        }
        .property-card .gallery img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 6px;
            flex-shrink: 0;
        }
        .property-card .info {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }
        .property-card .info .btn {
            margin-top: auto; 
        }
        .no-properties {
            text-align: center;
            color: #666;
            padding: 20px;
            grid-column: 1 / -1;
        }
        @media (max-width: 768px) {
            .filter-form {
                flex-direction: column;
                align-items: center;
            }
            .filter-form select, .filter-form button {
                width: 100%;
                max-width: 300px;
            }
        }
        .property-card.highlighted {
            background: #fffde7 !important;
            border: 2px solid #ffc107;
            box-shadow: 0 0 10px #ffe082;
        }
    </style>
</head>
<body>
<main>
    <div class="buy-house-container">
        <h2>Доступні будинки</h2>

        <form class="filter-form" method="GET" action="">
            <label for="price">Ціна:</label>
            <select name="price" id="price">
                <option value="">Виберіть діапазон</option>
                <option value="0-50000" <?= isset($_GET['price']) && $_GET['price'] === '0-50000' ? 'selected' : '' ?>>0 - 50,000 $</option>
                <option value="50000-100000" <?= isset($_GET['price']) && $_GET['price'] === '50000-100000' ? 'selected' : '' ?>>50,000 - 100,000 $</option>
                <option value="100000+" <?= isset($_GET['price']) && $_GET['price'] === '100000+' ? 'selected' : '' ?>>100,000+ $</option>
            </select>

            <label for="rooms">Кімнати:</label>
            <select name="rooms" id="rooms">
                <option value="">Виберіть кількість</option>
                <option value="1" <?= isset($_GET['rooms']) && $_GET['rooms'] === '1' ? 'selected' : '' ?>>1</option>
                <option value="2" <?= isset($_GET['rooms']) && $_GET['rooms'] === '2' ? 'selected' : '' ?>>2</option>
                <option value="3" <?= isset($_GET['rooms']) && $_GET['rooms'] === '3' ? 'selected' : '' ?>>3</option>
                <option value="4+" <?= isset($_GET['rooms']) && $_GET['rooms'] === '4+' ? 'selected' : '' ?>>4+</option>
            </select>

            <button type="submit">Фільтрувати</button>
        </form>

        <div class="properties-grid">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <?php
                    $property_id = $row['property_id'];
                    $photos_stmt = $conn->prepare("SELECT file_path FROM property_photos WHERE property_id = ?");
                    $photos_stmt->bind_param("i", $property_id);
                    $photos_stmt->execute();
                    $photos_result = $photos_stmt->get_result();
                    ?>
                    <div class="property-card card<?= ($row['is_highlighted'] ?? 0) ? ' highlighted' : '' ?>">
                        <div class="gallery">
                            <?php if ($photos_result->num_rows > 0): ?>
                                <?php while ($photo = $photos_result->fetch_assoc()): ?>
                                    <img src="<?= htmlspecialchars($photo['file_path']) ?>" alt="Фото нерухомості">
                                <?php endwhile; ?>
                            <?php else: ?>
                                <p style="text-align: center; padding: 10px; margin: 0;">Фото відсутні</p>
                            <?php endif; ?>
                        </div>
                        <div class="info">
                            <h3><?= htmlspecialchars($row['address']) ?></h3>
                            <p>Площа: <?= htmlspecialchars($row['area']) ?> м²</p>
                            <p>Поверх: <?= htmlspecialchars($row['floor']) ?></p>
                            <p>Кімнати: <?= htmlspecialchars($row['rooms']) ?></p>
                            <p>Ціна: <?= number_format($row['price'], 0, '.', ' ') ?> $</p>
                            <a href="property_details.php?id=<?= $row['property_id'] ?>" class="btn">Деталі</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="no-properties">Немає доступних будинків за вибраними фільтрами.</p>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include 'footer.php'; ?>
</body>
</html>