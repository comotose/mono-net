# Инструкция по развертыванию и деплою MONO

Документ описывает первичное развертывание проекта MONO, локальный запуск и деплой на production-сервер. Проект построен на Laravel 10, PHP 8.1+, MySQL/MariaDB, Vite, Tailwind CSS, Laravel Echo и Pusher/Soketi для realtime-функций.

## Требования

- PHP `8.1` или выше
- Composer
- Node.js `18` или выше и npm
- MySQL или MariaDB
- Веб-сервер: Nginx, Apache, Open Server Panel или аналог
- Доступ к терминалу на сервере

Проверь версии:

```bash
php -v
composer -V
node -v
npm -v
mysql --version
```

## Важные директории

- `public` - публичная директория сайта, именно на нее должен смотреть web root.
- `storage/app/public` - загруженные пользователями файлы.
- `public/storage` - символическая ссылка на `storage/app/public`.
- `resources/js` и `resources/css` - исходники фронтенда.
- `database/migrations` - миграции базы данных.

## Локальное развертывание

1. Склонируй проект и перейди в директорию:

```bash
git clone <repo-url> mono-net
cd mono-net
```

Если проект уже находится в `D:\OSPanel\domains\mono-net`, переходи сразу к установке зависимостей.

2. Установи PHP-зависимости:

```bash
composer install
```

3. Установи frontend-зависимости:

```bash
npm install
```

4. Создай файл окружения:

```bash
cp .env.example .env
```

На Windows PowerShell:

```powershell
Copy-Item .env.example .env
```

5. Сгенерируй ключ приложения:

```bash
php artisan key:generate
```

6. Создай базу данных, например `mono-net_db`, и настрой подключение в `.env`:

```env
APP_NAME=MONO
APP_ENV=local
APP_DEBUG=true
APP_URL=http://mono-net

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mono-net_db
DB_USERNAME=root
DB_PASSWORD=
```

Для OSPanel домен обычно совпадает с названием папки в `domains`, например `http://mono-net`.

7. Выполни миграции:

```bash
php artisan migrate
```

8. Создай ссылку на публичное хранилище:

```bash
php artisan storage:link
```

9. Запусти frontend в режиме разработки:

```bash
npm run dev
```

10. Открой сайт:

- при OSPanel: `http://mono-net`
- при artisan-сервере: `http://127.0.0.1:8000`

Если используешь artisan-сервер, запусти его отдельной командой:

```bash
php artisan serve
```

## Настройка realtime-чата

Проект поддерживает два режима.

### Без WebSocket

Если realtime не нужен, оставь в `.env`:

```env
BROADCAST_DRIVER=log
```

Сайт будет работать без websocket-сервера.

### Pusher Cloud

Для Pusher Cloud укажи:

```env
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=<app-id>
PUSHER_APP_KEY=<app-key>
PUSHER_APP_SECRET=<app-secret>
PUSHER_APP_CLUSTER=mt1
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https

VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_HOST="${PUSHER_HOST}"
VITE_PUSHER_PORT="${PUSHER_PORT}"
VITE_PUSHER_SCHEME="${PUSHER_SCHEME}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"
```

После изменения `VITE_*` переменных пересобери фронтенд:

```bash
npm run build
```

### Локальный Soketi

Можно использовать `.env.websocket.example` как основу:

```bash
cp .env.websocket.example .env
```

На Windows PowerShell:

```powershell
Copy-Item .env.websocket.example .env
```

Пример запуска Soketi в Docker:

```bash
docker run --rm -p 6001:6001 -p 9601:9601 -e SOKETI_DEFAULT_APP_ID=mono-net -e SOKETI_DEFAULT_APP_KEY=mono-net-key -e SOKETI_DEFAULT_APP_SECRET=mono-net-secret quay.io/soketi/soketi:latest-16-alpine
```

Ключевые переменные для Soketi:

```env
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=mono-net
PUSHER_APP_KEY=mono-net-key
PUSHER_APP_SECRET=mono-net-secret
PUSHER_HOST=127.0.0.1
PUSHER_PORT=6001
PUSHER_SCHEME=http
PUSHER_APP_CLUSTER=mt1
```

После изменения `.env` очисти конфиг и пересобери frontend:

```bash
php artisan config:clear
npm run build
```

## Production-деплой

### 1. Подготовка сервера

На сервере должны быть установлены:

- PHP `8.1+` с расширениями для Laravel и MySQL
- Composer
- Node.js `18+` и npm
- MySQL/MariaDB
- Nginx или Apache

Создай базу данных и пользователя:

```sql
CREATE DATABASE mono_net CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'mono_net_user'@'localhost' IDENTIFIED BY 'strong_password';
GRANT ALL PRIVILEGES ON mono_net.* TO 'mono_net_user'@'localhost';
FLUSH PRIVILEGES;
```

### 2. Загрузка кода

Вариант через Git:

```bash
cd /var/www
git clone <repo-url> mono-net
cd mono-net
```

Для обновления уже развернутого проекта:

```bash
cd /var/www/mono-net
git pull
```

### 3. Установка зависимостей

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
```

Если `package-lock.json` не соответствует `package.json`, используй:

```bash
npm install
npm run build
```

### 4. Настройка `.env`

Создай production `.env`:

```bash
cp .env.example .env
```

Минимальный production-набор:

```env
APP_NAME=MONO
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://example.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mono_net
DB_USERNAME=mono_net_user
DB_PASSWORD=strong_password

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=public
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
```

Сгенерируй ключ, если `.env` создан впервые:

```bash
php artisan key:generate
```

Не коммить `.env` в репозиторий.

### 5. Права на директории

Laravel должен иметь право записи в `storage` и `bootstrap/cache`.

```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

Пользователь веб-сервера может отличаться. Для Apache/Nginx на Ubuntu это обычно `www-data`.

### 6. Миграции, storage link и кеши

```bash
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

При обновлении проекта обычно выполняй:

```bash
php artisan down
git pull
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan up
```

Если после деплоя менялись env-переменные:

```bash
php artisan config:clear
php artisan config:cache
```

### 7. Настройка веб-сервера

Document root должен указывать на директорию `public`, а не на корень проекта.

Пример Nginx:

```nginx
server {
    listen 80;
    server_name example.com www.example.com;
    root /var/www/mono-net/public;

    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

После изменения конфига:

```bash
sudo nginx -t
sudo systemctl reload nginx
```

Для Apache включи `mod_rewrite`, укажи `DocumentRoot` на `public` и разреши чтение `.htaccess`.

## Очереди

Сейчас в примерах используется:

```env
QUEUE_CONNECTION=sync
```

Это означает выполнение задач сразу в рамках HTTP-запроса. Если перейдешь на очереди `database` или `redis`, нужно запустить worker:

```bash
php artisan queue:work
```

На production worker лучше держать через Supervisor или systemd.

## Проверка после деплоя

Выполни:

```bash
php artisan about
php artisan route:list
php artisan migrate:status
```

Проверь в браузере:

- открывается главная страница;
- регистрация и вход работают;
- создаются посты;
- загружаются изображения и файлы;
- открываются профили;
- работает чат;
- realtime работает, если включен Pusher/Soketi.

## Частые проблемы

### Белый экран или ошибка 500

Проверь лог:

```bash
tail -f storage/logs/laravel.log
```

Также проверь права на `storage` и `bootstrap/cache`.

### Не открываются загруженные изображения

Проверь:

```env
FILESYSTEM_DISK=public
```

Затем выполни:

```bash
php artisan storage:link
```

### После изменения `.env` ничего не поменялось

Laravel может использовать закешированный конфиг:

```bash
php artisan config:clear
php artisan config:cache
```

### Не применяются стили или JavaScript

Для разработки:

```bash
npm run dev
```

Для production:

```bash
npm run build
```

### WebSocket не подключается

Проверь:

- `BROADCAST_DRIVER=pusher`
- значения `PUSHER_APP_ID`, `PUSHER_APP_KEY`, `PUSHER_APP_SECRET`
- `PUSHER_HOST`, `PUSHER_PORT`, `PUSHER_SCHEME`
- что после изменения `VITE_PUSHER_*` выполнен `npm run build`
- что Soketi или Pusher Cloud доступны с сервера и из браузера

## Откат деплоя

Если после обновления возникла ошибка:

```bash
php artisan down
git log --oneline -5
git checkout <previous-commit>
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan up
```

Если миграция уже изменила структуру БД, откат кода может быть недостаточен. Перед production-деплоем делай backup базы данных и `storage/app/public`.

## Минимальный чеклист деплоя

1. Код загружен на сервер.
2. `.env` настроен под production.
3. `APP_DEBUG=false`.
4. `APP_URL` указывает на реальный домен.
5. Composer-зависимости установлены с `--no-dev`.
6. Frontend собран через `npm run build`.
7. Выполнены `php artisan migrate --force` и `php artisan storage:link`.
8. Выполнены `config:cache`, `route:cache`, `view:cache`.
9. Web root указывает на `public`.
10. Проверены регистрация, вход, лента, загрузка файлов и чат.
