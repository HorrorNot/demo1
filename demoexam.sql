-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Хост: MySQL-8.4:3306
-- Время создания: Май 30 2026 г., 08:58
-- Версия сервера: 8.4.7
-- Версия PHP: 8.4.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `demoexam`
--

-- --------------------------------------------------------

--
-- Структура таблицы `request`
--

CREATE TABLE `request` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `date` datetime NOT NULL,
  `status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Новая',
  `curses` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `review` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `request`
--

INSERT INTO `request` (`id`, `user_id`, `date`, `status`, `curses`, `payment`, `review`) VALUES
(17, 15, '2026-06-17 12:55:00', 'Мероприятие назначено', 'Коворкинг', 'перевод', 'zb');

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `fullname` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `login` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_admin` tinyint(1) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `review` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `fullname`, `phone`, `email`, `login`, `password`, `is_admin`, `created_at`, `review`) VALUES
(1, 'in', '+7(123)-456-78-90', '123@mail.ru', 'Admin26', '$2y$12$FoElWYpny0S0/nnT5BITTOTubAIy1ioPYNkluIY6.RwRoAsvnZYCG', 1, '2025-10-09 15:57:44', NULL),
(13, 'xhx sgsgf sgdsdg', '+7(987)980-09-09', 'qw@bk.ru', 'qwerty', '12345678', 0, '2026-05-30 05:36:24', NULL),
(14, 'апро апро апро', '+7(987)980-09-09', 'w@bk.ru', 'asdfghj', '$2y$12$agbtQcr3GDm1EwBLmHuY/.sP2eA2dYRiQ4/8AwuAWryt.qP.wtjcK', 0, '2026-05-30 05:42:42', NULL),
(15, 'jhd xjgk jkgbkj', '+7(456)456-45-45', 'q@bk.ru', 'zxcvbn', '$2y$12$.eIznvyLtcMV8/NsmD4/XugYsSyhHLoQbhIs5RBXLxSZZyQ2QHZTm', 0, '2026-05-30 05:44:40', NULL),
(16, 'пыпы ыпы ыпып', '+7(098)090-00-00', 's@bk.ru', 'lkjhgfd', '$2y$12$wMZhf7uqtZSVBA1QOQkJ.ONToxwFs/5tPxxJrK./I7NmhpfRv4ZyG', 0, '2026-05-30 05:49:02', NULL),
(17, 'ып ып ып', '+7(123)123-12-12', 'k@bk.ru', 'poiuyt', '12345678', 0, '2026-05-30 05:55:12', NULL),
(18, 'ртад оатв чпотвт', '+7(000)000-00-00', 'as@bk.ru', 'tyuiop', '$2y$12$MfA4HC/.TWPNrldQFm3p3.Zh.0fRrL2cP83JYxYBvOJfvlD/cNXjK', 0, '2026-05-30 05:58:04', NULL);

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `request`
--
ALTER TABLE `request`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `login` (`login`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `request`
--
ALTER TABLE `request`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `request`
--
ALTER TABLE `request`
  ADD CONSTRAINT `request_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
