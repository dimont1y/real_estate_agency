<?php
$mode = 'guest';
include 'connect_to_db.php';

$address = $_GET['address'] ?? '';
$rooms = $_GET['rooms'] ?? '';
$floor = $_GET['floor'] ?? '';
$area_from = $_GET['area_from'] ?? '';
$area_to = $_GET['area_to'] ?? '';
$type_id = $_GET['type_id'] ?? '';

$sql = "SELECT * FROM properties WHERE 1=1";
$params = [];

if (!empty($address)) {
    $sql .= " AND address LIKE ?";
    $params[] = '%' . $address . '%';
}
if (!empty($rooms)) {
    $sql .= " AND rooms = ?";
    $params[] = $rooms;
}
if (!empty($floor)) {
    $sql .= " AND floor = ?";
    $params[] = $floor;
}
if (!empty($area_from)) {
    $sql .= " AND area >= ?";
    $params[] = $area_from;
}
if (!empty($area_to)) {
    $sql .= " AND area <= ?";
    $params[] = $area_to;
}
if (!empty($type_id)) {
    $sql .= " AND type_id = ?";
    $params[] = $type_id;
}

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $types = str_repeat('s', count($params));
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Пошук нерухомості</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<h2 style="text-align:center;">Пошук нерухомості</h2>

<form method="get">
    <div class="form-group">
        <label>Адреса:</label>
        <input type="text" name="address" value="<?= htmlspecialchars($address) ?>">
    </div>
    <div class="form-group">
        <label>Кімнат:</label>
        <input type="number" name="rooms" value="<?= htmlspecialchars($rooms) ?>">
    </div>
    <div class="form-group">
        <label>Поверх:</label>
        <input type="number" name="floor" value="<?= htmlspecialchars($floor) ?>">
    </div>
    <div class="form-group">
        <label>Площа від:</label>
        <input type="number" step="0.1" name="area_from" value="<?= htmlspecialchars($area_from) ?>">
        <label style="width:auto;">до:</label>
        <input type="number" step="0.1" name="area_to" value="<?= htmlspecialchars($area_to) ?>">
    </div>
    <div class="form-group">
        <label>Тип (type_id):</label>
        <input type="number" name="type_id" value="<?= htmlspecialchars($type_id) ?>">
    </div>
    <button type="submit">Знайти</button>
</form>

<?php if ($result->num_rows > 0): ?>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Адреса</th>
                <th>Площа</th>
                <th>Поверх</th>
                <th>Кімнат</th>
                <th>Тип</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['property_id'] ?></td>
                <td><?= htmlspecialchars($row['address']) ?></td>
                <td><?= $row['area'] ?> м²</td>
                <td><?= $row['floor'] ?></td>
                <td><?= $row['rooms'] ?></td>
                <td><?= $row['type_id'] ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
<?php else: ?>
    <p style="text-align:center;">Нічого не знайдено.</p>
<?php endif; ?>

</body>
</html>
