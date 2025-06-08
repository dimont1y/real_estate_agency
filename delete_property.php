<?php
session_start();
include 'connect_to_db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['property_id'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['message'] = "Невірний CSRF-токен.";
        header("Location: profile.php");
        exit();
    }

    $property_id = (int)$_POST['property_id'];

    $stmt = $conn->prepare("SELECT type_id FROM properties WHERE property_id = ? AND owner_id = ?");
    $stmt->bind_param("ii", $property_id, $user_id);
    $stmt->execute();
    $property = $stmt->get_result()->fetch_assoc();

    if (!$property) {
        $_SESSION['message'] = "Оголошення не знайдено або ви не маєте права його видалити.";
        header("Location: profile.php");
        exit();
    }

    $type_id = $property['type_id'];
    $details_table = $type_id == 1 ? 'flat_details' : 'house_details';
    $photo_folder = $type_id == 1 ? 'pics/flat' : 'pics/houses';

    $conn->begin_transaction();

    try {
        $photo_stmt = $conn->prepare("SELECT file_path FROM property_photos WHERE property_id = ?");
        $photo_stmt->bind_param("i", $property_id);
        $photo_stmt->execute();
        $photos = $photo_stmt->get_result();

        while ($photo = $photos->fetch_assoc()) {
            $file_path = $photo['file_path'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }

        $property_folder = "$photo_folder/$property_id";
        if (is_dir($property_folder)) {
            rmdir($property_folder);
        }

        $photo_delete_stmt = $conn->prepare("DELETE FROM property_photos WHERE property_id = ?");
        $photo_delete_stmt->bind_param("i", $property_id);
        $photo_delete_stmt->execute();

        $details_delete_stmt = $conn->prepare("DELETE FROM {$details_table} WHERE property_id = ?");
        $details_delete_stmt->bind_param("i", $property_id);
        $details_delete_stmt->execute();

        $property_delete_stmt = $conn->prepare("DELETE FROM properties WHERE property_id = ?");
        $property_delete_stmt->bind_param("i", $property_id);
        $property_delete_stmt->execute();

        $conn->commit();

        $_SESSION['message'] = "Оголошення успішно видалено.";
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['message'] = "Помилка при видаленні оголошення: " . $e->getMessage();
    }

    header("Location: profile.php");
    exit();
} else {
    $_SESSION['message'] = "Невірний запит на видалення.";
    header("Location: profile.php");
    exit();
}
?>