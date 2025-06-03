<?php
$type = $_GET['type'] ?? 'flat'; 
if ($type === 'house') {
    header("Location: buy_house.php?" . http_build_query($_GET));
} else {
    header("Location: buy_flat.php?" . http_build_query($_GET));
}
exit();
