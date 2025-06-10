<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'connect_to_db.php';
$message = "";
$errors = [];

$user_id = $_SESSION['user_id'];
$limit = 3;
$ad_count = 0;
$stmt = $conn->prepare("SELECT COUNT(*) FROM properties WHERE owner_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($ad_count);
$stmt->fetch();
$stmt->close();

if ($ad_count >= $limit) {
    $admin = $conn->query("SELECT user_id FROM users WHERE role IN ('admin','moderator') ORDER BY role='admin' DESC, user_id ASC LIMIT 1")->fetch_assoc();
    if ($admin) {
        $admin_id = $admin['user_id'];
        $text = "Я хочу розмістити більше 3-х оголошень. Прошу надати можливість або розповісти про оплату.";
        $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, text) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $user_id, $admin_id, $text);
        $stmt->execute();
    }
    echo '<div style="background:#ffcdd2; color:#b71c1c; padding:16px; border-radius:8px; margin:20px 0; font-weight:bold; text-align:center;">Ви досягли ліміту безкоштовних оголошень. Ми вже повідомили адміністратора — очікуйте відповіді в чаті.<br><br><a href="index.php" style="display:inline-block; margin:6px 8px 0 0; padding:10px 24px; background:#007bff; color:#fff; border-radius:6px; text-decoration:none; font-weight:normal;">На головну</a><a href="chat.php" style="display:inline-block; margin:6px 0 0 8px; padding:10px 24px; background:#ffc107; color:#222; border-radius:6px; text-decoration:none; font-weight:normal;">Зв\'язатись з модератором</a></div>';
    include 'footer.php';
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $address = trim($_POST['address'] ?? '');
    $area = floatval($_POST['area'] ?? 0);
    $price = floatval($_POST['price'] ?? 0);
    $rooms = intval($_POST['rooms'] ?? 0);
    $floors = intval($_POST['floors'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    
    if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
        $admin_email = "admin@admin";
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->bind_param("s", $admin_email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            $username = "Admin";
            $password_hash = password_hash("admin", PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $admin_email, $password_hash);
            $stmt->execute();
            $owner_id = $stmt->insert_id;
        } else {
            $owner_id = $result->fetch_assoc()['user_id'];
        }
    } else {
        $owner_id = $_SESSION['user_id'];
    }

    if ($address === '') $errors[] = "Адреса є обов'язковою.";
    if ($area <= 0) $errors[] = "Площа повинна бути більше 0.";
    if ($rooms < 1) $errors[] = "Кількість кімнат повинна бути не менше 1.";
    if ($price <= 0) $errors[] = "Ціна повинна бути більше 0.";
    if ($floors < 1) $errors[] = "Кількість поверхів повинна бути не менше 1.";
    if ($description === '') $errors[] = "Опис є обов'язковим.";
    if (!isset($_FILES['photos']) || count($_FILES['photos']['name']) < 5) {
        $errors[] = "Потрібно завантажити від 5 до 20 фотографій.";
    }

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

    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO properties (address, area, floor, rooms, type_id, owner_id, price, status) VALUES (?, ?, ?, ?, 2, ?, ?, 'pending')");
        $stmt->bind_param("sddiid", $address, $area, $floors, $rooms, $owner_id, $price);
        if ($stmt->execute()) {
            $property_id = $stmt->insert_id;

            $building_type = $_POST['building_type'] ?? '';
            $build_year = intval($_POST['build_year'] ?? 0);
            $outbuildings = $_POST['outbuildings'] ?? '';
            $infrastructure = $_POST['infrastructure'] ?? '';
            $renovation = $_POST['renovation'] ?? '';
            $furnished = $_POST['furnished'] ?? '';
            $appliances = $_POST['appliances'] ?? '';
            $bathroom = $_POST['bathroom'] ?? '';
            $bathroom_location = $_POST['bathroom_location'] ?? '';
            $internet_tv = $_POST['internet_tv'] ?? '';
            $security = $_POST['security'] ?? '';
            $fence = $_POST['fence'] ?? '';
            $distance_to_city = floatval($_POST['distance_to_city'] ?? 0);

            $stmt2 = $conn->prepare("INSERT INTO house_details (
                property_id, building_type, build_year, floors, total_area, living_area, land_area,
                sewerage, water_supply, heating, garage, outbuildings, infrastructure, renovation,
                furnished, appliances, bathroom, bathroom_location, balcony_terrace, internet_tv,
                security, ownership, mortgage_available, purpose, fence, distance_to_city, description
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt2->bind_param(
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
            $stmt2->execute();

            $folder = "pics/houses/$property_id";
            mkdir($folder, 0777, true);
            $photos = $_FILES['photos'];
            for ($i = 0; $i < count($photos['name']); $i++) {
                if ($photos['error'][$i] === UPLOAD_ERR_OK) {
                    $name = basename($photos['name'][$i]);
                    $path = "$folder/$name";
                    if (move_uploaded_file($photos['tmp_name'][$i], $path)) {
                        $relPath = "pics/houses/$property_id/$name";
                        $stmt3 = $conn->prepare("INSERT INTO property_photos (property_id, file_path) VALUES (?, ?)");
                        $stmt3->bind_param("is", $property_id, $relPath);
                        $stmt3->execute();
                    }
                }
            }

            $message = "Ваше оголошення надіслано на модерацію. Після схвалення адміністратором воно з'явиться на сайті.";
        } else {
            $errors[] = "Помилка бази даних: " . $stmt->error;
        }
    }

    if (!empty($errors)) $message = implode("<br>", $errors);
}
?>

<?php include 'header.php'; ?>

<div class="form-wrapper">
    <h2>Продати будинок</h2>
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

        <label>Кількість поверхів:
            <input type="number" name="floors" min="1" required value="<?= htmlspecialchars($_POST['floors'] ?? '') ?>">
        </label>

        <?php include 'form_house_fields.php'; ?>

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
