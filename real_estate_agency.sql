-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1
-- Час створення: Чрв 07 2025 р., 16:30
-- Версія сервера: 10.4.32-MariaDB
-- Версія PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База даних: `real_estate_agency`
--

-- --------------------------------------------------------

--
-- Структура таблиці `agents`
--

CREATE TABLE `agents` (
  `agent_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `experience` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Структура таблиці `deals`
--

CREATE TABLE `deals` (
  `deal_id` int(11) NOT NULL,
  `deal_date` date NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `deal_type` enum('sale','rent') NOT NULL,
  `buyer_id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `agent_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Структура таблиці `flat_details`
--

CREATE TABLE `flat_details` (
  `detail_id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `building_type` enum('Цегляний','Панельний','Моноліт','Новобудова') DEFAULT NULL,
  `build_year` year(4) DEFAULT NULL,
  `elevators` tinyint(4) DEFAULT NULL,
  `heating` enum('Центральне','Автономне','Електричне') DEFAULT NULL,
  `infrastructure` text DEFAULT NULL,
  `renovation` enum('Євроремонт','Косметичний','Без ремонту') DEFAULT NULL,
  `furnished` enum('Повністю','Частково','Без меблів') DEFAULT NULL,
  `appliances` text DEFAULT NULL,
  `bathroom` enum('Суміщений','Роздільний') DEFAULT NULL,
  `bathroom_count` tinyint(4) DEFAULT NULL,
  `internet_tv` tinyint(1) DEFAULT NULL,
  `security` text DEFAULT NULL,
  `parking` text DEFAULT NULL,
  `ownership` enum('Приватизована','Договір купівлі-продажу') DEFAULT NULL,
  `mortgage_available` tinyint(1) DEFAULT NULL,
  `balcony` text DEFAULT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп даних таблиці `flat_details`
--

INSERT INTO `flat_details` (`detail_id`, `property_id`, `building_type`, `build_year`, `elevators`, `heating`, `infrastructure`, `renovation`, `furnished`, `appliances`, `bathroom`, `bathroom_count`, `internet_tv`, `security`, `parking`, `ownership`, `mortgage_available`, `balcony`, `description`) VALUES
(5, 33, 'Цегляний', '2021', 1, 'Автономне', 'Магазин', 'Євроремонт', '', 'Повний комплект', 'Суміщений', 1, 1, 'Домофон', '0', '', 1, 'Лоджія', 'Продаж 1-но кімнатної квартири в новобудові по вул. Кульпарківська. Житловий комплекс Parus Park. Квартира розташована на 3-му поверсі 16-ти поверхового будинку. Загальна площа квартири 67 кв.м. Простора кухня-вітальня, та ізольована спальня. Квартира з ремонтом, облаштована меблями та побутовою технікою. Є кондиціонер.Підігрів підлоги по всій квартирі. Опалення будинкове.');

-- --------------------------------------------------------

--
-- Структура таблиці `house_details`
--

CREATE TABLE `house_details` (
  `property_id` int(11) NOT NULL,
  `building_type` varchar(50) DEFAULT NULL,
  `build_year` int(11) DEFAULT NULL,
  `floors` int(11) DEFAULT NULL,
  `total_area` decimal(6,2) DEFAULT NULL,
  `living_area` decimal(6,2) DEFAULT NULL,
  `land_area` decimal(5,2) DEFAULT NULL,
  `sewerage` varchar(50) DEFAULT NULL,
  `water_supply` varchar(50) DEFAULT NULL,
  `heating` varchar(50) DEFAULT NULL,
  `garage` varchar(100) DEFAULT NULL,
  `outbuildings` text DEFAULT NULL,
  `infrastructure` text DEFAULT NULL,
  `renovation` varchar(50) DEFAULT NULL,
  `furnished` varchar(50) DEFAULT NULL,
  `appliances` text DEFAULT NULL,
  `bathroom` varchar(50) DEFAULT NULL,
  `bathroom_location` varchar(50) DEFAULT NULL,
  `balcony_terrace` varchar(100) DEFAULT NULL,
  `internet_tv` varchar(50) DEFAULT NULL,
  `security` text DEFAULT NULL,
  `ownership` varchar(100) DEFAULT NULL,
  `mortgage_available` varchar(10) DEFAULT NULL,
  `purpose` varchar(100) DEFAULT NULL,
  `fence` varchar(100) DEFAULT NULL,
  `distance_to_city` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп даних таблиці `house_details`
--

INSERT INTO `house_details` (`property_id`, `building_type`, `build_year`, `floors`, `total_area`, `living_area`, `land_area`, `sewerage`, `water_supply`, `heating`, `garage`, `outbuildings`, `infrastructure`, `renovation`, `furnished`, `appliances`, `bathroom`, `bathroom_location`, `balcony_terrace`, `internet_tv`, `security`, `ownership`, `mortgage_available`, `purpose`, `fence`, `distance_to_city`, `description`) VALUES
(22, 'Моноліт', 2018, 2, 60.00, 30.00, 140.00, 'Центральна', 'Центральне', 'Центральне', 'Окремий', 'sdasd', 'sadd', 'Євроремонт', 'Повністю мебльована', 'Повний комплект', 'Суміщений', '1 і 2 поверх', 'Балкон', 'Підключено', 'Відеоспостереження', 'Приватна', 'Так', 'Житлове', '1.5 м. Муровані опори та паркан з шифру', '3', '0'),
(23, 'Цегляний', 2009, 2, 350.00, 150.00, 999.99, 'Центральна', 'Центральне', 'Електричне', 'Вбудований', 'Нема', 'є', 'Євроремонт', 'Частково мебльована', 'є', '2', '1 і 2 поверх', 'Балкон і тераса', 'Підключено', 'Камери та сигналізація', 'Приватна', 'Ні', 'Житлове', '2м', '2', 'Характеристики об'єкта:\r\n– Загальна площа: 350,7 м²\r\n– Поверховість: 2 поверхи + цокольний поверх (може використовуватись як бомбосховище)\r\n– Власна територія: 10 соток, повністю огороджена\r\n– Ландшафтний дизайн: альтанка, сад, альпійська гірка\r\n– Опалення: індивідуальне газове\r\n– Інфраструктура: поруч школа, Стрийський парк'),
(24, 'Цегляний', 2001, 2, 140.00, 80.00, 500.00, 'Центральна', 'Центральне', 'Газове', 'Немає', 'Нема', 'Нема', 'Євроремонт', 'Повністю мебльована', 'Є', 'Є', 'Є', 'Немає', 'Підключено', 'Нема', 'Приватна', 'Так', 'Житлове', '2м', '0', 'Оренда 4-кім. частини будинку Львів, вул. Ткацька (Замарстинівська). Поверх 2/2ц., площа 140 кв.м., євроремонт, індивідуальне опалення, всі меблі та побутова техніка, тераса 20 кв.м, мангальна зона, парковка на 3 авто, вільна.'),
(25, 'Цегляний', 2020, 2, 165.00, 60.00, 4.00, 'Центральна', 'Центральне', 'Центральне', 'Окремий', 'Літня кухня', 'Паркомісце, колодязь', 'Євроремонт', 'Повністю мебльована', 'Є', 'Є', '1 поверх', 'Балкон', 'Підключено', 'Камери та сигналізація', 'Приватна', 'Ні', 'Житлове', '1.5 м. Муровані опори та паркан з шифру', '4', 'Продаж окремо стоячого будинку біля ринку «Південний» Заг.пл.-165 м2. Будинок збудований в двох рівнях,з цегли ,на першому поверсі 3 кімнати: вітальня 2 спальні, кухня, великий хол ,та ванна кімната, другий поверх мансардний де велика вітальня дитяча кімната кухня та санвузол.також є балкон,Будинок зпланований для можливості проживання 2 сімей,перекриття залізобетонне, будинок утеплений пінопластом 100мм є газ ,центаральна вода та каналізація та невеличкий підвал.Ділянка правильної форми 5 соток на ділянці є колодязь, два окремостоячих гаражі з ямою,також та передбачене паркомісце. В літній кухні, є газ, вода та електрика, є мангал та відпочинкова зона. Також є багато зелених насаджень на подвір\'ї .Зручний заїзд з вул. Виговського та з вулиці Щирецька.Поруч з будинком дитячний майданчик.');

-- --------------------------------------------------------

--
-- Структура таблиці `properties`
--

CREATE TABLE `properties` (
  `property_id` int(11) NOT NULL,
  `address` varchar(255) NOT NULL,
  `area` int(11) NOT NULL,
  `floor` int(11) NOT NULL,
  `rooms` int(11) NOT NULL,
  `type_id` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп даних таблиці `properties`
--

INSERT INTO `properties` (`property_id`, `address`, `area`, `floor`, `rooms`, `type_id`, `owner_id`, `price`) VALUES
(22, 'Львів, вул. Довженка 5', 80, 2, 1, 2, 1, 130000.00),
(23, 'вулиця Дзиндри, Львів', 350, 2, 5, 2, 1, 150000.00),
(24, 'вулиця Ткацька, 5, Львів', 140, 2, 4, 2, 1, 90000.00),
(25, 'Львів, вул. 6-й Скнилівський провулок, 9', 165, 2, 5, 2, 7, 290000.00),
(33, 'Львів, вул. Кульпарківська, 64а', 67, 3, 1, 1, 1, 107000.00);

-- --------------------------------------------------------

--
-- Структура таблиці `propertytypes`
--

CREATE TABLE `propertytypes` (
  `type_id` int(11) NOT NULL,
  `type_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп даних таблиці `propertytypes`
--

INSERT INTO `propertytypes` (`type_id`, `type_name`) VALUES
(1, 'Квартира'),
(2, 'Будинок'),
(3, 'Новобудова');

-- --------------------------------------------------------

--
-- Структура таблиці `property_photos`
--

CREATE TABLE `property_photos` (
  `photo_id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `file_path` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп даних таблиці `property_photos`
--

INSERT INTO `property_photos` (`photo_id`, `property_id`, `file_path`) VALUES
(82, 22, 'pics/houses/22/1000032864.jpg'),
(83, 22, 'pics/houses/22/1000032866.jpg'),
(84, 22, 'pics/houses/22/1000032867.jpg'),
(85, 22, 'pics/houses/22/1000032868.jpg'),
(86, 22, 'pics/houses/22/1000032871.jpg'),
(87, 22, 'pics/houses/22/1000032873.jpg'),
(88, 22, 'pics/houses/22/1000032878.jpg'),
(89, 22, 'pics/houses/22/1000032881.jpg'),
(90, 22, 'pics/houses/22/1000032882.jpg'),
(91, 23, 'pics/houses/23/1.jpg'),
(92, 23, 'pics/houses/23/1000043610-1170x785.jpg'),
(93, 23, 'pics/houses/23/1000043618-1170x785.jpg'),
(94, 23, 'pics/houses/23/1000043624-1170x785.jpg'),
(95, 23, 'pics/houses/23/5.jpg'),
(96, 23, 'pics/houses/23/4.jpg'),
(97, 23, 'pics/houses/23/2.jpg'),
(98, 23, 'pics/houses/23/3.jpg'),
(99, 23, 'pics/houses/23/x1000043619-1170x785.jpg.pagespeed.ic.p6ZYHWKSGf.jpg'),
(100, 24, 'pics/houses/24/1.jpg'),
(101, 24, 'pics/houses/24/12.jpg'),
(102, 24, 'pics/houses/24/13.jpg'),
(103, 24, 'pics/houses/24/14.jpg'),
(104, 24, 'pics/houses/24/15.jpg'),
(105, 24, 'pics/houses/24/16.jpg'),
(106, 24, 'pics/houses/24/17.jpg'),
(107, 24, 'pics/houses/24/18.jpg'),
(108, 24, 'pics/houses/24/19.jpg'),
(109, 24, 'pics/houses/24/20.jpg'),
(110, 25, 'pics/houses/25/1.jpg'),
(111, 25, 'pics/houses/25/2.jpg'),
(112, 25, 'pics/houses/25/3.jpg'),
(113, 25, 'pics/houses/25/4.jpg'),
(114, 25, 'pics/houses/25/5.jpg'),
(115, 25, 'pics/houses/25/6.jpg'),
(185, 33, 'pics/flat/33/6841f4b64c679_16.jpg'),
(186, 33, 'pics/flat/33/6841f4b64cb82_17.jpg'),
(187, 33, 'pics/flat/33/6841f4b64d16e_18.jpg'),
(188, 33, 'pics/flat/33/6841f4b64dc02_19.jpg'),
(189, 33, 'pics/flat/33/6841f4b64e50b_20.jpg'),
(190, 33, 'pics/flat/33/6841f4b64ed6e_21.jpg'),
(191, 33, 'pics/flat/33/6841f4b64f3e3_22.jpg'),
(192, 33, 'pics/flat/33/6841f4b64fa8d_23.jpg'),
(193, 33, 'pics/flat/33/6841f4b650259_24.jpg'),
(194, 33, 'pics/flat/33/6841f4b65080a_25.jpg'),
(195, 33, 'pics/flat/33/6841f4b650e40_26.jpg'),
(196, 33, 'pics/flat/33/6841f4b6514b1_27.jpg'),
(197, 33, 'pics/flat/33/6841f4b651c2c_28.jpg'),
(198, 33, 'pics/flat/33/6841f4b6524ea_29.jpg'),
(199, 33, 'pics/flat/33/6841f4b652d0a_31.jpg'),
(200, 33, 'pics/flat/33/6841f51d18441_16.jpg'),
(201, 33, 'pics/flat/33/6841f51d18a96_17.jpg'),
(202, 33, 'pics/flat/33/6841f51d19160_18.jpg'),
(203, 33, 'pics/flat/33/6841f51d1991e_19.jpg'),
(204, 33, 'pics/flat/33/6841f51d1a1c3_20.jpg'),
(205, 33, 'pics/flat/33/6841f51d1aa7c_21.jpg'),
(206, 33, 'pics/flat/33/6841f51d1b2af_22.jpg'),
(207, 33, 'pics/flat/33/6841f51d1ba6a_23.jpg'),
(208, 33, 'pics/flat/33/6841f51d1c53a_24.jpg'),
(209, 33, 'pics/flat/33/6841f51d1cbb7_25.jpg'),
(210, 33, 'pics/flat/33/6841f51d1d297_26.jpg'),
(211, 33, 'pics/flat/33/6841f51d1dad0_27.jpg'),
(212, 33, 'pics/flat/33/6841f51d1e309_28.jpg'),
(213, 33, 'pics/flat/33/6841f51d1eb87_29.jpg'),
(214, 33, 'pics/flat/33/6841f51d1f330_31.jpg');

-- --------------------------------------------------------

--
-- Структура таблиці `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('user','admin') NOT NULL DEFAULT 'user',
  `phone` varchar(13) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп даних таблиці `users`
--

INSERT INTO `users` (`user_id`, `username`, `password_hash`, `email`, `role`, `phone`) VALUES
(1, 'Дмитро', '$2y$10$gslcmtbsJNDpD4TQbwB/s.wpeQrDRW43lB3Q6/hIQLshfDrdNkFMa', 'dima1990gor@gmail.com', 'user', '+380993235480'),
(3, 'Діма', '$2y$10$8laa.s/V5JHxtiHv/qvEcedk4dyFTTflAonAu9Yq.U5GzYUY0UBhm', 'dmytro.horbach.kb.2023@lpnu.ua', 'user', '+380993235489'),
(6, 'Влад', '$2y$10$WqtrRvahnewEd8Nyb84yYuGDsgaE7VjH16T2Sq2g7qbEAaTC5GJdu', 'gorbachdima59@gmail.com', 'user', '+380993235480'),
(7, 'Dmytro', '$2y$10$QksfNJ8.rkanPXFN6d0W5O3zCy2sdB4VtH1rMRxKotGY7LmbsMrD6', 'gorbach2006@gmail.com', 'user', '+380993235489');

--
-- Індекси збережених таблиць
--

--
-- Індекси таблиці `agents`
--
ALTER TABLE `agents`
  ADD PRIMARY KEY (`agent_id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Індекси таблиці `deals`
--
ALTER TABLE `deals`
  ADD PRIMARY KEY (`deal_id`),
  ADD KEY `buyer_id` (`buyer_id`),
  ADD KEY `property_id` (`property_id`),
  ADD KEY `agent_id` (`agent_id`);

--
-- Індекси таблиці `flat_details`
--
ALTER TABLE `flat_details`
  ADD PRIMARY KEY (`detail_id`),
  ADD KEY `property_id` (`property_id`);

--
-- Індекси таблиці `house_details`
--
ALTER TABLE `house_details`
  ADD PRIMARY KEY (`property_id`);

--
-- Індекси таблиці `properties`
--
ALTER TABLE `properties`
  ADD PRIMARY KEY (`property_id`),
  ADD KEY `type_id` (`type_id`),
  ADD KEY `owner_id` (`owner_id`);

--
-- Індекси таблиці `propertytypes`
--
ALTER TABLE `propertytypes`
  ADD PRIMARY KEY (`type_id`);

--
-- Індекси таблиці `property_photos`
--
ALTER TABLE `property_photos`
  ADD PRIMARY KEY (`photo_id`),
  ADD KEY `property_id` (`property_id`);

--
-- Індекси таблиці `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT для збережених таблиць
--

--
-- AUTO_INCREMENT для таблиці `agents`
--
ALTER TABLE `agents`
  MODIFY `agent_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблиці `deals`
--
ALTER TABLE `deals`
  MODIFY `deal_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблиці `flat_details`
--
ALTER TABLE `flat_details`
  MODIFY `detail_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT для таблиці `properties`
--
ALTER TABLE `properties`
  MODIFY `property_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT для таблиці `propertytypes`
--
ALTER TABLE `propertytypes`
  MODIFY `type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT для таблиці `property_photos`
--
ALTER TABLE `property_photos`
  MODIFY `photo_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=221;

--
-- AUTO_INCREMENT для таблиці `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Обмеження зовнішнього ключа збережених таблиць
--

--
-- Обмеження зовнішнього ключа таблиці `agents`
--
ALTER TABLE `agents`
  ADD CONSTRAINT `agents_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Обмеження зовнішнього ключа таблиці `deals`
--
ALTER TABLE `deals`
  ADD CONSTRAINT `deals_ibfk_1` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `deals_ibfk_2` FOREIGN KEY (`property_id`) REFERENCES `properties` (`property_id`),
  ADD CONSTRAINT `deals_ibfk_3` FOREIGN KEY (`agent_id`) REFERENCES `agents` (`agent_id`);

--
-- Обмеження зовнішнього ключа таблиці `flat_details`
--
ALTER TABLE `flat_details`
  ADD CONSTRAINT `flat_details_ibfk_1` FOREIGN KEY (`property_id`) REFERENCES `properties` (`property_id`) ON DELETE CASCADE;

--
-- Обмеження зовнішнього ключа таблиці `house_details`
--
ALTER TABLE `house_details`
  ADD CONSTRAINT `house_details_ibfk_1` FOREIGN KEY (`property_id`) REFERENCES `properties` (`property_id`) ON DELETE CASCADE;

--
-- Обмеження зовнішнього ключа таблиці `properties`
--
ALTER TABLE `properties`
  ADD CONSTRAINT `properties_ibfk_1` FOREIGN KEY (`type_id`) REFERENCES `propertytypes` (`type_id`),
  ADD CONSTRAINT `properties_ibfk_2` FOREIGN KEY (`owner_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `properties_ibfk_3` FOREIGN KEY (`owner_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `properties_ibfk_4` FOREIGN KEY (`owner_id`) REFERENCES `users` (`user_id`);

--
-- Обмеження зовнішнього ключа таблиці `property_photos`
--
ALTER TABLE `property_photos`
  ADD CONSTRAINT `property_photos_ibfk_1` FOREIGN KEY (`property_id`) REFERENCES `properties` (`property_id`) ON DELETE CASCADE;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
