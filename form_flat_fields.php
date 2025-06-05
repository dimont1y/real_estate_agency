<?php
// form_flat_fields.php
?>

<label>Поверх:
    <input type="number" name="floor" min="1">
</label>

<label>Тип будинку:
    <select name="building_type" required>
        <option value="Цегляний">Цегляний</option>
        <option value="Панельний">Панельний</option>
        <option value="Моноліт">Моноліт</option>
        <option value="Новобудова">Новобудова</option>
    </select>
</label>

<label>Рік будівництва / здачі:
    <input type="number" name="build_year">
</label>

<label>Ліфти (кількість):
    <input type="number" name="elevators">
</label>

<label>Опалення:
    <select name="heating">
        <option value="Центральне">Центральне</option>
        <option value="Автономне">Автономне</option>
        <option value="Електричне">Електричне</option>
    </select>
</label>

<label>Інфраструктура:
    <textarea name="infrastructure"></textarea>
</label>

<label>Стан ремонту:
    <select name="renovation">
        <option value="Євроремонт">Євроремонт</option>
        <option value="Косметичний">Косметичний</option>
        <option value="Без ремонту">Без ремонту</option>
    </select>
</label>

<label>Меблі:
    <select name="furnished">
        <option value="Повністю мебльована">Повністю мебльована</option>
        <option value="Частково мебльована">Частково мебльована</option>
        <option value="Без меблів">Без меблів</option>
    </select>
</label>

<label>Побутова техніка:
    <select name="appliances" required>
        <option value="">Виберіть наявність</option>
        <option value="Повний комплект" <?= ($_POST['appliances'] ?? '') == 'Повний комплект' ? 'selected' : '' ?>>Повний комплект</option>
        <option value="Частковий комплект" <?= ($_POST['appliances'] ?? '') == 'Частковий комплект' ? 'selected' : '' ?>>Частковий комплект</option>
        <option value="Немає" <?= ($_POST['appliances'] ?? '') == 'Немає' ? 'selected' : '' ?>>Немає</option>
    </select>
</label>

<label>Санвузол:
    <select name="bathroom">
        <option value="Суміщений">Суміщений</option>
        <option value="Роздільний">Роздільний</option>
    </select>
</label>

<label>Кількість санвузлів:
    <input type="number" name="bathroom_count" min="1">
</label>

<label>Інтернет / TV:
    <select name="internet_tv">
        <option value="Підключено">Підключено</option>
        <option value="Не підключено">Не підключено</option>
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

<label>Паркінг:
    <select name="parking" required>
        <option value="Підземний" <?= ($_POST['parking'] ?? '') == 'Підземний' ? 'selected' : '' ?>>Підземний</option>
        <option value="Наземний" <?= ($_POST['parking'] ?? '') == 'Наземний' ? 'selected' : '' ?>>Наземний</option>
        <option value="Гараж" <?= ($_POST['parking'] ?? '') == 'Гараж' ? 'selected' : '' ?>>Гараж</option>
        <option value="Немає" <?= ($_POST['parking'] ?? '') == 'Немає' ? 'selected' : '' ?>>Немає</option>
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
    <select name="mortgage_available">
        <option value="Так">Так</option>
        <option value="Ні">Ні</option>
    </select>
</label>

<label>Балкон/лоджія:
    <select name="balcony" required>
        <option value="Балкон" <?= ($_POST['balcony'] ?? '') == 'Балкон' ? 'selected' : '' ?>>Балкон</option>
        <option value="Лоджія" <?= ($_POST['balcony'] ?? '') == 'Лоджія' ? 'selected' : '' ?>>Лоджія</option>
        <option value="Балкон і лоджія" <?= ($_POST['balcony'] ?? '') == 'Балкон і лоджія' ? 'selected' : '' ?>>Балкон і лоджія</option>
        <option value="Немає" <?= ($_POST['balcony'] ?? '') == 'Немає' ? 'selected' : '' ?>>Немає</option>
    </select>
</label>

