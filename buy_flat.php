<?php
include 'connect_to_db.php';
include 'header.php';

$type_id = 1;
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

if (!empty($_GET['floor'])) {
    if ($_GET['floor'] === '11+') {
        $conditions[] = "floor >= ?";
        $params[] = 11;
    } else {
        [$min, $max] = explode('-', $_GET['floor']);
        $conditions[] = "floor BETWEEN ? AND ?";
        $params[] = (int)$min;
        $params[] = (int)$max;
    }
}

if (!empty($_GET['area'])) {
    if ($_GET['area'] === '100+') {
        $conditions[] = "area >= ?";
        $params[] = 100;
    } else {
        [$min, $max] = explode('-', $_GET['area']);
        $conditions[] = "area BETWEEN ? AND ?";
        $params[] = (int)$min;
        $params[] = (int)$max;
    }
}

$sql = "SELECT * FROM properties WHERE " . implode(' AND ', $conditions);
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
    <title>Купити квартиру</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }
        h2 {
            text-align: center;
            margin-top: 2rem;
            margin-bottom: 2rem;
            color: #333;
            width: 100%;
        }
        .filter-section {
            flex: 1 1 300px;
            min-width: 250px;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
        }
        .filter-form {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .filter-form label {
            font-weight: bold;
            color: #555;
        }
        .filter-form select {
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #ccc;
            font-size: 1rem;
            width: 100%;
        }
        .filter-form button {
            padding: 10px;
            background-color: #a30000;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            align-self: flex-start;
        }
        .filter-form button:hover {
            background-color: #c82333;
        }
        .properties-grid {
            flex: 3 1 700px;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        .card {
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            background: #f0f0f0;
        }
        .info {
            padding: 15px;
        }
        .info h3 {
            margin: 0 0 10px;
            font-size: 1.2rem;
            color: #333;
        }
        .info p {
            margin: 5px 0;
            color: #666;
        }
        .info .btn {
            display: inline-block;
            margin-top: 10px;
            padding: 8px 15px;
            background-color: #a30000;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }
        .info .btn:hover {
            background-color: #c82333;
        }
        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }
            .filter-section {
                order: -1;
                width: 100%;
            }
            .properties-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Квартири на продаж</h2>

        <div class="filter-section">
            <form class="filter-form" method="GET" action="buy_flat.php">
                <label for="price">Ціна ($):</label>
                <select name="price" id="price">
                    <option value="">Будь-яка</option>
                    <option value="0-50000" <?= isset($_GET['price']) && $_GET['price'] === '0-50000' ? 'selected' : '' ?>>0 - 20,000</option>
                    <option value="20000-50000" <?= isset($_GET['price']) && $_GET['price'] === '0-50000' ? 'selected' : '' ?>>20,000 - 50,000</option>
                    <option value="50000-100000" <?= isset($_GET['price']) && $_GET['price'] === '50000-100000' ? 'selected' : '' ?>>50,000 - 100,000</option>
                    <option value="100000+" <?= isset($_GET['price']) && $_GET['price'] === '100000+' ? 'selected' : '' ?>>100,000+</option>
                </select>

                <label for="rooms">Кімнати:</label>
                <select name="rooms" id="rooms">
                    <option value="">Будь-яка</option>
                    <option value="1" <?= isset($_GET['rooms']) && $_GET['rooms'] === '1' ? 'selected' : '' ?>>1</option>
                    <option value="2" <?= isset($_GET['rooms']) && $_GET['rooms'] === '2' ? 'selected' : '' ?>>2</option>
                    <option value="3" <?= isset($_GET['rooms']) && $_GET['rooms'] === '3' ? 'selected' : '' ?>>3</option>
                    <option value="4+" <?= isset($_GET['rooms']) && $_GET['rooms'] === '4+' ? 'selected' : '' ?>>4+</option>
                </select>

                <label for="floor">Поверх:</label>
                <select name="floor" id="floor">
                    <option value="">Будь-який</option>
                    <option value="1-5" <?= isset($_GET['floor']) && $_GET['floor'] === '1-5' ? 'selected' : '' ?>>1-5</option>
                    <option value="6-10" <?= isset($_GET['floor']) && $_GET['floor'] === '6-10' ? 'selected' : '' ?>>6-10</option>
                    <option value="11+" <?= isset($_GET['floor']) && $_GET['floor'] === '11+' ? 'selected' : '' ?>>11+</option>
                </select>

                <label for="area">Площа (м²):</label>
                <select name="area" id="area">
                    <option value="">Будь-яка</option>
                    <option value="0-50" <?= isset($_GET['area']) && $_GET['area'] === '0-50' ? 'selected' : '' ?>>0-50</option>
                    <option value="50-100" <?= isset($_GET['area']) && $_GET['area'] === '50-100' ? 'selected' : '' ?>>50-100</option>
                    <option value="100+" <?= isset($_GET['area']) && $_GET['area'] === '100+' ? 'selected' : '' ?>>100+</option>
                </select>

                <button type="submit">Фільтрувати</button>
            </form>
        </div>

        <div class="properties-grid">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <?php
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
                            <p><strong>Площа:</strong> <?= htmlspecialchars($row['area']) ?> m²</p>
                            <p><strong>Кімнат:</strong> <?= htmlspecialchars($row['rooms']) ?></p>
                            <p><strong>Поверх:</strong> <?= htmlspecialchars($row['floor']) ?></p>
                            <a class="btn" href="property_details.php?id=<?= $row['property_id'] ?>">Детальніше</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="text-align: center; color: #666;">Квартири за вашими критеріями не знайдені.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
<?php include 'footer.php'; ?>