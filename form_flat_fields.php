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
    <input type="text" name="appliances">
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
    <input type="text" name="security">
</label>

<label>Паркінг:
    <input type="text" name="parking">
</label>

<label>Тип власності:
    <input type="text" name="ownership">
</label>

<label>Підходить під іпотеку:
    <select name="mortgage_available">
        <option value="Так">Так</option>
        <option value="Ні">Ні</option>
    </select>
</label>

<label>Балкон/лоджія:
    <input type="text" name="balcony">
</label>

<label>Опис:
    <textarea name="description" rows="4" placeholder="Додайте опис квартири..."></textarea>
</label>
