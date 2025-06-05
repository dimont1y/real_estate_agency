<?php
include 'connect_to_db.php';
include 'header.php';

$property_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($property_id <= 0) {
    echo "<p style='text-align:center;'>Невірний ідентифікатор нерухомості.</p>";
    include 'footer.php';
    exit();
}

// Fetch property data
$stmt = $conn->prepare("SELECT * FROM properties WHERE property_id = ?");
$stmt->bind_param("i", $property_id);
$stmt->execute();
$property = $stmt->get_result()->fetch_assoc();

if (!$property) {
    echo "<p style='text-align:center;'>Оголошення не знайдено.</p>";
    include 'footer.php';
    exit();
}

// Define default values for all possible fields
$property_defaults = [
    // From properties table
    'property_id' => 0,
    'address' => '',
    'area' => 0,
    'floor' => 0, // Used for both flats (floor) and houses (floors)
    'rooms' => 0,
    'type_id' => 0,
    'owner_id' => 0,
    'price' => 0,

    // Common fields in flat_details and house_details
    'building_type' => '',
    'build_year' => 0,
    'heating' => '',
    'infrastructure' => '',
    'renovation' => '',
    'furnished' => '',
    'appliances' => '',
    'bathroom' => '',
    'internet_tv' => '', // Treating as string for now (e.g., "Під<v>Підключено</v> or "Не підключено")
    'security' => '',
    'ownership' => '',
    'mortgage_available' => '', // Treating as string (e.g., "Так" or "Ні")
    'description' => '',

    // Flat-specific fields (type_id = 1)
    'elevators' => 0,
    'bathroom_count' => 0,
    'parking' => '',
    'balcony' => '',

    // House-specific fields (type_id = 2)
    'floors' => 0, // Already included as 'floor' in properties, but we’ll map it
    'total_area' => 0,
    'living_area' => 0,
    'land_area' => 0,
    'sewerage' => '',
    'water_supply' => '',
    'garage' => '',
    'outbuildings' => '',
    'bathroom_location' => '',
    'balcony_terrace' => '',
    'purpose' => '',
    'fence' => '',
    'distance_to_city' => 0,
];

// Merge default values with the fetched property data
$property = array_merge($property_defaults, $property);

// Determine property type and fetch additional details
$type_id = $property['type_id'];
$details_table = ($type_id == 1) ? 'flat_details' : 'house_details';

$details_stmt = $conn->prepare("SELECT * FROM {$details_table} WHERE property_id = ?");
$details_stmt ? $details_stmt->bind_param("i", $property_id) : null;
$details_stmt->execute();
$details = $details_stmt->get_result()->fetch_assoc();

// Merge details with property data, ensuring all fields are covered
if ($details) {
    $property = array_merge($property, $details);
}

// For houses, map 'floors' from house_details to the 'floor' field for consistency
if ($type_id == 2 && isset($details['floors'])) {
    $property['floor'] = $details['floors'];
}

// Debug: Log the property data to inspect what's being retrieved
file_put_contents('debug.log', print_r($property, true));

// Utility functions
function safe_output($value, $default = 'Не вказано') {
    return !empty($value) && $value !== null ? htmlspecialchars($value) : $default;
}

function bool_output($value, $true_text = 'Так', $false_text = 'Ні', $default = 'Не вказано') {
    if ($value === null || $value === '') return $default;
    // Handle string values from the form (e.g., "Так"/"Ні" or "Підключено"/"Не підключено")
    if (is_string($value)) {
        return $value === 'Так' || $value === 'Підключено' ? $true_text : $false_text;
    }
    // Handle numeric/boolean values (e.g., 1/0)
    return $value ? $true_text : $false_text;
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Деталі нерухомості - <?= safe_output($property['address']) ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        .gallery-container {
            max-width: 100%;
            margin: 1rem 0;
        }

        .gallery-main {
            position: relative;
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            background: #f5f5f5;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .gallery-main img {
            width: 100%;
            height: 400px;
            object-fit: cover;
            display: block;
            transition: opacity 0.3s ease;
        }

        .gallery-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(0, 0, 0, 0.6);
            color: white;
            border: none;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            font-size: 18px;
            cursor: pointer;
            transition: all 0.3s ease;
            z-index: 10;
        }

        .gallery-btn:hover {
            background: rgba(0, 0, 0, 0.8);
            transform: translateY(-50%) scale(1.1);
        }

        .gallery-btn.prev { left: 15px; }
        .gallery-btn.next { right: 15px; }

        .gallery-thumbnails {
            display: flex;
            gap: 10px;
            margin: 15px 0;
            padding: 0 15px;
            overflow-x: auto;
            justify-content: center;
        }

        .thumbnail {
            width: 70px;
            height: 70px;
            object-fit: cover;
            border-radius: 8px;
            cursor: pointer;
            border: 3px solid transparent;
            transition: all 0.3s ease;
            flex-shrink: 0;
        }

        .thumbnail:hover {
            border-color: #a30000;
            opacity: 0.8;
            transform: scale(1.05);
        }

        .thumbnail.active {
            border-color: #a30000;
            box-shadow: 0 0 10px rgba(163, 0, 0, 0.3);
        }

        .gallery-counter {
            text-align: center;
            margin-top: 10px;
            font-weight: bold;
            color: #666;
            font-size: 14px;
        }

        .section {
            margin-bottom: 2rem;
            padding: 1.5rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .section h4 {
            color: #a30000;
            margin-bottom: 1rem;
            font-size: 1.2rem;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 0.5rem;
        }

        .section.details {
            background: linear-gradient(135deg, #a30000, #630000);
            color: white;
        }

        .section.details p {
            margin: 0.5rem 0;
            font-size: 1.1rem;
        }

        .section.details strong {
            color: #ffecec;
        }

        @media (max-width: 768px) {
            .gallery-main img { height: 250px; }
            .gallery-btn { width: 40px; height: 40px; font-size: 14px; }
            .gallery-btn.prev { left: 10px; }
            .gallery-btn.next { right: 10px; }
            .thumbnail { width: 60px; height: 60px; }
            .section { padding: 1rem; margin-bottom: 1rem; }
        }

        @media (max-width: 480px) {
            .gallery-main img { height: 200px; }
            .thumbnail { width: 50px; height: 50px; }
            .gallery-thumbnails { gap: 8px; padding: 0 10px; }
        }
    </style>
</head>

<body>
    <div class="container">
        <h2><?= safe_output($property['address']) ?></h2>
        
        <div class="section details">
            <p><strong>Площа:</strong> <?= safe_output($property['area']) ?> м²</p>
            <p><strong>Поверх:</strong> <?= safe_output($property['floor']) ?></p>
            <p><strong>Кімнати:</strong> <?= safe_output($property['rooms']) ?></p>
            <p><strong>Ціна:</strong> <?= number_format($property['price'], 0, '.', ' ') ?> $</p>
        </div>

        <div class="section">
            <h4>Основна інформація</h4>
            <p><strong>Тип будинку:</strong> <?= safe_output($property['building_type']) ?></p>
            <p><strong>Рік будівництва / здачі:</strong> <?= safe_output($property['build_year']) ?></p>
            
            <?php if ($type_id == 1): ?>
                <p><strong>Ліфт:</strong> 
                    <?php if ($property['elevators'] > 0): ?>
                        Є, <?= (int)$property['elevators'] ?> ліфт(ів)
                    <?php else: ?>
                        Не вказано
                    <?php endif; ?>
                </p>
            <?php endif; ?>
            
            <p><strong>Опалення:</strong> <?= safe_output($property['heating']) ?></p>
            <p><strong>Інфраструктура:</strong> <?= safe_output($property['infrastructure']) ?></p>
            <p><strong>Стан ремонту:</strong> <?= safe_output($property['renovation']) ?></p>
            <p><strong>Меблі:</strong> <?= safe_output($property['furnished']) ?></p>
            <p><strong>Побутова техніка:</strong> <?= safe_output($property['appliances']) ?></p>
            
            <p><strong>Санвузол:</strong> 
                <?= safe_output($property['bathroom']) ?>
                <?php if ($type_id == 1 && $property['bathroom_count'] > 0): ?>
                    , кількість: <?= (int)$property['bathroom_count'] ?>
                <?php endif; ?>
                <?php if ($type_id == 2): ?>
                    <?= $property['bathroom_location'] ? ', розташування: ' . safe_output($property['bathroom_location']) : '' ?>
                <?php endif; ?>
            </p>

            <?php if ($type_id == 2): ?>
                <p><strong>Загальна площа:</strong> <?= safe_output($property['total_area']) ?> м²</p>
                <p><strong>Житлова площа:</strong> <?= safe_output($property['living_area']) ?> м²</p>
                <p><strong>Площа ділянки:</strong> <?= safe_output($property['land_area']) ?> соток</p>
                <p><strong>Каналізація:</strong> <?= safe_output($property['sewerage']) ?></p>
                <p><strong>Водопостачання:</strong> <?= safe_output($property['water_supply']) ?></p>
                <p><strong>Гараж:</strong> <?= safe_output($property['garage']) ?></p>
                <p><strong>Господарські споруди:</strong> <?= safe_output($property['outbuildings']) ?></p>
            <?php endif; ?>
        </div>

        <div class="section">
            <h4>Додаткові опції</h4>
            <p><strong>Інтернет / TV:</strong> <?= bool_output($property['internet_tv'], 'Підключено', 'Не підключено') ?></p>
            <p><strong>Безпека:</strong> <?= safe_output($property['security']) ?></p>
            <?php if ($type_id == 1): ?>
                <p><strong>Паркінг:</strong> <?= safe_output($property['parking']) ?></p>
            <?php endif; ?>
            <p><strong>Тип власності:</strong> <?= safe_output($property['ownership']) ?></p>
            <p><strong>Підходить під іпотеку:</strong> <?= bool_output($property['mortgage_available'], 'Так', 'Ні') ?></p>
            <p><strong>Балкон / лоджія / тераса:</strong> 
                <?= $type_id == 1 ? safe_output($property['balcony']) : safe_output($property['balcony_terrace']) ?>
            </p>
            <?php if ($type_id == 2): ?>
                <p><strong>Призначення:</strong> <?= safe_output($property['purpose']) ?></p>
                <p><strong>Огорожа:</strong> <?= safe_output($property['fence']) ?></p>
                <p><strong>Відстань до міста:</strong> 
                    <?= $property['distance_to_city'] > 0 ? safe_output($property['distance_to_city']) . ' км' : 'Не вказано' ?>
                </p>
            <?php endif; ?>
        </div>

        <?php if (!empty($property['description'])): ?>
        <div class="section">
            <h4>Опис</h4>
            <p><?= nl2br(htmlspecialchars($property['description'])) ?></p>
        </div>
        <?php endif; ?>

        <div class="section">
            <h4>Галерея</h4>
            <?php
            $photo_stmt = $conn->prepare("SELECT file_path FROM property_photos WHERE property_id = ?");
            $photo_stmt->bind_param("i", $property_id);
            $photo_stmt->execute();
            $photos = $photo_stmt->get_result();
            $photo_array = [];
            while ($photo = $photos->fetch_assoc()) {
                $photo_array[] = $photo['file_path'];
            }
            
            if (!empty($photo_array)): ?>
                <div class="gallery-container">
                    <div class="gallery-main">
                        <img id="mainPhoto" src="<?= htmlspecialchars($photo_array[0]) ?>" alt="Фото нерухомості">
                        <?php if (count($photo_array) > 1): ?>
                            <button class="gallery-btn prev" onclick="changePhoto(-1)">❮</button>
                            <button class="gallery-btn next" onclick="changePhoto(1)">❯</button>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (count($photo_array) > 1): ?>
                        <div class="gallery-thumbnails">
                            <?php foreach ($photo_array as $index => $photo): ?>
                                <img src="<?= htmlspecialchars($photo) ?>" 
                                     alt="Мініатюра <?= $index + 1 ?>" 
                                     class="thumbnail <?= $index === 0 ? 'active' : '' ?>"
                                     onclick="setActivePhoto(<?= $index ?>)">
                            <?php endforeach; ?>
                        </div>
                        <div class="gallery-counter">
                            <span id="photoCounter">1</span> / <?= count($photo_array) ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <script>
                const photos = <?= json_encode($photo_array) ?>;
                let currentPhotoIndex = 0;

                function changePhoto(direction) {
                    currentPhotoIndex += direction;
                    
                    if (currentPhotoIndex >= photos.length) {
                        currentPhotoIndex = 0;
                    } else if (currentPhotoIndex < 0) {
                        currentPhotoIndex = photos.length - 1;
                    }
                    
                    updatePhoto();
                }

                function setActivePhoto(index) {
                    currentPhotoIndex = index;
                    updatePhoto();
                }

                function updatePhoto() {
                    document.getElementById('mainPhoto').src = photos[currentPhotoIndex];
                    document.getElementById('photoCounter').textContent = currentPhotoIndex + 1;
                    
                    document.querySelectorAll('.thumbnail').forEach((thumb, index) => {
                        thumb.classList.toggle('active', index === currentPhotoIndex);
                    });
                }

                document.addEventListener('keydown', function(e) {
                    if (e.key === 'ArrowLeft') changePhoto(-1);
                    if (e.key === 'ArrowRight') changePhoto(1);
                });
                </script>
            <?php else: ?>
                <p style="text-align: center; color: #666;">Фото відсутні</p>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>