
# DevHub

**Версия:** 0.8.0-beta

> Платформа для публикации и обмена знаниями в сфере разработки и искусственного интеллекта

---

## Ссылка на проект

🔗 [http://h9998985.beget.tech/index.php](http://h9998985.beget.tech/index.php)

> Учебный проект на бесплатном хостинге. Ссылка может быть неактивна при отсутствии активности или смене домена.

---

## О проекте

DevHub — это веб-платформа, созданная для публикации технических статей, обсуждения вопросов разработки и обмена опытом в IT-сфере. Проект разработан в рамках проектной работы и демонстрирует полноценный цикл разработки веб-приложения: от проектирования базы данных (позднее будет так же добавлены файлы проектирования функций) до реализации сложных функций поиска и администрирования.

**Ключевые возможности:**
- Регистрация и авторизация пользователей
- Создание публикаций с поддержкой тегов и изображений
- Система черновиков
- Комментирование и лайки
- Оценка постов (рейтинг 0.5-5 звёзд)
- Поиск с ранжированием по релевантности
- Административная панель с аналитикой

---

## Технологический стек

| Компонент | Технология | Версия |
|-----------|------------|--------|
| **Backend** | PHP | 5.6 |
| **Database** | MySQL | 5.6 |
| **Frontend** | HTML5, CSS3, JavaScript | ES6 |
| **Графики** | Chart.js, Plotly.js | 4.4, 3.0 |
| **Шрифты** | Google Fonts (Inter) | — |
| **Сервер** | Apache (OS Panel / Beget) | — |

---

## Структура проекта

```
DevHub/
├── 📁 CSS/                      # Стили компонентов
│   ├── categories.css           # Кнопки категорий
│   ├── comments.css             # Комментарии и лайки
│   ├── components.css           # Кнопки, формы, сообщения
│   ├── create_pub_forms.css     # Форма создания публикации
│   ├── layout.css               # Контейнеры .container и .box
│   ├── modal_overlay.css        # Модальные окна
│   ├── nav.css                  # Верхняя навигация
│   ├── post_cards.css           # Карточки постов
│   ├── post_page.css            # Страница отдельного поста
│   ├── rating.css               # Звёздный рейтинг
│   ├── reset.css                # Сброс стилей
│   ├── search.css               # Поиск с выпадающим меню
│   └── typography.css           # Заголовки и текст
│
├── 📁 JS/                       # Клиентские скрипты
│   ├── comments_ajax.js         # AJAX для комментариев (polling)
│   ├── post_rating.js           # Звёздный рейтинг
│   ├── search_ui.js             # UI поиска с фильтрами
│   └── tag_selector.js          # Выбор тегов (модальное окно)
│
├── 📁 adminpanel/               # Административная панель
│   ├── adm_comments.php         # Управление комментариями
│   ├── adm_index.php            # Дашборд с аналитикой
│   ├── adm_posts.php            # Управление постами
│   ├── adm_tags.php             # Управление тегами
│   ├── adm_user_details.php     # Детальная информация о пользователе
│   ├── adm_users.php            # Управление пользователями
│   ├── 📁 css/
│   │   └── admin.css            # Стили админ-панели
│   └── 📁 includes/
│       ├── auth.php             # Проверка авторизации админа
│       ├── functions.php        # Вспомогательные функции
│       └── adm_charts.js        # Функции для создания графиков
|   
├── 📁 ajax/                     # AJAX-обработчики
│   ├── add_comment.php          # Добавление комментария
│   ├── delete_comment.php       # Удаление комментария
│   ├── get_new_comments.php     # Polling новых комментариев
│   ├── like_comment.php         # Лайки комментариев
│   ├── rate_post.php            # Оценка поста (звёзды)
│   └── search_posts.php         # Поиск постов (AJAX)
│
├── 📁 icons/                    # Иконки
│   ├── 📁 anim/                 # Анимированные GIF
│   └── 📁 static/               # Статические PNG/JPEG
│
├── 📁 uploads/                  # Загруженные изображения постов
│
├── index.php                    # Главная страница
├── post.php                     # Просмотр поста
├── cabinet.php                  # Личный кабинет
├── login.php                    # Авторизация
├── register.php                 # Регистрация
├── logout.php                   # Выход из системы
├── create_publication.php       # Создание публикации
├── drafts.php                   # Черновики
├── edit_prof.php                # Редактирование профиля
├── header.php                   # Шапка сайта (навигация)
├── config.php                   # Конфигурация БД (НЕ в Git)
├── config.example.php           # Пример конфигурации (в Git)
├── search_engine_mdl.php        # Поисковый движок (ранжирование)
└── .gitignore                   # Исключения для Git
```

---

## 🔧 Основные функции

### Пользователи
- Регистрация и авторизация
- Личный кабинет с постами пользователя
- Редактирование профиля (логин, имя, пароль)

### Посты
- Создание публикаций с заголовком и содержанием
- Выбор категории: **Разработка** или **Промпты**
- Выбор тегов (модальное окно с категориями тегов)
- Загрузка изображения для карточки поста
- Черновики (неопубликованные посты)
- Рейтинг постов (звёзды 0.5–5)

### Комментарии и лайки
- Комментирование постов (AJAX, без перезагрузки)
- Лайки комментариев (AJAX)
- Polling (автообновление комментариев каждые 5 секунд)

### Поиск
- Поиск по заголовку, содержанию и тегам
- Ранжирование по весу (заголовок ×10, теги ×5, содержание ×1)
- Фильтрация по дате, рейтингу, количеству комментариев
- Сортировка по дате/рейтингу/комментариям (возрастание/убывание)

### Административная панель
- Доступ по специальному логину и паролю, указанным в `config.php`
- Общая статистика (пользователи, посты, черновики, комментарии, лайки)
- Топ-10 постов по рейтингу и комментариям
- Топ-10 авторов
- Топ-30 тегов
- Графики (Chart.js и Plotly.js)
- Управление пользователями, постами, тегами, комментариями

---

## База данных (ключевые таблицы)

| Таблица | Назначение |
|---------|------------|
| `Users` | Пользователи (логин, пароль, username, дата регистрации) |
| `Posts` | Посты (заголовок, содержание, рейтинг, статус, изображение) |
| `Comments` | Комментарии (содержание, дата, количество лайков) |
| `comments_likes` | Лайки комментариев (связь пользователя и комментария) |
| `post_rates` | Оценки постов (связь пользователя и поста) |
| `Tags` | Теги (название, категория) |
| `tags_catg` | Категории тегов (цвет, порядок сортировки) |
| `tags_posts` | Связь тегов с постами |
| `posts_catg` | Категории постов (Разработка, Промпты) |

---

## Безопасность

- Пароли хранятся в открытом виде (учебный проект — не для продакшена)
- Сессии для авторизации пользователей и администратора
- `config.php` исключён из Git (`.gitignore`)
- Пример конфигурации в `config.example.php`

---

## Установка и запуск (локально)

1. **Клонировать репозиторий**
   ```bash
   git clone https://github.com/Clafordisment/DevHub.git
   ```

2. **Настроить базу данных**
   - Импортировать `DevHub_mainDB.sql` в MySQL (phpMyAdmin)
   - Создать `config.php` из `config.example.php` и заполнить данными БД

3. **Настроить веб-сервер** (OS Panel / OpenServer / XAMPP)
   - Корневая директория: папка проекта
   - PHP 5.6 или выше

4. **Запустить сайт**
   ```
   http://localhost/index.php
   ```

---

## Авторы

- **Clafordisment** — разработка и документация

---

## Лицензия

Учебный проект. Не предназначен для коммерческого использования.

---

## Использование ресурсов и технологий:

- Библиотеки: Chart.js, Plotly.js, Google Fonts
- Хостинг: Beget
- Локальная среда: OS Panel

---

## 📖 Документирование функций

### 1. Регистрация пользователя (`register.php`)

```php
// Проверка на существование логина
$check_login_sql = "SELECT login FROM Users WHERE login = '$login'";

// Вставка нового пользователя
$sql = "INSERT INTO Users (login, password, create_at) VALUES ('$login', '$password', NOW())";
```

### 2. Авторизация (`login.php`)

```php
// Проверка пользователя в БД
$sql = "SELECT Id_U, login, username FROM Users WHERE login='$login' AND password='$password' LIMIT 1";

// Проверка на администратора
if ($login === 'devAdminHubber') {
    $_SESSION['is_admin'] = true;
    header("Location: adminpanel/adm_index.php");
} else {
    header("Location: cabinet.php");
}
```

### 3. Поисковый движок (`search_engine_mdl.php`)

```php
// Расчёт веса релевантности
private function calculateWeight($post, $keywords) {
    $weight = 0;
    $title = mb_strtolower($post['title']);
    $content = mb_strtolower(strip_tags($post['content']));
    
    foreach ($keywords as $keyword) {
        $weight += substr_count($title, $keyword) * 10;  // Заголовок ×10
        $weight += substr_count($content, $keyword) * 1; // Содержание ×1
        // Теги ×5 (если есть)
    }
    return $weight;
}
```

### 4. Комментарии с polling (`comments_ajax.js`)

```javascript
// Polling — проверка новых комментариев каждые 5 секунд
startPolling() {
    this.lastCommentId = this.getLastCommentId();
    this.pollingInterval = setInterval(() => this.checkNewComments(), 5000);
}

// Добавление комментария через AJAX
async submitComment() {
    const response = await fetch('ajax/add_comment.php', {
        method: 'POST',
        body: JSON.stringify({ post_id: this.postId, content: content })
    });
}
```

### 5. Рейтинг постов (`post_rating.js` + `rate_post.php`)

```javascript
// Отправка оценки
async submitRating(rating) {
    const response = await fetch('ajax/rate_post.php', {
        method: 'POST',
        body: JSON.stringify({ post_id: postId, rating: rating })
    });
}
```

```php
// Сохранение оценки (обновление или вставка)
if ($checkResult && $checkResult->num_rows > 0) {
    $updateSql = "UPDATE post_rates SET rate = $rating WHERE id_p = $postId AND id_u = $userId";
} else {
    $insertSql = "INSERT INTO post_rates (id_p, id_u, rate) VALUES ($postId, $userId, $rating)";
}
// Пересчёт среднего рейтинга
$avgSql = "SELECT AVG(rate) as avg_rate, COUNT(*) as count FROM post_rates WHERE id_p = $postId";
```

### 6. Административная проверка (`adminpanel/includes/auth.php`)

```php
function isAdminLoggedIn() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}

function requireAdminAuth() {
    if (!isAdminLoggedIn()) {
        header('Location: ../login.php');
        exit;
    }
}
```

### 7. Графики в админ-панели (`adm_charts.js`)

```javascript
// Показ/скрытие графика при нажатии кнопки
if (!isVisible) {
    this.showChart(chartId, chartType, chartContainer, topList, btn, section);
} else {
    this.hideChart(chartContainer, topList, btn, section);
}
```

### 8. Теги в карточках постов (`search_engine_mdl.php`)

```php
// Рандомный выбор 3 тегов для карточки
private function getPostTags($postId) {
    // Получаем все теги поста
    // Перемешиваем shuffle($allTags)
    // Берём первые 3 array_slice($allTags, 0, 3)
}
```

*Последнее обновление: 18 апреля 2026*
