<?php
// form_house_fields.php
?>

<label>Тип будинку:
    <select name="building_type" required>
        <option value="Цегляний" <?= ($_POST['building_type'] ?? '') == 'Цегляний' ? 'selected' : '' ?>>Цегляний</option>
        <option value="Панельний" <?= ($_POST['building_type'] ?? '') == 'Панельний' ? 'selected' : '' ?>>Панельний</option>
        <option value="Моноліт" <?= ($_POST['building_type'] ?? '') == 'Моноліт' ? 'selected' : '' ?>>Моноліт</option>
        <option value="Новобудова" <?= ($_POST['building_type'] ?? '') == 'Новобудова' ? 'selected' : '' ?>>Новобудова</option>
    </select>
</label>

<label>Рік будівництва:
    <input type="number" name="build_year" value="<?= htmlspecialchars($_POST['build_year'] ?? '') ?>">
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

<label>Гараж:
    <select name="garage" required>
        <option value="Окремий" <?= ($_POST['garage'] ?? '') == 'Окремий' ? 'selected' : '' ?>>Окремий</option>
        <option value="Вбудований" <?= ($_POST['garage'] ?? '') == 'Вбудований' ? 'selected' : '' ?>>Вбудований</option>
        <option value="Немає" <?= ($_POST['garage'] ?? '') == 'Немає' ? 'selected' : '' ?>>Немає</option>
    </select>
</label>

<label>Господарські споруди:
    <input type="text" name="outbuildings" value="<?= htmlspecialchars($_POST['outbuildings'] ?? '') ?>">
</label>

<label>Інфраструктура:
    <textarea name="infrastructure"><?= htmlspecialchars($_POST['infrastructure'] ?? '') ?></textarea>
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
        <option value="Балкон і тераса" <?= ($_POST['balcony_terrace'] ?? '') == 'Балкон і тераса' ? 'selected' : '' ?>>Балкон і тераса</option>
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
    <input type="number" step="0.1" name="distance_to_city" value="<?= htmlspecialchars($_POST['distance_to_city'] ?? '') ?>">
</label>