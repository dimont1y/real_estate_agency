    <?php
    session_start();
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    include 'connect_to_db.php';

    $message = "";

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $address = trim($_POST['address']);
        $area = floatval($_POST['area']);
        $type_id = intval($_POST['type_id']);
        $price = floatval($_POST['price']);
        $rooms = intval($_POST['rooms']);
        $owner_id = $_SESSION['user_id'];
        $description = trim($_POST['description']);

        $floor = ($type_id == 1) ? intval($_POST['floor']) : null;
        $total_floors = ($type_id == 2) ? intval($_POST['total_floors']) : null;

        if ($address === '' || $area <= 0 || $rooms < 1 || $type_id < 1 || $price <= 0 ||
            ($type_id == 1 && $floor < 1) || ($type_id == 2 && $total_floors < 1)) {
            $message = "Будь ласка, заповніть всі поля коректно.";
        } elseif (!isset($_FILES['photos']) || count($_FILES['photos']['name']) < 5) {
            $message = "Потрібно завантажити від 5 до 20 фотографій.";
        } else {
            $stmt = $conn->prepare("INSERT INTO properties (address, area, floor, rooms, type_id, owner_id, price) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $floorToInsert = ($type_id == 1) ? $floor : $total_floors;
            $stmt->bind_param("sddiiid", $address, $area, $floorToInsert, $rooms, $type_id, $owner_id, $price);

            if ($stmt->execute()) {
                $property_id = $stmt->insert_id;

                if ($type_id == 1) {
                    $stmt3 = $conn->prepare("INSERT INTO flat_details (property_id, building_type, build_year, elevators, heating, infrastructure, renovation, furnished, appliances, bathroom, bathroom_count, internet_tv, security, parking, ownership, mortgage_available, balcony, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt3->bind_param(
                        "isisssssssisisssss",
                        $property_id,
                        $_POST['building_type'],
                        $_POST['build_year'],
                        $_POST['elevators'],
                        $_POST['heating'],
                        $_POST['infrastructure'],
                        $_POST['renovation'],
                        $_POST['furnished'],
                        $_POST['appliances'],
                        $_POST['bathroom'],
                        $_POST['bathroom_count'],
                        $_POST['internet_tv'],
                        $_POST['security'],
                        $_POST['parking'],
                        $_POST['ownership'],
                        $_POST['mortgage_available'],
                        $_POST['balcony'],
                        $description
                    );
                    $stmt3->execute();
                } else {
                    $stmt3 = $conn->prepare("INSERT INTO house_details (property_id, building_type, build_year, floors, total_area, living_area, land_area, sewerage, water_supply, heating, garage, outbuildings, infrastructure, renovation, furnished, appliances, bathroom, bathroom_location, balcony_terrace, internet_tv, security, ownership, mortgage_available, purpose, fence, distance_to_city, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt3->bind_param(
                        "isidddssssssssssssssssssss",
                        $property_id,
                        $_POST['building_type'],
                        $_POST['build_year'],
                        $_POST['floors'],
                        $_POST['total_area'],
                        $_POST['living_area'],
                        $_POST['land_area'],
                        $_POST['sewerage'],
                        $_POST['water_supply'],
                        $_POST['heating'],
                        $_POST['garage'],
                        $_POST['outbuildings'],
                        $_POST['infrastructure'],
                        $_POST['renovation'],
                        $_POST['furnished'],
                        $_POST['appliances'],
                        $_POST['bathroom'],
                        $_POST['bathroom_location'],
                        $_POST['balcony_terrace'],
                        $_POST['internet_tv'],
                        $_POST['security'],
                        $_POST['ownership'],
                        $_POST['mortgage_available'],
                        $_POST['purpose'],
                        $_POST['fence'],
                        $_POST['distance_to_city'],
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
                $message = "Помилка: " . $stmt->error;
            }
        }
    }
    ?>

<?php include 'header.php'; ?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Продати нерухомість</title>
    <link rel="stylesheet" href="style.css">
</head>

<div class="container">
        <h2>Продати нерухомість</h2>
        <form method="POST" enctype="multipart/form-data">
            <label>Адреса:
                <input type="text" name="address" required>
            </label>

            <label>Площа (м²):
                <input type="number" step="0.1" name="area" required>
            </label>

            <label>Тип нерухомості:
                <select name="type_id" onchange="toggleFields()" required>
                    <option value="1">Квартира</option>
                    <option value="2">Будинок</option>
                </select>
            </label>

            <div id="flat-fields">
                <?php include 'form_flat_fields.php'; ?>
            </div>

            <div id="house-fields" style="display:none;">
                <?php include 'form_house_fields.php'; ?>
            </div>

            <label>Кількість кімнат:
                <select name="rooms" required>
                    <?php for ($i = 1; $i <= 10; $i++) echo "<option value='$i'>$i</option>"; ?>
                </select>
            </label>

            <label>Ціна (у $):
                <input type="number" name="price" required>
            </label>

            <label>Опис:
                <textarea name="description" rows="5" placeholder="Опишіть квартиру або будинок..."></textarea>
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
<body>

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
        houseFields.querySelectorAll("input, select, textarea").forEach(el => el.required = true);
        flatFields.querySelectorAll("input, select, textarea").forEach(el => el.required = false);
    }
}

    document.addEventListener('DOMContentLoaded', toggleFields);
    </script>

    </body>

    </html>
<?php include 'footer.php'; ?>
