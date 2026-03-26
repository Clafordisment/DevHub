-- phpMyAdmin SQL Dump
-- version 4.7.3
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1:4306
-- Время создания: Мар 26 2026 г., 17:15
-- Версия сервера: 5.6.37
-- Версия PHP: 5.5.38

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `DevHub_mainDB`
--

-- --------------------------------------------------------

--
-- Структура таблицы `Comments`
--

CREATE TABLE `Comments` (
  `id_c` int(11) NOT NULL,
  `id_p` int(11) NOT NULL,
  `id_u` int(11) NOT NULL,
  `content` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `issued_rate` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `Comments`
--

INSERT INTO `Comments` (`id_c`, `id_p`, `id_u`, `content`, `created_at`, `issued_rate`) VALUES
(20, 6, 5, 'А я вочиноубароу', '2026-03-18 16:40:22', 0);

-- --------------------------------------------------------

--
-- Структура таблицы `Posts`
--

CREATE TABLE `Posts` (
  `id_p` int(11) NOT NULL,
  `id_type` int(11) DEFAULT NULL,
  `id_u` int(11) NOT NULL,
  `title` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT 'заголовок',
  `content` longtext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT 'содержание',
  `create_at` timestamp NOT NULL COMMENT 'дата создания',
  `avRate` tinyint(4) DEFAULT NULL COMMENT 'Рейтинг (0/10)',
  `isNote` tinyint(1) DEFAULT NULL,
  `ownPrev` blob
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `Posts`
--

INSERT INTO `Posts` (`id_p`, `id_type`, `id_u`, `title`, `content`, `create_at`, `avRate`, `isNote`, `ownPrev`) VALUES
(6, 1, 2, 'Какой-то опубликован', 'Сгенеренный текст из ворда (путём =rand()):\r\nВидео предоставляет прекрасную возможность подтвердить свою точку зрения. Чтобы вставить код внедрения для видео, которое вы хотите добавить, нажмите \"Видео в сети\".Вы также можете ввести ключевое слово, чтобы найти в Интернете видео, которое лучше всего подходит для вашего документа.\r\nЧтобы придать документу профессиональный вид, воспользуйтесь доступными в Word макетами верхних и нижних колонтитулов, титульной страницы и текстовых полей, которые дополняют друг друга. Например, вы можете добавить подходящую титульную страницу, верхний колонтитул и боковое примечание. Откройте вкладку \"Вставка\" и выберите нужные элементы из различных коллекций.\r\nТемы и стили также помогают придать документу единообразный вид. Если на вкладке \"Конструктор\" выбрать другую тему, то изображения, диаграммы и графические элементы SmartArt изменятся соответствующим образом.При применении стилей заголовки изменяются в соответствии с новой темой.\r\nНовые кнопки, которые видны, только если они действительно нужны, экономят время при работе в Word.Чтобы изменить расположение рисунка в документе, щелкните его, и рядом с ним появится кнопка для доступа к параметрам разметки. При работе с таблицей щелкните то место, куда нужно добавить строку или столбец, и щелкните знак \"плюс\".\r\nЧитать тоже стало проще благодаря новому режиму чтения. Можно свернуть части документа, чтобы сосредоточиться на нужном фрагменте текста. Если вы прервете чтение, не дойдя до конца документа, Word запомнит, в каком месте вы остановились (даже на другом устройстве).', '2026-03-09 19:24:40', 0, 0, ''),
(8, 1, 2, 'УАщшцтацута', 'УАЦЩРШшртуацацдтлуцу', '2026-03-11 13:34:47', 0, 0, ''),
(9, 1, 2, 'Какой то черновик', 'И какой то текст черновика', '2026-03-12 18:07:40', 0, 1, ''),
(10, 1, 2, 'Ещё черновичок', 'Бу', '2026-03-12 18:21:24', 0, 1, '');

-- --------------------------------------------------------

--
-- Структура таблицы `posts_catg`
--

CREATE TABLE `posts_catg` (
  `id_PType` int(11) NOT NULL,
  `name` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `posts_catg`
--

INSERT INTO `posts_catg` (`id_PType`, `name`) VALUES
(2, 'Промпты'),
(1, 'Разработка');

-- --------------------------------------------------------

--
-- Структура таблицы `Tags`
--

CREATE TABLE `Tags` (
  `id_t` int(11) NOT NULL,
  `name` varchar(15) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `id_catg` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `Tags`
--

INSERT INTO `Tags` (`id_t`, `name`, `id_catg`) VALUES
(1, 'Python', 1),
(2, 'C', 1),
(3, 'C++', 1),
(4, 'C#', 1),
(5, 'Java', 1),
(6, 'JS', 1),
(7, 'SQL', 1),
(8, 'Delphi', 1),
(9, 'PHP', 1),
(10, 'Swift', 1),
(11, 'TypeScript', 1),
(12, 'Rust', 1),
(13, 'Go', 1),
(14, 'Kotlin', 1),
(15, 'Ruby', 1),
(16, 'Perl', 1),
(17, 'HTML', 1),
(18, 'CSS', 1),
(19, 'GPT-5.2', 2),
(20, 'GPT o3', 2),
(21, 'GPT o4', 2),
(22, 'GPT o3-mini', 2),
(23, 'GPT o4-mini', 2),
(24, 'Llama 4 Maveric', 2),
(25, 'Claude Sonnet', 2),
(26, 'Claude Opus', 2),
(27, 'DeepSeek', 2),
(28, 'Qwen', 2),
(29, 'Grok-3', 2),
(30, 'Grok-4', 2),
(31, 'Gemini 2.5', 2),
(32, 'Gemini 3', 2),
(33, 'Mistral', 2),
(34, 'Cursor', 3),
(35, 'Copilot', 3),
(36, 'Qodo', 3),
(37, 'Windsurf', 3),
(38, 'VSCode', 4),
(39, 'Visual Studio', 4),
(40, 'IntelliJ IDEA', 4),
(41, 'PyCharm', 4),
(42, 'Git', 4),
(43, 'GitHub', 4),
(44, 'GitLab', 4),
(45, 'GitLab CI/CD', 4),
(46, 'GitHub Actions', 4),
(47, 'Jenkins', 4),
(48, 'Azure DevOps', 4),
(49, 'Docker', 4),
(50, 'Kubernetes', 4),
(51, 'MAUI', 4),
(52, 'Flutter', 4),
(53, 'React Native', 4),
(54, 'Qt', 4),
(55, 'KMP', 4);

-- --------------------------------------------------------

--
-- Структура таблицы `tags_catg`
--

CREATE TABLE `tags_catg` (
  `id_catg` int(11) NOT NULL,
  `name` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `sort_order` int(11) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `tags_catg`
--

INSERT INTO `tags_catg` (`id_catg`, `name`, `sort_order`) VALUES
(1, 'Языки программирования', 1),
(2, 'Модели ИИ', 2),
(3, 'Ассистенты и агенты', 3),
(4, 'Платформы разработки и деплоя', 4);

-- --------------------------------------------------------

--
-- Структура таблицы `tags_posts`
--

CREATE TABLE `tags_posts` (
  `id_t&p` int(11) NOT NULL,
  `id_t` int(11) NOT NULL,
  `id_p` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `Users`
--

CREATE TABLE `Users` (
  `Id_U` int(11) NOT NULL,
  `username` varchar(15) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `login` varchar(20) NOT NULL,
  `password` varchar(30) NOT NULL,
  `create_at` timestamp NOT NULL,
  `avRate` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `Users`
--

INSERT INTO `Users` (`Id_U`, `username`, `email`, `login`, `password`, `create_at`, `avRate`) VALUES
(2, 'Foogoolya', '', 'Foogle_1234', '1234', '0000-00-00 00:00:00', NULL),
(5, NULL, NULL, 'Вочиноубароу', '1234', '0000-00-00 00:00:00', NULL);

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `Comments`
--
ALTER TABLE `Comments`
  ADD PRIMARY KEY (`id_c`),
  ADD KEY `id_u` (`id_u`),
  ADD KEY `id_p` (`id_p`);

--
-- Индексы таблицы `Posts`
--
ALTER TABLE `Posts`
  ADD PRIMARY KEY (`id_p`),
  ADD KEY `id_u` (`id_u`),
  ADD KEY `id_type` (`id_type`);

--
-- Индексы таблицы `posts_catg`
--
ALTER TABLE `posts_catg`
  ADD PRIMARY KEY (`id_PType`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Индексы таблицы `Tags`
--
ALTER TABLE `Tags`
  ADD PRIMARY KEY (`id_t`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `id_catg` (`id_catg`);

--
-- Индексы таблицы `tags_catg`
--
ALTER TABLE `tags_catg`
  ADD PRIMARY KEY (`id_catg`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Индексы таблицы `tags_posts`
--
ALTER TABLE `tags_posts`
  ADD PRIMARY KEY (`id_t&p`),
  ADD KEY `id_t` (`id_t`,`id_p`),
  ADD KEY `id_p` (`id_p`);

--
-- Индексы таблицы `Users`
--
ALTER TABLE `Users`
  ADD PRIMARY KEY (`Id_U`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `Comments`
--
ALTER TABLE `Comments`
  MODIFY `id_c` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;
--
-- AUTO_INCREMENT для таблицы `Posts`
--
ALTER TABLE `Posts`
  MODIFY `id_p` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
--
-- AUTO_INCREMENT для таблицы `posts_catg`
--
ALTER TABLE `posts_catg`
  MODIFY `id_PType` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT для таблицы `Tags`
--
ALTER TABLE `Tags`
  MODIFY `id_t` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;
--
-- AUTO_INCREMENT для таблицы `tags_catg`
--
ALTER TABLE `tags_catg`
  MODIFY `id_catg` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;
--
-- AUTO_INCREMENT для таблицы `tags_posts`
--
ALTER TABLE `tags_posts`
  MODIFY `id_t&p` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT для таблицы `Users`
--
ALTER TABLE `Users`
  MODIFY `Id_U` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `Comments`
--
ALTER TABLE `Comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`id_u`) REFERENCES `Users` (`Id_U`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`id_p`) REFERENCES `Posts` (`id_p`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `Posts`
--
ALTER TABLE `Posts`
  ADD CONSTRAINT `posts_ibfk_type` FOREIGN KEY (`id_type`) REFERENCES `posts_catg` (`id_PType`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `Tags`
--
ALTER TABLE `Tags`
  ADD CONSTRAINT `tags_ibfk_1` FOREIGN KEY (`id_catg`) REFERENCES `tags_catg` (`id_catg`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `tags_posts`
--
ALTER TABLE `tags_posts`
  ADD CONSTRAINT `tags_posts_ibfk_1` FOREIGN KEY (`id_t`) REFERENCES `Tags` (`id_t`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `tags_posts_ibfk_2` FOREIGN KEY (`id_p`) REFERENCES `Posts` (`id_p`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
