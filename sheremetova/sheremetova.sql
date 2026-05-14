-- phpMyAdmin SQL Dump
-- version 5.2.3-1.red80
-- https://www.phpmyadmin.net/
--
-- Хост: localhost
-- Время создания: Май 14 2026 г., 06:50
-- Версия сервера: 10.11.16-MariaDB
-- Версия PHP: 8.1.32

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `sheremetova`
--

-- --------------------------------------------------------

--
-- Структура таблицы `accruals_deductions`
--

CREATE TABLE `accruals_deductions` (
  `record_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `calc_id` int(11) DEFAULT NULL,
  `type` enum('accrual','deduction') NOT NULL,
  `code` varchar(50) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `document_date` date DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `accruals_deductions`
--

INSERT INTO `accruals_deductions` (`record_id`, `employee_id`, `calc_id`, `type`, `code`, `amount`, `document_date`, `description`, `created_at`) VALUES
(1, 2, 1, 'accrual', 'BONUS', 5000.00, '2026-03-30', 'Ежемесячная премия за выполнение KPI', '2026-04-17 08:21:43'),
(2, 3, NULL, 'accrual', 'SALAY', 6000.00, '2026-05-14', 'лялял', '2026-05-14 04:59:20');

-- --------------------------------------------------------

--
-- Структура таблицы `departments`
--

CREATE TABLE `departments` (
  `department_id` int(11) NOT NULL,
  `department_name` varchar(150) NOT NULL,
  `cost_center_code` varchar(20) DEFAULT NULL,
  `manager_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `departments`
--

INSERT INTO `departments` (`department_id`, `department_name`, `cost_center_code`, `manager_id`, `created_at`) VALUES
(1, 'Бухгалтерия', 'CC-101', 1, '2026-04-17 08:21:43'),
(2, 'Отдел кадров', 'CC-102', 3, '2026-04-17 08:21:43'),
(3, 'IT-отдел', 'CC-103', NULL, '2026-04-17 08:21:43'),
(4, 'Планово-экономический отдел', 'CC-104', NULL, '2026-04-17 08:21:43'),
(6, 'Отдел продаж и сопровождения клиентов', 'CC-SLS-004', NULL, '2026-05-07 04:51:48'),
(7, 'учебный отдел', 'СС-103', NULL, '2026-05-14 04:02:53');

-- --------------------------------------------------------

--
-- Структура таблицы `employees`
--

CREATE TABLE `employees` (
  `employee_id` int(11) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `inn` varchar(12) DEFAULT NULL,
  `snils` varchar(14) DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `hire_date` date NOT NULL,
  `dismissal_date` date DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `position_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `employees`
--

INSERT INTO `employees` (`employee_id`, `last_name`, `first_name`, `middle_name`, `inn`, `snils`, `birth_date`, `hire_date`, `dismissal_date`, `department_id`, `position_id`, `created_at`) VALUES
(1, 'Иванова', 'Анна', 'Петровна', '123456789012', '123-456-789 01', '1985-03-15', '2020-01-10', NULL, 1, 1, '2026-04-17 08:21:43'),
(2, 'Петров', 'Сергей', 'Иванович', '234567890123', '234-567-890 12', '1990-07-22', '2021-03-01', NULL, 1, 2, '2026-04-17 08:21:43'),
(3, 'Юркова', 'Алена', 'Олеговна', '345678901234', '345-678-901 23', '1988-05-05', '2022-02-15', NULL, 2, 5, '2026-04-17 08:21:43'),
(4, 'Горшанова', 'Елизавета', 'Васильевна', '738256871679', '89602968670', '2008-09-06', '2026-05-14', '2026-05-11', 3, NULL, '2026-05-14 04:58:05');

-- --------------------------------------------------------

--
-- Структура таблицы `leave_sick`
--

CREATE TABLE `leave_sick` (
  `event_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `event_type` enum('vacation','sick_leave') NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `days_count` int(3) GENERATED ALWAYS AS (to_days(`end_date`) - to_days(`start_date`) + 1) STORED,
  `payment_amount` decimal(12,2) DEFAULT 0.00,
  `basis_document_number` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `leave_sick`
--

INSERT INTO `leave_sick` (`event_id`, `employee_id`, `event_type`, `start_date`, `end_date`, `payment_amount`, `basis_document_number`, `created_at`) VALUES
(1, 2, 'sick_leave', '2026-02-10', '2026-02-15', 8500.00, 'БЛ-2026-001', '2026-04-17 08:21:43');

-- --------------------------------------------------------

--
-- Структура таблицы `positions`
--

CREATE TABLE `positions` (
  `position_id` int(11) NOT NULL,
  `position_name` varchar(150) NOT NULL,
  `salary_rate` decimal(12,2) NOT NULL DEFAULT 0.00,
  `category_code` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `positions`
--

INSERT INTO `positions` (`position_id`, `position_name`, `salary_rate`, `category_code`) VALUES
(1, 'Главный бухгалтер', 120000.00, 'MGMT'),
(2, 'Бухгалтер-расчетчик', 80000.00, 'SPEC'),
(3, 'Экономист', 75000.00, 'SPEC'),
(4, 'Программист 1С', 90000.00, 'IT'),
(5, 'Менеджер по персоналу', 70000.00, 'HR');

-- --------------------------------------------------------

--
-- Структура таблицы `salary_calc`
--

CREATE TABLE `salary_calc` (
  `calc_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `period_year` int(4) NOT NULL,
  `period_month` int(2) NOT NULL,
  `taxable_base` decimal(15,2) DEFAULT 0.00,
  `rate_percent` decimal(15,2) DEFAULT 0.00,
  `amount` decimal(15,2) DEFAULT 0.00,
  `payment_status` enum('pending','paid','overdue') DEFAULT 'pending',
  `payment_date` date DEFAULT NULL,
  `days_worked` decimal(5,2) DEFAULT 0.00,
  `hours_worked` decimal(6,2) DEFAULT 0.00,
  `base_salary` decimal(12,2) NOT NULL DEFAULT 0.00,
  `bonus` decimal(12,2) DEFAULT 0.00,
  `deduction` decimal(12,2) DEFAULT 0.00,
  `personal_income_tax` decimal(12,2) DEFAULT 0.00,
  `insurance_contributions` decimal(12,2) DEFAULT 0.00,
  `net_salary` decimal(12,2) DEFAULT 0.00,
  `calc_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `salary_calc`
--

INSERT INTO `salary_calc` (`calc_id`, `employee_id`, `period_year`, `period_month`, `taxable_base`, `rate_percent`, `amount`, `payment_status`, `payment_date`, `days_worked`, `hours_worked`, `base_salary`, `bonus`, `deduction`, `personal_income_tax`, `insurance_contributions`, `net_salary`, `calc_date`, `created_at`) VALUES
(1, 2, 2026, 3, 0.00, 0.00, 0.00, 'pending', NULL, 21.00, 168.00, 80000.00, 5000.00, 0.00, 11050.00, 24150.00, 73950.00, '2026-03-31', '2026-04-17 08:21:43'),
(2, 3, 2026, 5, 0.00, 0.00, 0.00, 'pending', NULL, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, NULL, '2026-05-14 04:37:41'),
(6, 4, 2026, 5, 0.00, 0.00, 0.00, 'pending', NULL, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, NULL, '2026-05-14 05:00:03'),
(8, 2, 2026, 5, 5000.00, 13.00, 650.00, 'pending', NULL, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, NULL, '2026-05-14 05:21:05');

-- --------------------------------------------------------

--
-- Структура таблицы `tax_contributions`
--

CREATE TABLE `tax_contributions` (
  `tax_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `period_year` int(4) NOT NULL,
  `period_month` int(2) NOT NULL,
  `tax_type` enum('NDFL','PFR','FSS','FOMS') NOT NULL,
  `taxable_base` decimal(12,2) NOT NULL DEFAULT 0.00,
  `rate_percent` decimal(5,2) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `payment_status` enum('pending','paid','overdue') DEFAULT 'pending',
  `payment_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `tax_contributions`
--

INSERT INTO `tax_contributions` (`tax_id`, `employee_id`, `period_year`, `period_month`, `tax_type`, `taxable_base`, `rate_percent`, `amount`, `payment_status`, `payment_date`, `created_at`) VALUES
(1, 2, 2026, 3, 'NDFL', 85000.00, 13.00, 11050.00, 'pending', NULL, '2026-04-17 08:21:43'),
(2, 2, 2026, 3, 'PFR', 85000.00, 22.00, 18700.00, 'pending', NULL, '2026-04-17 08:21:43'),
(3, 2, 2026, 3, 'FSS', 85000.00, 2.90, 2465.00, 'pending', NULL, '2026-04-17 08:21:43'),
(5, 3, 2026, 5, 'NDFL', 5000.00, 12.00, 600.00, 'pending', NULL, '2026-05-14 04:56:19'),
(6, 4, 2026, 5, 'NDFL', 3000.00, 13.00, 390.00, 'pending', NULL, '2026-05-14 04:58:26');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `accruals_deductions`
--
ALTER TABLE `accruals_deductions`
  ADD PRIMARY KEY (`record_id`),
  ADD KEY `idx_ad_employee` (`employee_id`),
  ADD KEY `idx_ad_calc` (`calc_id`);

--
-- Индексы таблицы `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`department_id`),
  ADD UNIQUE KEY `uk_department_name` (`department_name`),
  ADD KEY `fk_department_manager` (`manager_id`);

--
-- Индексы таблицы `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`employee_id`),
  ADD UNIQUE KEY `uk_inn` (`inn`),
  ADD UNIQUE KEY `uk_snils` (`snils`),
  ADD KEY `idx_employee_department` (`department_id`),
  ADD KEY `idx_employee_position` (`position_id`);

--
-- Индексы таблицы `leave_sick`
--
ALTER TABLE `leave_sick`
  ADD PRIMARY KEY (`event_id`),
  ADD KEY `idx_ls_employee` (`employee_id`),
  ADD KEY `idx_ls_dates` (`start_date`,`end_date`);

--
-- Индексы таблицы `positions`
--
ALTER TABLE `positions`
  ADD PRIMARY KEY (`position_id`),
  ADD UNIQUE KEY `uk_position_name` (`position_name`);

--
-- Индексы таблицы `salary_calc`
--
ALTER TABLE `salary_calc`
  ADD PRIMARY KEY (`calc_id`),
  ADD UNIQUE KEY `uk_employee_period` (`employee_id`,`period_year`,`period_month`),
  ADD KEY `idx_salary_period` (`period_year`,`period_month`);

--
-- Индексы таблицы `tax_contributions`
--
ALTER TABLE `tax_contributions`
  ADD PRIMARY KEY (`tax_id`),
  ADD KEY `idx_tax_employee` (`employee_id`),
  ADD KEY `idx_tax_period` (`period_year`,`period_month`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `accruals_deductions`
--
ALTER TABLE `accruals_deductions`
  MODIFY `record_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `departments`
--
ALTER TABLE `departments`
  MODIFY `department_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT для таблицы `employees`
--
ALTER TABLE `employees`
  MODIFY `employee_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT для таблицы `leave_sick`
--
ALTER TABLE `leave_sick`
  MODIFY `event_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT для таблицы `positions`
--
ALTER TABLE `positions`
  MODIFY `position_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT для таблицы `salary_calc`
--
ALTER TABLE `salary_calc`
  MODIFY `calc_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT для таблицы `tax_contributions`
--
ALTER TABLE `tax_contributions`
  MODIFY `tax_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `accruals_deductions`
--
ALTER TABLE `accruals_deductions`
  ADD CONSTRAINT `fk_ad_calc` FOREIGN KEY (`calc_id`) REFERENCES `salary_calc` (`calc_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ad_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `departments`
--
ALTER TABLE `departments`
  ADD CONSTRAINT `fk_department_manager` FOREIGN KEY (`manager_id`) REFERENCES `employees` (`employee_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `fk_employee_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_employee_position` FOREIGN KEY (`position_id`) REFERENCES `positions` (`position_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `leave_sick`
--
ALTER TABLE `leave_sick`
  ADD CONSTRAINT `fk_ls_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `salary_calc`
--
ALTER TABLE `salary_calc`
  ADD CONSTRAINT `fk_salary_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `tax_contributions`
--
ALTER TABLE `tax_contributions`
  ADD CONSTRAINT `fk_tax_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
