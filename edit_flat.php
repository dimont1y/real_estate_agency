<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include 'connect_to_db.php';
include 'header.php';
$property_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Check if user is admin
if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
    $stmt = $conn->prepare("SELECT * FROM properties WHERE property_id = ? AND type_id = 1");
    $stmt->bind_param("i", $property_id);
} else {
    $stmt = $conn->prepare("SELECT * FROM properties WHERE property_id = ? AND type_id = 1 AND owner_id = ?");
    $stmt->bind_param("ii", $property_id, $_SESSION['user_id']);
}
$stmt->execute();
$property = $stmt->get_result()->fetch_assoc();

if (!$property) {
    echo "<p style='text-align:center;'>Квартира не знайдена або не належить вам.</p>";
    exit();
}

$details_stmt = $conn->prepare("SELECT * FROM flat_details WHERE property_id = ?");
$details_stmt->bind_param("i", $property_id);
$details_stmt->execute();
$details = $details_stmt->get_result()->fetch_assoc();

$photos_stmt = $conn->prepare("SELECT photo_id, file_path FROM property_photos WHERE property_id = ?");
$photos_stmt->bind_param("i", $property_id);
$photos_stmt->execute();
$photos_result = $photos_stmt->get_result();
$photos = [];
while ($photo = $photos_result->fetch_assoc()) {
    $photos[] = $photo;
}

$errors = [];

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

    if ($address === '') {
        $errors[] = "Адреса є обов'язковою.";
    }
    if ($area <= 0) {
        $errors[] = "Площа повинна бути більше 0.";
    }
    if ($rooms < 1) {
        $errors[] = "Кількість кімнат повинна бути не менше 1.";
    }
    if ($price <= 0) {
        $errors[] = "Ціна повинна бути більше 0.";
    }
    if ($floor < 1) {
        $errors[] = "Поверх для квартири повинен бути не менше 1.";
    }
    if ($building_type === '') {
        $errors[] = "Виберіть тип будинку.";
    }
    if ($heating === '') {
        $errors[] = "Виберіть тип опалення.";
    }
    if ($renovation === '') {
        $errors[] = "Виберіть стан ремонту.";
    }
    if ($furnished === '') {
        $errors[] = "Виберіть стан меблювання.";
    }
    if ($bathroom === '') {
        $errors[] = "Виберіть тип санвузла.";
    }
    if ($bathroom_count < 1) {
        $errors[] = "Кількість санвузлів повинна бути не менше 1.";
    }

    if (empty($errors) && isset($_POST['delete_photos']) && is_array($_POST['delete_photos'])) {
        foreach ($_POST['delete_photos'] as $photo_id) {
            $photo_id = (int)$photo_id;
            $delete_stmt = $conn->prepare("SELECT file_path FROM property_photos WHERE photo_id = ? AND property_id = ?");
            $delete_stmt->bind_param("ii", $photo_id, $property_id);
            $delete_stmt->execute();
            $photo = $delete_stmt->get_result()->fetch_assoc();

            if ($photo) {
                if (file_exists($photo['file_path'])) {
                    unlink($photo['file_path']);
                }
                $delete_photo = $conn->prepare("DELETE FROM property_photos WHERE photo_id = ?");
                $delete_photo->bind_param("i", $photo_id);
                $delete_photo->execute();
            }
        }
    }

    if (empty($errors) && !empty($_FILES['photos']['name'][0])) {
        $upload_dir = "pics/flat/$property_id/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $remaining_photos = $photos_stmt->execute();
        $remaining_photos = $photos_stmt->get_result()->num_rows;

        $new_photos_count = count(array_filter($_FILES['photos']['name']));
        if (($remaining_photos - count($_POST['delete_photos'] ?? []) + $new_photos_count) < 5) {
            $errors[] = "Кількість фотографій після змін повинна бути не менше 5.";
        } elseif ($new_photos_count > 20) {
            $errors[] = "Максимальна кількість нових фотографій — 20.";
        } else {
            foreach ($_FILES['photos']['name'] as $key => $name) {
                if ($_FILES['photos']['error'][$key] === UPLOAD_ERR_OK) {
                    $tmp_name = $_FILES['photos']['tmp_name'][$key];
                    $file_name = uniqid() . '_' . basename($name);
                    $file_path = $upload_dir . $file_name;

                    if (move_uploaded_file($tmp_name, $file_path)) {
                        $photo_stmt = $conn->prepare("INSERT INTO property_photos (property_id, file_path) VALUES (?, ?)");
                        $photo_stmt->bind_param("is", $property_id, $file_path);
                        $photo_stmt->execute();
                    } else {
                        $errors[] = "Не вдалося завантажити фото: $name.";
                    }
                }
            }
        }
    }

    if (empty($errors)) {
        $update_prop = $conn->prepare("UPDATE properties SET address = ?, area = ?, floor = ?, rooms = ?, price = ? WHERE property_id = ?");
        $update_prop->bind_param("sddisi", $address, $area, $floor, $rooms, $price, $property_id);
        $update_prop->execute();

        $update_flat = $conn->prepare("UPDATE flat_details SET building_type = ?, build_year = ?, elevators = ?, heating = ?, infrastructure = ?, renovation = ?, furnished = ?, appliances = ?, bathroom = ?, bathroom_count = ?, internet_tv = ?, security = ?, parking = ?, ownership = ?, mortgage_available = ?, balcony = ?, description = ? WHERE property_id = ?");
        $update_flat->bind_param("siisssssssisissssi", $building_type, $build_year, $elevators, $heating, $infrastructure, $renovation, $furnished, $appliances, $bathroom, $bathroom_count, $internet_tv, $security, $parking, $ownership, $mortgage_available, $balcony, $description, $property_id);
        $update_flat->execute();

        header("Location: profile.php");
        exit();
    } else {
        echo "<p style='color:red; text-align:center;'>Помилки:<br>" . implode("<br>", array_map('htmlspecialchars', $errors)) . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Редагування квартири</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .photo-preview {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }
        .photo-preview label {
            position: relative;
            display: inline-block;
        }
        .photo-preview img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border: 1px solid #ccc;
        }
        .photo-preview input[type="checkbox"] {
            position: absolute;
            top: 5px;
            left: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Редагувати квартиру</h2>
        <form method="POST" enctype="multipart/form-data">
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
                    <select name="heating" required>
                        <option value="Центральне" <?= ($details['heating'] ?? '') === 'Центральне' ? 'selected' : '' ?>>Центральне</option>
                        <option value="Автономне" <?= ($details['heating'] ?? '') === 'Автономне' ? 'selected' : '' ?>>Автономне</option>
                        <option value="Електричне" <?= ($details['heating'] ?? '') === 'Електричне' ? 'selected' : '' ?>>Електричне</option>
                    </select>
                </label>
                <label>Інфраструктура:
                    <textarea name="infrastructure"><?= htmlspecialchars($details['infrastructure'] ?? '') ?></textarea>
                </label>
                <label>Стан ремонту:
                    <select name="renovation" required>
                        <option value="Євроремонт" <?= ($details['renovation'] ?? '') === 'Євроремонт' ? 'selected' : '' ?>>Євроремонт</option>
                        <option value="Косметичний" <?= ($details['renovation'] ?? '') === 'Косметичний' ? 'selected' : '' ?>>Косметичний</option>
                        <option value="Без ремонту" <?= ($details['renovation'] ?? '') === 'Без ремонту' ? 'selected' : '' ?>>Без ремонту</option>
                    </select>
                </label>
                <label>Меблі:
                    <select name="furnished" required>
                        <option value="Повністю мебльована" <?= ($details['furnished'] ?? '') === 'Повністю мебльована' ? 'selected' : '' ?>>Повністю мебльована</option>
                        <option value="Частково мебльована" <?= ($details['furnished'] ?? '') === 'Частково мебльована' ? 'selected' : '' ?>>Частково мебльована</option>
                        <option value="Без меблів" <?= ($details['furnished'] ?? '') === 'Без меблів' ? 'selected' : '' ?>>Без меблів</option>
                    </select>
                </label>
                <label>Побутова техніка:
                    <select name="appliances" required>
                        <option value="Повний комплект" <?= ($details['appliances'] ?? '') === 'Повний комплект' ? 'selected' : '' ?>>Повний комплект</option>
                        <option value="Частковий комплект" <?= ($details['appliances'] ?? '') === 'Частковий комплект' ? 'selected' : '' ?>>Частковий комплект</option>
                        <option value="Немає" <?= ($details['appliances'] ?? '') === 'Немає' ? 'selected' : '' ?>>Немає</option>
                    </select>
                </label>
                <label>Санвузол:
                    <select name="bathroom" required>
                        <option value="Суміщений" <?= ($details['bathroom'] ?? '') === 'Суміщений' ? 'selected' : '' ?>>Суміщений</option>
                        <option value="Роздільний" <?= ($details['bathroom'] ?? '') === 'Роздільний' ? 'selected' : '' ?>>Роздільний</option>
                    </select>
                </label>
                <label>Кількість санвузлів:
                    <input type="number" name="bathroom_count" value="<?= htmlspecialchars($details['bathroom_count'] ?? '') ?>" required min="1">
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
                    <select name="security" required>
                        <option value="Домофон" <?= ($details['security'] ?? '') === 'Домофон' ? 'selected' : '' ?>>Домофон</option>
                        <option value="Відеоспостереження" <?= ($details['security'] ?? '') === 'Відеоспостереження' ? 'selected' : '' ?>>Відеоспостереження</option>
                        <option value="Охорона" <?= ($details['security'] ?? '') === 'Охорона' ? 'selected' : '' ?>>Охорона</option>
                        <option value="Немає" <?= ($details['security'] ?? '') === 'Немає' ? 'selected' : '' ?>>Немає</option>
                    </select>
                </label>
                <label>Паркінг:
                    <select name="parking" required>
                        <option value="Підземний" <?= ($details['parking'] ?? '') === 'Підземний' ? 'selected' : '' ?>>Підземний</option>
                        <option value="Наземний" <?= ($details['parking'] ?? '') === 'Наземний' ? 'selected' : '' ?>>Наземний</option>
                        <option value="Гараж" <?= ($details['parking'] ?? '') === 'Гараж' ? 'selected' : '' ?>>Гараж</option>
                        <option value="Немає" <?= ($details['parking'] ?? '') === 'Немає' ? 'selected' : '' ?>>Немає</option>
                    </select>
                </label>
                <label>Тип власності:
                    <select name="ownership" required>
                        <option value="Приватна" <?= ($details['ownership'] ?? '') === 'Приватна' ? 'selected' : '' ?>>Приватна</option>
                        <option value="Кооперативна" <?= ($details['ownership'] ?? '') === 'Кооперативна' ? 'selected' : '' ?>>Кооперативна</option>
                        <option value="Державна" <?= ($details['ownership'] ?? '') === 'Державна' ? 'selected' : '' ?>>Державна</option>
                    </select>
                </label>
                <label>Підходить під іпотеку:
                    <select name="mortgage_available">
                        <option value="1" <?= ($details['mortgage_available'] ?? 0) == 1 ? 'selected' : '' ?>>Так</option>
                        <option value="0" <?= ($details['mortgage_available'] ?? 0) == 0 ? 'selected' : '' ?>>Ні</option>
                    </select>
                </label>
                <label>Балкон/лоджія:
                    <select name="balcony" required>
                        <option value="Балкон" <?= ($details['balcony'] ?? '') === 'Балкон' ? 'selected' : '' ?>>Балкон</option>
                        <option value="Лоджія" <?= ($details['balcony'] ?? '') === 'Лоджія' ? 'selected' : '' ?>>Лоджія</option>
                        <option value="Балкон і лоджія" <?= ($details['balcony'] ?? '') === 'Балкон і лоджія' ? 'selected' : '' ?>>Балкон і лоджія</option>
                        <option value="Немає" <?= ($details['balcony'] ?? '') === 'Немає' ? 'selected' : '' ?>>Немає</option>
                    </select>
                </label>
            </div>

            <div class="form-section">
                <h3>Фотографії</h3>
                <?php if (!empty($photos)): ?>
                    <div class="photo-preview">
                        <h4>Поточні фотографії:</h4>
                        <?php foreach ($photos as $photo): ?>
                            <label>
                                <input type="checkbox" name="delete_photos[]" value="<?php echo htmlspecialchars($photo['photo_id']); ?>">
                                <img src="<?php echo htmlspecialchars($photo['file_path']); ?>" alt="Фото квартири">
                            </label>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <label>Завантажити нові фотографії:
                    <input type="file" name="photos[]" multiple accept="image/*">
                </label>
            </div>

            <div class="form-section">
                <label>Опис:
                    <textarea name="description" rows="4" placeholder="Додайте опис квартири..."><?= htmlspecialchars($details['description'] ?? '') ?></textarea>
                </label>
            </div>

            <button type="submit">Зберегти зміни</button>
        </form>
    </div>
</body>
</html>
<?php include 'footer.php'; ?>