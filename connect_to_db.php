<?php
$mode = $mode ?? 'admin';

if ($mode === 'guest') {
    $user = "real_estate_guest";
    $password = "guest_password";
} else {
    $user = "agency_admin";
    $password = "agency_dima_12302";
}

$host = "localhost";
$dbname = "real_estate_agency";

$conn = mysqli_connect($host, $user, $password, $dbname);

if (!$conn) {
    die("Помилка підключення: " . mysqli_connect_error());
}
?>
