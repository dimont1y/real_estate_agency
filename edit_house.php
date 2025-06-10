<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'connect_to_db.php';
include 'header.php';
$property_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
    $stmt = $conn->prepare("SELECT * FROM properties WHERE property_id = ? AND type_id = 2");
    $stmt->bind_param("i", $property_id);
} else {
    $stmt = $conn->prepare("SELECT * FROM properties WHERE property_id = ? AND type_id = 2 AND owner_id = ?");
    $stmt->bind_param("ii", $property_id, $_SESSION['user_id']);
}
$stmt->execute();
$property = $stmt->get_result()->fetch_assoc();

if (!$property) {
    echo "<p style='text-align:center;'>Будинок не знайдено або не належить вам.</p>";
    exit();
}

$details_stmt = $conn->prepare("SELECT * FROM house_details WHERE property_id = ?");
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
    $floors = (int)$_POST['floors'];
    $building_type = $_POST['building_type'] ?? '';
    $build_year = (int)$_POST['build_year'] ?? 0;
    $total_area = (float)$_POST['total_area'] ?? 0;
    $living_area = (float)$_POST['living_area'] ?? 0;
    $land_area = (float)$_POST['land_area'] ?? 0;
    $sewerage = $_POST['sewerage'] ?? '';
    $water_supply = $_POST['water_supply'] ?? '';
    $heating = $_POST['heating'] ?? '';
    $garage = $_POST['garage'] ?? '';
    $outbuildings = $_POST['outbuildings'] ?? '';
    $infrastructure = $_POST['infrastructure'] ?? '';
    $renovation = $_POST['renovation'] ?? '';
    $furnished = $_POST['furnished'] ?? '';
    $appliances = $_POST['appliances'] ?? '';
    $bathroom = $_POST['bathroom'] ?? '';
    $bathroom_location = $_POST['bathroom_location'] ?? '';
    $balcony_terrace = $_POST['balcony_terrace'] ?? '';
    $internet_tv = $_POST['internet_tv'] ?? '';
    $security = $_POST['security'] ?? '';
    $ownership = $_POST['ownership'] ?? '';
    $mortgage_available = $_POST['mortgage_available'] ?? '';
    $purpose = $_POST['purpose'] ?? '';
    $fence = $_POST['fence'] ?? '';
    $distance_to_city = (float)$_POST['distance_to_city'] ?? 0;
    $description = trim($_POST['description']);


    if ($address === '') $errors[] = "Адреса є обов'язковою.";
    if ($area <= 0) $errors[] = "Площа повинна бути більше 0.";
    if ($rooms < 1) $errors[] = "Кількість кімнат повинна бути не менше 1.";
    if ($price <= 0) $errors[] = "Ціна повинна бути більше 0.";
    if ($floors < 1) $errors[] = "Кількість поверхів повинна бути не менше 1.";
    if ($total_area <= 0) $errors[] = "Загальна площа повинна бути більше 0.";
    if ($living_area <= 0) $errors[] = "Житлова площа повинна бути більше 0.";
    if ($land_area <= 0) $errors[] = "Площа ділянки повинна бути більше 0.";
    if ($sewerage === '') $errors[] = "Виберіть каналізацію.";
    if ($water_supply === '') $errors[] = "Виберіть водопостачання.";
    if ($heating === '') $errors[] = "Виберіть тип опалення.";
    if ($garage === '') $errors[] = "Виберіть тип гаража.";
    if ($balcony_terrace === '') $errors[] = "Виберіть балкон / терасу.";
    if ($ownership === '') $errors[] = "Виберіть тип власності.";
    if ($mortgage_available === '') $errors[] = "Вкажіть іпотеку.";
    if ($purpose === '') $errors[] = "Вкажіть призначення.";
    if ($description === '') $errors[] = "Опис є обов'язковим.";


    if (empty($errors) && isset($_POST['delete_photos']) && is_array($_POST['delete_photos'])) {
        foreach ($_POST['delete_photos'] as $photo_id) {
            $photo_id = (int)$photo_id;
            $delete_stmt = $conn->prepare("SELECT file_path FROM property_photos WHERE photo_id = ? AND property_id = ?");
            $delete_stmt->bind_param("ii", $photo_id, $property_id);
            $delete_stmt->execute();
            $photo = $delete_stmt->get_result()->fetch_assoc();

            if ($photo && file_exists($photo['file_path'])) {
                unlink($photo['file_path']);
                $delete_photo = $conn->prepare("DELETE FROM property_photos WHERE photo_id = ?");
                $delete_photo->bind_param("i", $photo_id);
                $delete_photo->execute();
            }
        }
    }

    if (empty($errors) && !empty($_FILES['photos']['name'][0])) {
        $upload_dir = "pics/houses/$property_id/";
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

        $photos_stmt->execute();
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
        $update_prop->bind_param("sddidi", $address, $area, $floors, $rooms, $price, $property_id);
        $update_prop->execute();

        $update_house = $conn->prepare("UPDATE house_details SET building_type = ?, build_year = ?, floors = ?, total_area = ?, living_area = ?, land_area = ?, sewerage = ?, water_supply = ?, heating = ?, garage = ?, outbuildings = ?, infrastructure = ?, renovation = ?, furnished = ?, appliances = ?, bathroom = ?, bathroom_location = ?, balcony_terrace = ?, internet_tv = ?, security = ?, ownership = ?, mortgage_available = ?, purpose = ?, fence = ?, distance_to_city = ?, description = ? WHERE property_id = ?");
        $update_house->bind_param("siidddsssssssssssssssssssdi", $building_type, $build_year, $floors, $total_area, $living_area, $land_area, $sewerage, $water_supply, $heating, $garage, $outbuildings, $infrastructure, $renovation, $furnished, $appliances, $bathroom, $bathroom_location, $balcony_terrace, $internet_tv, $security, $ownership, $mortgage_available, $purpose, $fence, $distance_to_city, $description, $property_id);
        $update_house->execute();

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
    <title>Редагування будинку</title>
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
        <h2>Редагувати будинок</h2>
        <form method="POST" enctype="multipart/form-data">
            <label>Адреса:
                <input type="text" name="address" value="<?= htmlspecialchars($property['address']) ?>" required>
            </label>

            <label>Площа (м²):
                <input type="number" name="area" step="0.1" value="<?= htmlspecialchars($property['area']) ?>" required>
            </label>

            <label>Кількість кімнат:
                <input type="number" name="rooms" value="<?= htmlspecialchars($property['rooms']) ?>" required min="1">
            </label>

            <label>Ціна ($):
                <input type="number" name="price" step="0.01" value="<?= htmlspecialchars($property['price']) ?>" required>
            </label>

            <div class="form-section">
                <h3>Основна інформація</h3>
                <label>Кількість поверхів:
                    <input type="number" name="floors" min="1" required value="<?= htmlspecialchars($_POST['floors'] ?? '') ?>">
                </label>

                <label>Тип будинку:
                    <select name="building_type" required>
                        <option value="Цегляний" <?= ($details['building_type'] ?? '') === 'Цегляний' ? 'selected' : '' ?>>Цегляний</option>
                        <option value="Панельний" <?= ($details['building_type'] ?? '') === 'Панельний' ? 'selected' : '' ?>>Панельний</option>
                        <option value="Моноліт" <?= ($details['building_type'] ?? '') === 'Моноліт' ? 'selected' : '' ?>>Моноліт</option>
                        <option value="Новобудова" <?= ($details['building_type'] ?? '') === 'Новобудова' ? 'selected' : '' ?>>Новобудова</option>
                    </select>
                </label>

                <label>Рік будівництва / здачі:
                    <input type="number" name="build_year">
                </label>

                <label>Загальна площа (м²):
                    <input type="number" step="0.1" name="total_area" value="<?= htmlspecialchars($_POST['total_area'] ?? '') ?>" required>
                </label>

                <label>Житлова площа (м²):
                    <input type="number" step="0.1" name="living_area" value="<?= htmlspecialchars($_POST['living_area'] ?? '') ?>" required>
                </label>

                <label>Площа ділянки (сотки):
                    <input type="number" step="0.1" name="land_area" value="<?= htmlspecialchars($_POST['land_area'] ?? '') ?>" required>
                </label>

                <label>Каналізація:
                    <select name="sewerage" required>
                        <option value="Центральна" <?= ($_POST['sewerage'] ?? '') == 'Центральна' ? 'selected' : '' ?>>Центральна</option>
                        <option value="Автономна" <?= ($_POST['sewerage'] ?? '') == 'Автономна' ? 'selected' : '' ?>>Автономна</option>
                        <option value="Немає" <?= ($_POST['sewerage'] ?? '') == 'Немає' ? 'selected' : '' ?>>Немає</option>
                    </select>
                </label>

                <label>Водопостачання:
                    <select name="water_supply" required>
                        <option value="Центральне" <?= ($_POST['water_supply'] ?? '') == 'Центральне' ? 'selected' : '' ?>>Центральне</option>
                        <option value="Свердловина" <?= ($_POST['water_supply'] ?? '') == 'Свердловина' ? 'selected' : '' ?>>Свердловина</option>
                        <option value="Колодязь" <?= ($_POST['water_supply'] ?? '') == 'Колодязь' ? 'selected' : '' ?>>Колодязь</option>
                        <option value="Немає" <?= ($_POST['water_supply'] ?? '') == 'Немає' ? 'selected' : '' ?>>Немає</option>
                    </select>
                </label>

                <label>Опалення:
                    <select name="heating" required>
                        <option value="Центральне" <?= ($_POST['heating'] ?? '') == 'Центральне' ? 'selected' : '' ?>>Центральне</option>
                        <option value="Автономне" <?= ($_POST['heating'] ?? '') == 'Автономне' ? 'selected' : '' ?>>Автономне</option>
                        <option value="Електричне" <?= ($_POST['heating'] ?? '') == 'Електричне' ? 'selected' : '' ?>>Електричне</option>
                        <option value="Газове" <?= ($_POST['heating'] ?? '') == 'Газове' ? 'selected' : '' ?>>Газове</option>
                        <option value="Твердопаливне" <?= ($_POST['heating'] ?? '') == 'Твердопаливне' ? 'selected' : '' ?>>Твердопаливне</option>
                    </select>
                </label>

                <label>Господарські споруди:
                    <input type="text" name="outbuildings" value="<?= htmlspecialchars($_POST['outbuildings'] ?? '') ?>">
                </label>

                <label>Інфраструктура:
                    <textarea name="infrastructure"><?= htmlspecialchars($_POST['infrastructure'] ?? '') ?></textarea>
                </label>

                <label>Гараж:
                    <select name="garage" required>
                        <option value="Окремий" <?= ($_POST['garage'] ?? '') == 'Окремий' ? 'selected' : '' ?>>Окремий</option>
                        <option value="Вбудований" <?= ($_POST['garage'] ?? '') == 'Вбудований' ? 'selected' : '' ?>>Вбудований</option>
                        <option value="Немає" <?= ($_POST['garage'] ?? '') == 'Немає' ? 'selected' : '' ?>>Немає</option>
                    </select>
                </label>

                <label>Стан ремонту:
                    <select name="renovation" required>
                        <option value="Євроремонт" <?= ($_POST['renovation'] ?? '') == 'Євроремонт' ? 'selected' : '' ?>>Євроремонт</option>
                        <option value="Косметичний" <?= ($_POST['renovation'] ?? '') == 'Косметичний' ? 'selected' : '' ?>>Косметичний</option>
                        <option value="Без ремонту" <?= ($_POST['renovation'] ?? '') == 'Без ремонту' ? 'selected' : '' ?>>Без ремонту</option>
                    </select>
                </label>

                <label>Меблі:
                    <select name="furnished" required>
                        <option value="Повністю мебльована" <?= ($_POST['furnished'] ?? '') == 'Повністю мебльована' ? 'selected' : '' ?>>Повністю мебльована</option>
                        <option value="Частково мебльована" <?= ($_POST['furnished'] ?? '') == 'Частково мебльована' ? 'selected' : '' ?>>Частково мебльована</option>
                        <option value="Без меблів" <?= ($_POST['furnished'] ?? '') == 'Без меблів' ? 'selected' : '' ?>>Без меблів</option>
                    </select>
                </label>

                <label>Побутова техніка:
                    <select name="appliances" required>
                        <option value="Повний комплект" <?= ($_POST['appliances'] ?? '') == 'Повний комплект' ? 'selected' : '' ?>>Повний комплект</option>
                        <option value="Частковий комплект" <?= ($_POST['appliances'] ?? '') == 'Частковий комплект' ? 'selected' : '' ?>>Частковий комплект</option>
                        <option value="Немає" <?= ($_POST['appliances'] ?? '') == 'Немає' ? 'selected' : '' ?>>Немає</option>
                    </select>
                </label>

                <label>Санвузол:
                    <select name="bathroom" required>
                        <option value="Суміщений" <?= ($_POST['bathroom'] ?? '') == 'Суміщений' ? 'selected' : '' ?>>Суміщений</option>
                        <option value="Роздільний" <?= ($_POST['bathroom'] ?? '') == 'Роздільний' ? 'selected' : '' ?>>Роздільний</option>
                    </select>
                </label>

                <label>Розташування санвузла:
                    <input type="text" name="bathroom_location" value="<?= htmlspecialchars($_POST['bathroom_location'] ?? '') ?>">
                </label>

                <label>Балкон / Тераса:
                    <select name="balcony_terrace" required>
                        <option value="Балкон" <?= ($_POST['balcony_terrace'] ?? '') == 'Балкон' ? 'selected' : '' ?>>Балкон</option>
                        <option value="Тераса" <?= ($_POST['balcony_terrace'] ?? '') == 'Тераса' ? 'selected' : '' ?>>Тераса</option>
                        <option value="Немає" <?= ($_POST['balcony_terrace'] ?? '') == 'Немає' ? 'selected' : '' ?>>Немає</option>
                    </select>
                </label>

                <label>Інтернет / TV:
                    <select name="internet_tv" required>
                        <option value="Підключено" <?= ($_POST['internet_tv'] ?? '') == 'Підключено' ? 'selected' : '' ?>>Підключено</option>
                        <option value="Не підключено" <?= ($_POST['internet_tv'] ?? '') == 'Не підключено' ? 'selected' : '' ?>>Не підключено</option>
                    </select>
                </label>

                <label>Безпека:
                    <select name="security" required>
                        <option value="">Виберіть тип</option>
                        <option value="Домофон" <?= ($_POST['security'] ?? '') == 'Домофон' ? 'selected' : '' ?>>Домофон</option>
                        <option value="Відеоспостереження" <?= ($_POST['security'] ?? '') == 'Відеоспостереження' ? 'selected' : '' ?>>Відеоспостереження</option>
                        <option value="Охорона" <?= ($_POST['security'] ?? '') == 'Охорона' ? 'selected' : '' ?>>Охорона</option>
                        <option value="Немає" <?= ($_POST['security'] ?? '') == 'Немає' ? 'selected' : '' ?>>Немає</option>
                    </select>
                </label>

                <label>Тип власності:
                    <select name="ownership" required>
                        <option value="Приватна" <?= ($_POST['ownership'] ?? '') == 'Приватна' ? 'selected' : '' ?>>Приватна</option>
                        <option value="Кооперативна" <?= ($_POST['ownership'] ?? '') == 'Кооперативна' ? 'selected' : '' ?>>Кооперативна</option>
                        <option value="Державна" <?= ($_POST['ownership'] ?? '') == 'Державна' ? 'selected' : '' ?>>Державна</option>
                    </select>
                </label>

                <label>Підходить під іпотеку:
                    <select name="mortgage_available" required>
                        <option value="Так" <?= ($_POST['mortgage_available'] ?? '') == 'Так' ? 'selected' : '' ?>>Так</option>
                        <option value="Ні" <?= ($_POST['mortgage_available'] ?? '') == 'Ні' ? 'selected' : '' ?>>Ні</option>
                    </select>
                </label>

                <label>Призначення:
                    <select name="purpose" required>
                        <option value="Житлове" <?= ($_POST['purpose'] ?? '') == 'Житлове' ? 'selected' : '' ?>>Житлове</option>
                        <option value="Комерційне" <?= ($_POST['purpose'] ?? '') == 'Комерційне' ? 'selected' : '' ?>>Комерційне</option>
                        <option value="Змішане" <?= ($_POST['purpose'] ?? '') == 'Змішане' ? 'selected' : '' ?>>Змішане</option>
                    </select>
                </label>

                <label>Огорожа:
                    <input type="text" name="fence" value="<?= htmlspecialchars($_POST['fence'] ?? '') ?>">
                </label>

                <label>Відстань до міста (км):
                    <input type="number" step="0.1" name="distance_to_city" value="<?= htmlspecialchars($_POST['distance_to_city'] ?? '') ?>" required>
                </label>
            </div>

            <div class="form-section">
                <h3>Фотографії</h3>
                <?php if (!empty($photos)): ?>
                    <div class="photo-preview">
                        <?php foreach ($photos as $photo): ?>
                            <label>
                                <input type="checkbox" name="delete_photos[]" value="<?= htmlspecialchars($photo['photo_id']) ?>">
                                <img src="<?= htmlspecialchars($photo['file_path']) ?>" alt="Фото будинку">
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