<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'connect_to_db.php';
$message = "";
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $address = trim($_POST['address'] ?? '');
    $area = floatval($_POST['area'] ?? 0);
    $price = floatval($_POST['price'] ?? 0);
    $rooms = intval($_POST['rooms'] ?? 0);
    $floor = intval($_POST['floor'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $owner_id = $_SESSION['user_id'];

    if ($address === '') $errors[] = "Адреса є обов'язковою.";
    if ($area <= 0) $errors[] = "Площа повинна бути більше 0.";
    if ($rooms < 1) $errors[] = "Кількість кімнат повинна бути не менше 1.";
    if ($price <= 0) $errors[] = "Ціна повинна бути більше 0.";
    if ($floor < 1) $errors[] = "Поверх повинен бути не менше 1.";
    if ($description === '') $errors[] = "Опис є обов'язковим.";
    if (!isset($_FILES['photos']) || count($_FILES['photos']['name']) < 5) {
        $errors[] = "Потрібно завантажити від 5 до 20 фотографій.";
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO properties (address, area, floor, rooms, type_id, owner_id, price) VALUES (?, ?, ?, ?, 1, ?, ?)");
        $stmt->bind_param("sddiid", $address, $area, $floor, $rooms, $owner_id, $price);
        if ($stmt->execute()) {
            $property_id = $stmt->insert_id;

            $building_type = $_POST['building_type'] ?? '';
            $build_year = intval($_POST['build_year'] ?? 0);
            $elevators = intval($_POST['elevators'] ?? 0);
            $heating = $_POST['heating'] ?? '';
            $infrastructure = $_POST['infrastructure'] ?? '';
            $renovation = $_POST['renovation'] ?? '';
            $furnished = $_POST['furnished'] ?? '';
            $appliances = $_POST['appliances'] ?? '';
            $bathroom = $_POST['bathroom'] ?? '';
            $bathroom_count = intval($_POST['bathroom_count'] ?? 0);
            $internet_tv = $_POST['internet_tv'] ?? '';
            $security = $_POST['security'] ?? '';
            $parking = $_POST['parking'] ?? '';
            $ownership = $_POST['ownership'] ?? '';
            $mortgage_available = $_POST['mortgage_available'] ?? '';
            $balcony = $_POST['balcony'] ?? '';

            $stmt2 = $conn->prepare("INSERT INTO flat_details (
                property_id, building_type, build_year, elevators, heating, infrastructure, renovation,
                furnished, appliances, bathroom, bathroom_count, internet_tv, security, parking,
                ownership, mortgage_available, balcony, description
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt2->bind_param(
                "isisssssssisssssss",
                $property_id,
                $building_type,
                $build_year,
                $elevators,
                $heating,
                $infrastructure,
                $renovation,
                $furnished,
                $appliances,
                $bathroom,
                $bathroom_count,
                $internet_tv,
                $security,
                $parking,
                $ownership,
                $mortgage_available,
                $balcony,
                $description
            );
            $stmt2->execute();

            $folder = "pics/flat/$property_id";
            mkdir($folder, 0777, true);
            $photos = $_FILES['photos'];
            for ($i = 0; $i < count($photos['name']); $i++) {
                if ($photos['error'][$i] === UPLOAD_ERR_OK) {
                    $name = basename($photos['name'][$i]);
                    $path = "$folder/$name";
                    if (move_uploaded_file($photos['tmp_name'][$i], $path)) {
                        $relPath = "pics/flat/$property_id/$name";
                        $stmt3 = $conn->prepare("INSERT INTO property_photos (property_id, file_path) VALUES (?, ?)");
                        $stmt3->bind_param("is", $property_id, $relPath);
                        $stmt3->execute();
                    }
                }
            }

            $message = "Оголошення про квартиру успішно додано.";
        } else {
            $errors[] = "Помилка: " . $stmt->error;
        }
    }

    if (!empty($errors)) $message = implode("<br>", $errors);
}
?>

<?php include 'header.php'; ?>

<div class="form-wrapper">
    <h2>Продати квартиру</h2>
    <form method="POST" enctype="multipart/form-data">
        <label>Адреса:
            <input type="text" name="address" required value="<?= htmlspecialchars($_POST['address'] ?? '') ?>">
        </label>

        <label>Площа (м²):
            <input type="number" name="area" required step="0.1" value="<?= htmlspecialchars($_POST['area'] ?? '') ?>">
        </label>

        <label>Кількість кімнат:
            <select name="rooms" required>
                <?php for ($i = 1; $i <= 10; $i++): ?>
                    <option value="<?= $i ?>" <?= ($_POST['rooms'] ?? '') == $i ? 'selected' : '' ?>><?= $i ?></option>
                <?php endfor; ?>
            </select>
        </label>

        <label>Ціна ($):
            <input type="number" name="price" required value="<?= htmlspecialchars($_POST['price'] ?? '') ?>">
        </label>

        <label>Поверх:
            <input type="number" name="floor" required min="1" value="<?= htmlspecialchars($_POST['floor'] ?? '') ?>">
        </label>

        <?php include 'form_flat_fields.php'; ?>

        <label>Опис:
            <textarea name="description" required><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
        </label>

        <label>Фото (мін. 5):
            <input type="file" name="photos[]" multiple accept="image/*" required>
        </label>

        <button type="submit">Додати</button>

        <?php if ($message): ?>
            <p class="message"><?= $message ?></p>
        <?php endif; ?>
    </form>
</div>

<?php include 'footer.php'; ?>
