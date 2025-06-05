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
    $type_id = intval($_POST['type_id'] ?? 0);
    $price = floatval($_POST['price'] ?? 0);
    $rooms = intval($_POST['rooms'] ?? 0);
    $owner_id = $_SESSION['user_id'];
    $description = trim($_POST['description'] ?? '');

    // Initialize optional fields
    $floor = ($type_id == 1) ? intval($_POST['floor'] ?? 0) : 0;
    $floors = ($type_id == 2) ? intval($_POST['floors'] ?? 0) : 0; // Changed from total_floors to floors

    // Validate required fields
    if ($address === '') {
        $errors[] = "Адреса є обов'язковою.";
    }
    if ($area <= 0) {
        $errors[] = "Площа повинна бути більше 0.";
    }
    if ($rooms < 1) {
        $errors[] = "Кількість кімнат повинна бути не менше 1.";
    }
    if ($type_id < 1) {
        $errors[] = "Виберіть тип нерухомості.";
    }
    if ($price <= 0) {
        $errors[] = "Ціна повинна бути більше 0.";
    }
    if ($type_id == 1 && $floor < 1) {
        $errors[] = "Поверх для квартири повинен бути не менше 1.";
    }
    if ($type_id == 2 && $floors < 1) {
        $errors[] = "Кількість поверхів для будинку повинна бути не менше 1.";
    }
    if ($description === '') {
        $errors[] = "Опис є обов'язковим.";
    }
    if (!isset($_FILES['photos']) || count($_FILES['photos']['name']) < 5) {
        $errors[] = "Потрібно завантажити від 5 до 20 фотографій.";
    }

    // Validate house-specific fields
    if ($type_id == 2) {
        $total_area = floatval($_POST['total_area'] ?? 0);
        $living_area = floatval($_POST['living_area'] ?? 0);
        $land_area = floatval($_POST['land_area'] ?? 0);
        $sewerage = $_POST['sewerage'] ?? '';
        $water_supply = $_POST['water_supply'] ?? '';
        $heating = $_POST['heating'] ?? '';
        $garage = $_POST['garage'] ?? '';
        $balcony_terrace = $_POST['balcony_terrace'] ?? '';
        $ownership = $_POST['ownership'] ?? '';
        $mortgage_available = $_POST['mortgage_available'] ?? '';
        $purpose = $_POST['purpose'] ?? '';

        if ($total_area <= 0) {
            $errors[] = "Загальна площа будинку повинна бути більше 0.";
        }
        if ($living_area <= 0) {
            $errors[] = "Житлова площа будинку повинна бути більше 0.";
        }
        if ($land_area <= 0) {
            $errors[] = "Площа ділянки повинна бути більше 0.";
        }
        if ($sewerage === '') {
            $errors[] = "Виберіть тип каналізації.";
        }
        if ($water_supply === '') {
            $errors[] = "Виберіть тип водопостачання.";
        }
        if ($heating === '') {
            $errors[] = "Виберіть тип опалення.";
        }
        if ($garage === '') {
            $errors[] = "Виберіть тип гаража.";
        }
        if ($balcony_terrace === '') {
            $errors[] = "Виберіть тип балкона/тераси.";
        }
        if ($ownership === '') {
            $errors[] = "Виберіть тип власності.";
        }
        if ($mortgage_available === '') {
            $errors[] = "Вкажіть, чи підходить під іпотеку.";
        }
        if ($purpose === '') {
            $errors[] = "Виберіть призначення.";
        }
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO properties (address, area, floor, rooms, type_id, owner_id, price) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $floorToInsert = ($type_id == 1) ? $floor : $floors; // Use floors for houses
        $stmt->bind_param("sddiiid", $address, $area, $floorToInsert, $rooms, $type_id, $owner_id, $price);

        if ($stmt->execute()) {
            $property_id = $stmt->insert_id;

            // For house details
            if ($type_id == 2) {
                $building_type = $_POST['building_type'] ?? '';
                $build_year = intval($_POST['build_year'] ?? 0);
                $floors = intval($_POST['floors'] ?? 0);
                $total_area = floatval($_POST['total_area'] ?? 0);
                $living_area = floatval($_POST['living_area'] ?? 0);
                $land_area = floatval($_POST['land_area'] ?? 0);
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
                $distance_to_city = floatval($_POST['distance_to_city'] ?? 0);
            
                $stmt3 = $conn->prepare("INSERT INTO house_details (
                    property_id, building_type, build_year, floors, total_area, living_area, land_area,
                    sewerage, water_supply, heating, garage, outbuildings, infrastructure, renovation,
                    furnished, appliances, bathroom, bathroom_location, balcony_terrace, internet_tv,
                    security, ownership, mortgage_available, purpose, fence, distance_to_city, description
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt3->bind_param(
                    "isisddsssssssssssssssssssds",
                    $property_id,
                    $building_type,
                    $build_year,
                    $floors,
                    $total_area,
                    $living_area,
                    $land_area,
                    $sewerage,
                    $water_supply,
                    $heating,
                    $garage,
                    $outbuildings,
                    $infrastructure,
                    $renovation,
                    $furnished,
                    $appliances,
                    $bathroom,
                    $bathroom_location,
                    $balcony_terrace,
                    $internet_tv,
                    $security,
                    $ownership,
                    $mortgage_available,
                    $purpose,
                    $fence,
                    $distance_to_city,
                    $description
                );
                $stmt3->execute();
            }
            } else {
                // Handle flat details (unchanged)
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

                $stmt3 = $conn->prepare("INSERT INTO flat_details (
                    property_id, building_type, build_year, elevators, heating, infrastructure, renovation,
                    furnished, appliances, bathroom, bathroom_count, internet_tv, security, parking,
                    ownership, mortgage_available, balcony, description
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt3->bind_param(
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
                $stmt3->execute();
            }

            $baseFolder = $type_id == 1 ? 'pics/flat' : 'pics/houses';
            $propertyFolder = "$baseFolder/$property_id";
            if (!is_dir($propertyFolder)) {
                mkdir($propertyFolder, 0777, true);
            }

            $photos = $_FILES['photos'];
            $successUploads = 0;

            for ($i = 0; $i < count($photos['name']); $i++) {
                if ($photos['error'][$i] === UPLOAD_ERR_OK) {
                    $tmp_name = $photos['tmp_name'][$i];
                    $name = basename($photos['name'][$i]);
                    $targetPath = "$propertyFolder/$name";

                    if (move_uploaded_file($tmp_name, $targetPath)) {
                        $relativePath = "$baseFolder/$property_id/$name";
                        $stmt2 = $conn->prepare("INSERT INTO property_photos (property_id, file_path) VALUES (?, ?)");
                        $stmt2->bind_param("is", $property_id, $relativePath);
                        $stmt2->execute();
                        $successUploads++;
                    }
                }
            }

            $message = "Нерухомість додана. Завантажено $successUploads фото.";
        } else {
            $errors[] = "Помилка бази даних: " . $stmt->error;
        }
    }

    if (!empty($errors)) {
        $message = implode("<br>", $errors);
    }
?>

<?php include 'header.php'; ?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <form method="POST" enctype="multipart/form-data">
        <h2>Продати нерухомість</h2>
        <label>Адреса:
            <input type="text" name="address" value="<?= htmlspecialchars($_POST['address'] ?? '') ?>" required>
        </label>

        <label>Площа (м²):
            <input type="number" step="0.1" name="area" value="<?= htmlspecialchars($_POST['area'] ?? '') ?>" required min="0.1">
        </label>

        <label>Тип нерухомості:
            <select name="type_id" onchange="toggleFields()" required>
                <option value="1" <?= (($_POST['type_id'] ?? '') == 1) ? 'selected' : '' ?>>Квартира</option>
                <option value="2" <?= (($_POST['type_id'] ?? '') == 2) ? 'selected' : '' ?>>Будинок</option>
            </select>
        </label>

        <div id="flat-fields" style="<?= (($_POST['type_id'] ?? 1) == 1) ? 'display:block' : 'display:none' ?>">
            <?php include 'form_flat_fields.php'; ?>
        </div>

        <div id="house-fields" style="<?= (($_POST['type_id'] ?? 1) == 2) ? 'display:block' : 'display:none' ?>">
            <?php include 'form_house_fields.php'; ?>
        </div>

        <label>Кількість кімнат:
            <select name="rooms" required>
                <?php for ($i = 1; $i <= 10; $i++): ?>
                    <option value="<?= $i ?>" <?= (($_POST['rooms'] ?? '') == $i) ? 'selected' : '' ?>><?= $i ?></option>
                <?php endfor; ?>
            </select>
        </label>

        <label>Ціна (у $):
            <input type="number" name="price" value="<?= htmlspecialchars($_POST['price'] ?? '') ?>" required min="1">
        </label>

        <label>Опис:
            <textarea name="description" rows="5" required><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
        </label>

        <label>Фото (мін. 5, макс. 20):
            <input type="file" name="photos[]" multiple accept="image/*" required>
        </label>

        <button type="submit">Додати оголошення</button>

        <?php if ($message): ?>
            <p class="message"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>
    </form>
</div>

<script>
    function toggleFields() {
        const typeSelect = document.querySelector('[name="type_id"]');
        const flatFields = document.getElementById('flat-fields');
        const houseFields = document.getElementById('house-fields');

        if (typeSelect.value === '1') {
            flatFields.style.display = 'block';
            houseFields.style.display = 'none';
            flatFields.querySelectorAll("input, select, textarea").forEach(el => el.required = true);
            houseFields.querySelectorAll("input, select, textarea").forEach(el => el.required = false);
        } else {
            flatFields.style.display = 'none';
            houseFields.style.display = 'block';
            houseFields.querySelectorAll("input:not([name='outbuildings']):not([name='appliances']):not([name='bathroom']):not([name='bathroom_location']):not([name='security']):not([name='fence'])").forEach(el => el.required = true);
            houseFields.querySelectorAll("select, textarea").forEach(el => el.required = true);
            flatFields.querySelectorAll("input, select, textarea").forEach(el => el.required = false);
        }
    }

    document.addEventListener('DOMContentLoaded', toggleFields);
</script>
</body>
</html>
<?php include 'footer.php'; ?>