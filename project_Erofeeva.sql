-- phpMyAdmin SQL Dump
-- version 5.1.1deb5ubuntu1
-- https://www.phpmyadmin.net/
--
-- Хост: localhost:3306
-- Время создания: Июн 06 2026 г., 11:53
-- Версия сервера: 10.6.23-MariaDB-0ubuntu0.22.04.1
-- Версия PHP: 8.1.2-1ubuntu2.24

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `project_Erofeeva`
--

DELIMITER $$
--
-- Процедуры
--
CREATE DEFINER=`Erofeeva`@`%` PROCEDURE `AddCustomers` (IN `p_First_name` VARCHAR(15), IN `p_Last_name` VARCHAR(15), IN `p_Patronymic` VARCHAR(15), IN `p_Phone` VARCHAR(20), IN `p_Email` VARCHAR(30), IN `p_Birthday` DATE, IN `p_Place_of_work` VARCHAR(20))  BEGIN
    INSERT INTO Customers (First_name, Last_name, Patronymic, Phone, Email, Birthday, Place_of_work)
    VALUES (p_First_name, p_Last_name, p_Patronymic, p_Phone, p_Email, p_Birthday, p_Place_of_work);
END$$

CREATE DEFINER=`Erofeeva`@`%` PROCEDURE `AddEmployees` (IN `p_ID_professions` INT, IN `p_First_name` VARCHAR(15), IN `p_Last_name` VARCHAR(15), IN `p_Patronymic` VARCHAR(15), IN `p_Phone` VARCHAR(20), IN `p_Email` VARCHAR(30), IN `p_Birthday` DATE)  BEGIN
    INSERT INTO Employees (ID_professions, First_name, Last_name, Patronymic, Phone, Email, Birthday)
    VALUES (p_ID_professions, p_First_name, p_Last_name, p_Patronymic, p_Phone, p_Email, p_Birthday);

END$$

CREATE DEFINER=`Erofeeva`@`%` PROCEDURE `UpdateProjectStatus` ()  BEGIN
    UPDATE project_Erofeeva.Project
    SET Status = 'Просрочен'
    WHERE End_date < CURRENT_DATE()
      AND Status != 'Просрочен';    
END$$

--
-- Функции
--
CREATE DEFINER=`Erofeeva`@`%` FUNCTION `CountEmployeesTask` (`p_Employee_ID` INT) RETURNS INT(11) BEGIN
    DECLARE task_count INT;
    SELECT COUNT(*) INTO task_count
    FROM Employees e
    JOIN Project p ON e.id = p.ID_Employees   
    JOIN Tasks t ON p.id = t.ID_project
    WHERE e.ID = ID_Employees;
    
    RETURN task_count;
END$$

CREATE DEFINER=`Erofeeva`@`%` FUNCTION `CountEmployeesTask1` (`p_Employee_ID` INT) RETURNS INT(11) BEGIN
    DECLARE task_count INT;
    SELECT COUNT(*) INTO task_count
    FROM Tasks t
    JOIN Employees e ON t.ID_Employees = e.ID
    WHERE e.ID = ID_Employees;
    
    RETURN task_count;
END$$

CREATE DEFINER=`Erofeeva`@`%` FUNCTION `CountEmployeesTasks` (`p_Employee_ID` INT) RETURNS INT(11) BEGIN
    DECLARE task_count INT;
    SELECT COUNT(*) INTO task_count
    FROM Employees e
    JOIN Project p ON e.id = p.ID_Employees   
    JOIN Tasks t ON p.id = t.ID_project
    WHERE e.ID = Employees_ID;
    
    RETURN task_count;
END$$

CREATE DEFINER=`Erofeeva`@`%` FUNCTION `CountEmployeesTasks1` (`p_Employee_ID` INT) RETURNS INT(11) BEGIN
    DECLARE task_count INT;
    
    SELECT COUNT(*) INTO task_count
    FROM Tasks
    WHERE ID_Employees = p_Employee_ID; 
    
    RETURN task_count;
END$$

CREATE DEFINER=`Erofeeva`@`%` FUNCTION `CountEmployeeTasks` (`p_Employee_ID` INT) RETURNS INT(11) BEGIN
    DECLARE task_count INT;
    SELECT COUNT(*) INTO task_count
    FROM Employees e
    JOIN Project p ON e.id = p.ID_Employees   
    JOIN Tasks t ON p.id = t.ID_project
    WHERE e.ID = Employee_ID;
    
    RETURN task_count;
END$$

CREATE DEFINER=`Erofeeva`@`%` FUNCTION `CountOverdueProjects` () RETURNS INT(11) BEGIN
    DECLARE project_count INT;
    SELECT COUNT(*) INTO project_count
    FROM Project
    WHERE Status = 'Просрочен';
    RETURN project_count;
END$$

CREATE DEFINER=`Erofeeva`@`%` FUNCTION `InfoProject` (`p_Project_ID` INT) RETURNS VARCHAR(255) CHARSET utf8mb4 COLLATE utf8mb4_unicode_ci BEGIN
    DECLARE project_info VARCHAR(255);
    SELECT CONCAT('Название: ', Name, 'Стоимость: ', Cost, ' ', Start_date, '-', End_date, ' ', Status) INTO project_info
    FROM Project
    WHERE ID = p_Project_ID;
    RETURN project_info;
END$$

CREATE DEFINER=`Erofeeva`@`%` FUNCTION `ProjectInfo` (`p_Project_ID` INT) RETURNS VARCHAR(255) CHARSET utf8mb4 COLLATE utf8mb4_unicode_ci BEGIN
    DECLARE project_info VARCHAR(255);
    
    SELECT CONCAT( 'ID: ', ID, ', Название: ', Name, ', Заказчик: ', ID_Customers,  ', Стоимость: ', Cost, ', Начало: ', Start_date, ', Конец: ', End_date, ', Статус: ', Status ) INTO project_info
    FROM Project
    WHERE ID = p_Project_id;
    
    RETURN project_info;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Структура таблицы `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `inn` varchar(255) DEFAULT NULL,
  `addres` varchar(255) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `salesman` tinyint(1) NOT NULL,
  `buyer` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `customers`
--

INSERT INTO `customers` (`id`, `name`, `inn`, `addres`, `phone`, `salesman`, `buyer`) VALUES
(1, 'ООО \"Поставка\"', '', 'г.Пятигорск', '+79198634592', 1, 1),
(2, 'ООО \"Кинотеатр Квант\"', '26320045123', 'г. Железноводск, ул. Мира, 123', '+79884581555', 1, 0),
(3, 'ООО \"Ромашка\"', '4140784214', 'г. Омск, ул. Строителей, 294', '+79882584546', 0, 1),
(8, 'ООО \"Новый JDTO\"', '26320045111', 'г. Железноводсу', '+79884581555', 1, 0),
(9, 'ООО \"Ипподром\"', '5874045632', 'г. Уфа, ул. Набережная,  37', '+79627486389', 1, 1),
(10, 'ООО \"Ассоль\"', '2629011278', 'г. Калуга, ул. Пушкина, 94', '+79184572398', 0, 1),
(11, 'ООО \"Цельсий\"', '', 'не указан', '', 0, 1);

-- --------------------------------------------------------

--
-- Структура таблицы `manufacturer`
--

CREATE TABLE `manufacturer` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `manufacturer`
--

INSERT INTO `manufacturer` (`id`, `name`) VALUES
(1, 'ООО \"Мясной цех №1\"');

-- --------------------------------------------------------

--
-- Структура таблицы `materials`
--

CREATE TABLE `materials` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(100) NOT NULL,
  `unit` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `materials`
--

INSERT INTO `materials` (`id`, `name`, `code`, `unit`) VALUES
(1, 'Молоко нормализованное', 'НФ-00000004', 'кг'),
(2, 'Говядина', 'НФ-00000005', 'кг'),
(3, 'Соль', 'НФ-00000009', 'кг'),
(4, 'Оболочка натуральная', 'НФ-00000010', 'шт');

-- --------------------------------------------------------

--
-- Структура таблицы `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `id_customers` int(11) NOT NULL,
  `number_of_order` varchar(255) NOT NULL,
  `date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `orders`
--

INSERT INTO `orders` (`id`, `id_customers`, `number_of_order`, `date`) VALUES
(1, 1, '1', '2025-06-06');

-- --------------------------------------------------------

--
-- Структура таблицы `order_line`
--

CREATE TABLE `order_line` (
  `id` int(11) NOT NULL,
  `id_order` int(11) NOT NULL,
  `id_product` int(11) NOT NULL,
  `count` decimal(10,3) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `order_line`
--

INSERT INTO `order_line` (`id`, `id_order`, `id_product`, `count`, `price`) VALUES
(1, 1, 1, '4.000', '450.00'),
(2, 1, 2, '8.000', '510.00'),
(3, 1, 3, '3.000', '370.00');

-- --------------------------------------------------------

--
-- Структура таблицы `price`
--

CREATE TABLE `price` (
  `id` int(11) NOT NULL,
  `id_material` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `date_start` date NOT NULL,
  `date_end` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `price`
--

INSERT INTO `price` (`id`, `id_material`, `price`, `date_start`, `date_end`) VALUES
(1, 1, '34.00', '2025-01-01', '9999-12-31'),
(2, 2, '370.00', '2025-01-01', '9999-12-31'),
(3, 3, '60.00', '2025-01-01', '9999-12-31'),
(4, 4, '20.00', '2025-01-01', '9999-12-31');

-- --------------------------------------------------------

--
-- Структура таблицы `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `id_manufacturer` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(100) NOT NULL,
  `count` decimal(10,3) NOT NULL,
  `unit` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `products`
--

INSERT INTO `products` (`id`, `id_manufacturer`, `name`, `code`, `count`, `unit`) VALUES
(1, 1, 'Пельмени \"Сибирские\" 900г.', 'ПР-000001', '0.000', 'шт'),
(2, 1, 'Пельмени \"Из говядины\" 900г.', 'ПР-000002', '0.000', 'шт'),
(3, 1, 'Сосиски венские 850г.', 'ПР-000003', '0.000', 'шт'),
(4, 1, 'Сосиски молочные 850г.', 'НФ-00000003', '0.000', 'шт');

-- --------------------------------------------------------

--
-- Структура таблицы `specifications`
--

CREATE TABLE `specifications` (
  `id` int(11) NOT NULL,
  `id_product` int(11) NOT NULL,
  `id_material` int(11) NOT NULL,
  `count` decimal(10,3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `specifications`
--

INSERT INTO `specifications` (`id`, `id_product`, `id_material`, `count`) VALUES
(1, 4, 1, '0.400'),
(2, 4, 2, '1.000'),
(3, 4, 3, '0.003'),
(4, 4, 4, '0.100'),
(5, 1, 2, '0.800'),
(6, 1, 3, '0.005'),
(7, 1, 4, '0.000'),
(8, 2, 2, '0.900'),
(9, 2, 3, '0.005'),
(10, 3, 2, '0.700'),
(11, 3, 1, '0.300'),
(12, 3, 3, '0.003'),
(13, 3, 4, '0.100');

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `login` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `customer_id` int(11) DEFAULT NULL,
  `is_blocked` tinyint(1) DEFAULT 0,
  `failed_attempts` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `login`, `password`, `role`, `customer_id`, `is_blocked`, `failed_attempts`) VALUES
(1, 'admin', '$2y$10$w0/dD75.7UaZ.sYwe5Hr4ugdHBUzqjiMyLUk/rJnRr1XuBAfCGHry', 'admin', NULL, 0, 0),
(2, 'user', '$2y$10$8miuEj4fLURqBLnkLbhGz./UBpeSrPmu/vhtCL6T4VfbZpF/7.SCe', 'user', 1, 0, 0),
(4, 'User1', '$2y$10$tOoyMtmrGQ4IG/DScZcVTeoESGVM9jBU7/i1gGkAlDZpEQ0gOxbtq', 'user', NULL, 1, 3);

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `manufacturer`
--
ALTER TABLE `manufacturer`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `materials`
--
ALTER TABLE `materials`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_customers` (`id_customers`);

--
-- Индексы таблицы `order_line`
--
ALTER TABLE `order_line`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_order` (`id_order`),
  ADD KEY `id_product` (`id_product`);

--
-- Индексы таблицы `price`
--
ALTER TABLE `price`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_material` (`id_material`);

--
-- Индексы таблицы `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_manufacturer` (`id_manufacturer`);

--
-- Индексы таблицы `specifications`
--
ALTER TABLE `specifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_product` (`id_product`),
  ADD KEY `id_material` (`id_material`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `login` (`login`),
  ADD KEY `customer_id` (`customer_id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT для таблицы `manufacturer`
--
ALTER TABLE `manufacturer`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT для таблицы `materials`
--
ALTER TABLE `materials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT для таблицы `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT для таблицы `order_line`
--
ALTER TABLE `order_line`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT для таблицы `price`
--
ALTER TABLE `price`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT для таблицы `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT для таблицы `specifications`
--
ALTER TABLE `specifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`id_customers`) REFERENCES `customers` (`id`);

--
-- Ограничения внешнего ключа таблицы `order_line`
--
ALTER TABLE `order_line`
  ADD CONSTRAINT `order_line_ibfk_1` FOREIGN KEY (`id_order`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `order_line_ibfk_2` FOREIGN KEY (`id_product`) REFERENCES `products` (`id`);

--
-- Ограничения внешнего ключа таблицы `price`
--
ALTER TABLE `price`
  ADD CONSTRAINT `price_ibfk_1` FOREIGN KEY (`id_material`) REFERENCES `materials` (`id`);

--
-- Ограничения внешнего ключа таблицы `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`id_manufacturer`) REFERENCES `manufacturer` (`id`);

--
-- Ограничения внешнего ключа таблицы `specifications`
--
ALTER TABLE `specifications`
  ADD CONSTRAINT `specifications_ibfk_1` FOREIGN KEY (`id_product`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `specifications_ibfk_2` FOREIGN KEY (`id_material`) REFERENCES `materials` (`id`);

--
-- Ограничения внешнего ключа таблицы `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
