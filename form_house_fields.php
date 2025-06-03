<?php
// form_house_fields.php
?>

<label>Кількість поверхів:
    <input type="number" name="floors" min="1">
</label>

<label>Тип будинку:
    <input type="text" name="building_type">
</label>

<label>Рік будівництва:
    <input type="number" name="build_year">
</label>

<label>Загальна площа (м²):
    <input type="number" step="0.1" name="total_area">
</label>

<label>Житлова площа (м²):
    <input type="number" step="0.1" name="living_area">
</label>

<label>Площа ділянки (сотки):
    <input type="number" step="0.1" name="land_area">
</label>

<label>Каналізація:
    <input type="text" name="sewerage">
</label>

<label>Водопостачання:
    <input type="text" name="water_supply">
</label>

<label>Опалення:
    <input type="text" name="heating">
</label>

<label>Гараж:
    <input type="text" name="garage">
</label>

<label>Господарські споруди:
    <input type="text" name="outbuildings">
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
    <input type="text" name="bathroom">
</label>

<label>Розташування санвузла:
    <input type="text" name="bathroom_location">
</label>

<label>Балкон / Тераса:
    <input type="text" name="balcony_terrace">
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

<label>Тип власності:
    <input type="text" name="ownership">
</label>

<label>Підходить під іпотеку:
    <select name="mortgage_available">
        <option value="Так">Так</option>
        <option value="Ні">Ні</option>
    </select>
</label>

<label>Призначення:
    <input type="text" name="purpose">
</label>

<label>Огорожа:
    <input type="text" name="fence">
</label>

<label>Відстань до міста (км):
    <input type="number" step="0.1" name="distance_to_city">
</label>

<label>Опис:
    <textarea name="description" rows="4" placeholder="Додайте опис будинку..."></textarea>
</label>

