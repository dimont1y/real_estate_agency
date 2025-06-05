<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include 'connect_to_db.php';
include 'header.php';
$property_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Check if this flat belongs to the user
$stmt = $conn->prepare("SELECT * FROM properties WHERE property_id = ? AND type_id = 1 AND owner_id = ?");
$stmt->bind_param("ii", $property_id, $_SESSION['user_id']);
$stmt->execute();
$property = $stmt->get_result()->fetch_assoc();

if (!$property) {
    echo "<p style='text-align:center;'>Квартира не знайдена або не належить вам.</p>";
    exit();
}

// Get details from flat_details
$details_stmt = $conn->prepare("SELECT * FROM flat_details WHERE property_id = ?");
$details_stmt->bind_param("i", $property_id);
$details_stmt->execute();
$details = $details_stmt->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $address = trim($_POST['address']);
    $area = (float)$_POST['area'];
    $rooms = (int)$_POST['rooms'];
    $price = (float)$_POST['price'];
    $floor = (int)$_POST['floor'];
    $building_type = $_POST['building_type'];
    $build_year = (int)$_POST['build_year'];
    $elevators = (int)$_POST['elevators'];
    $heating = $_POST['heating'];
    $infrastructure = $_POST['infrastructure'];
    $renovation = $_POST['renovation'];
    $furnished = $_POST['furnished'];
    $appliances = $_POST['appliances'];
    $bathroom = $_POST['bathroom'];
    $bathroom_count = (int)$_POST['bathroom_count'];
    $internet_tv = isset($_POST['internet_tv']) ? 1 : 0;
    $security = $_POST['security'];
    $parking = $_POST['parking'];
    $ownership = $_POST['ownership'];
    $mortgage_available = isset($_POST['mortgage_available']) ? 1 : 0;
    $balcony = $_POST['balcony'];
    $description = trim($_POST['description']);

    // Update properties - removed extra comma
    $update_prop = $conn->prepare("UPDATE properties SET address = ?, area = ?, floor = ?, rooms = ?, price = ? WHERE property_id = ?");
    $update_prop->bind_param("sddisi", $address, $area, $floor, $rooms, $price, $property_id);
    $update_prop->execute();

    // Update flat_details
    $update_flat = $conn->prepare("UPDATE flat_details SET building_type = ?, build_year = ?, elevators = ?, heating = ?, infrastructure = ?, renovation = ?, furnished = ?, appliances = ?, bathroom = ?, bathroom_count = ?, internet_tv = ?, security = ?, parking = ?, ownership = ?, mortgage_available = ?, balcony = ?, description = ? WHERE property_id = ?");
    $update_flat->bind_param("isisssssssisissssi", $building_type, $build_year, $elevators, $heating, $infrastructure, $renovation, $furnished, $appliances, $bathroom, $bathroom_count, $internet_tv, $security, $parking, $ownership, $mortgage_available, $balcony, $description, $property_id);
    $update_flat->execute();

    header("Location: profile.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Редагування квартири</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
    <title>Редагувати квартиру</title>
    <form method="POST">
        <div class="form-section">
            <h3>Основні дані</h3>
            <label>Адреса:
                <input type="text" name="address" value="<?= htmlspecialchars($property['address'] ?? '') ?>" required>
            </label>
            <label>Площа (м²):
                <input type="number" step="0.1" name="area" value="<?= htmlspecialchars($property['area'] ?? '') ?>" required min="1">
            </label>
            <label>Кількість кімнат:
                <input type="number" name="rooms" value="<?= htmlspecialchars($property['rooms'] ?? '') ?>" required min="1">
            </label>
            <label>Ціна ($):
                <input type="number" step="0.01" name="price" value="<?= htmlspecialchars($property['price'] ?? '') ?>" required min="0">
            </label>
            <label>Поверх:
                <input type="number" name="floor" value="<?= htmlspecialchars($property['floor'] ?? '') ?>" required min="1">
            </label>
        </div>

        <div class="form-section">
            <h3>Основна інформація</h3>
            <label>Тип будинку:
                <select name="building_type" required>
                    <option value="Цегляний" <?= ($details['building_type'] ?? '') === 'Цегляний' ? 'selected' : '' ?>>Цегляний</option>
                    <option value="Панельний" <?= ($details['building_type'] ?? '') === 'Панельний' ? 'selected' : '' ?>>Панельний</option>
                    <option value="Моноліт" <?= ($details['building_type'] ?? '') === 'Моноліт' ? 'selected' : '' ?>>Моноліт</option>
                    <option value="Новобудова" <?= ($details['building_type'] ?? '') === 'Новобудова' ? 'selected' : '' ?>>Новобудова</option>
                </select>
            </label>
            <label>Рік будівництва / здачі:
                <input type="number" name="build_year" value="<?= htmlspecialchars($details['build_year'] ?? '') ?>">
            </label>
            <label>Ліфти (кількість):
                <input type="number" name="elevators" value="<?= htmlspecialchars($details['elevators'] ?? '') ?>">
            </label>
            <label>Опалення:
                <select name="heating">
                    <option value="Центральне" <?= ($details['heating'] ?? '') === 'Центральне' ? 'selected' : '' ?>>Центральне</option>
                    <option value="Автономне" <?= ($details['heating'] ?? '') === 'Автономне' ? 'selected' : '' ?>>Автономне</option>
                    <option value="Електричне" <?= ($details['heating'] ?? '') === 'Електричне' ? 'selected' : '' ?>>Електричне</option>
                </select>
            </label>
            <label>Інфраструктура:
                <textarea name="infrastructure"><?= htmlspecialchars($details['infrastructure'] ?? '') ?></textarea>
            </label>
            <label>Стан ремонту:
                <select name="renovation">
                    <option value="Євроремонт" <?= ($details['renovation'] ?? '') === 'Євроремонт' ? 'selected' : '' ?>>Євроремонт</option>
                    <option value="Косметичний" <?= ($details['renovation'] ?? '') === 'Косметичний' ? 'selected' : '' ?>>Косметичний</option>
                    <option value="Без ремонту" <?= ($details['renovation'] ?? '') === 'Без ремонту' ? 'selected' : '' ?>>Без ремонту</option>
                </select>
            </label>
            <label>Меблі:
                <select name="furnished">
                    <option value="Повністю мебльована" <?= ($details['furnished'] ?? '') === 'Повністю мебльована' ? 'selected' : '' ?>>Повністю мебльована</option>
                    <option value="Частково мебльована" <?= ($details['furnished'] ?? '') === 'Частково мебльована' ? 'selected' : '' ?>>Частково мебльована</option>
                    <option value="Без меблів" <?= ($details['furnished'] ?? '') === 'Без меблів' ? 'selected' : '' ?>>Без меблів</option>
                </select>
            </label>
            <label>Побутова техніка:
                <input type="text" name="appliances" value="<?= htmlspecialchars($details['appliances'] ?? '') ?>">
            </label>
            <label>Санвузол:
                <select name="bathroom">
                    <option value="Суміщений" <?= ($details['bathroom'] ?? '') === 'Суміщений' ? 'selected' : '' ?>>Суміщений</option>
                    <option value="Роздільний" <?= ($details['bathroom'] ?? '') === 'Роздільний' ? 'selected' : '' ?>>Роздільний</option>
                </select>
            </label>
            <label>Кількість санвузлів:
                <input type="number" name="bathroom_count" value="<?= htmlspecialchars($details['bathroom_count'] ?? '') ?>" min="1">
            </label>
        </div>

        <div class="form-section">
            <h3>Додаткові опції</h3>
            <label>Інтернет / TV:
                <select name="internet_tv">
                    <option value="1" <?= ($details['internet_tv'] ?? 0) == 1 ? 'selected' : '' ?>>Підключено</option>
                    <option value="0" <?= ($details['internet_tv'] ?? 0) == 0 ? 'selected' : '' ?>>Не підключено</option>
                </select>
            </label>
            <label>Безпека:
                <input type="text" name="security" value="<?= htmlspecialchars($details['security'] ?? '') ?>">
            </label>
            <label>Паркінг:
                <input type="text" name="parking" value="<?= htmlspecialchars($details['parking'] ?? '') ?>">
            </label>
            <label>Тип власності:
                <input type="text" name="ownership" value="<?= htmlspecialchars($details['ownership'] ?? '') ?>">
            </label>
            <label>Підходить під іпотеку:
                <select name="mortgage_available">
                    <option value="1" <?= ($details['mortgage_available'] ?? 0) == 1 ? 'selected' : '' ?>>Так</option>
                    <option value="0" <?= ($details['mortgage_available'] ?? 0) == 0 ? 'selected' : '' ?>>Ні</option>
                </select>
            </label>
            <label>Балкон/лоджія:
                <input type="text" name="balcony" value="<?= htmlspecialchars($details['balcony'] ?? '') ?>">
            </label>
        </div>

        <div class="form-section">
            <label>Опис:
                <textarea name="description" rows="4" placeholder="Додайте опис квартири..."><?= htmlspecialchars($property['description'] ?? '') ?></textarea>
            </label>
        </div>

        <button type="submit">Зберегти зміни</button>
    </form>
</div>
    </form>
</div>
</body>
</html>
<?php include 'footer.php'; ?>