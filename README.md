# MONO

MONO - это социальная веб-платформа на Laravel: лента публикаций, профили, подписки, поиск пользователей и личные сообщения в реальном времени.

## Что реализовано

- Аутентификация и верификация email (Laravel Breeze).
- Лента публикаций: создание постов, изображения в постах, удаление своих постов.
- Лайки и комментарии.
- Профиль пользователя: просмотр, редактирование, подписка/отписка.
- Поиск друзей по имени/email/био.
- Личные сообщения:
  - текстовые сообщения;
  - отправка файлов;
  - отправка изображений с миниатюрой;
  - отправка голосовых сообщений;
  - realtime-получение новых сообщений (Laravel Echo + Pusher).

## Технологии

- Backend: `Laravel 10`, `PHP 8.1+`, `MySQL`.
- Frontend: `Blade`, `Alpine.js`, `Tailwind CSS`, `Vite`.
- Realtime: `laravel-echo`, `pusher-js`, `pusher/pusher-php-server`.
- Хранение файлов: `storage/app/public` + `public/storage` symlink.

## Структура основных модулей

- `routes/web.php` - маршруты приложения.
- `app/Http/Controllers` - контроллеры ленты, профиля, поиска, чата.
- `app/Models` - модели (`User`, `Post`, `Message`, ...).
- `resources/views` - Blade-шаблоны интерфейса.
- `resources/js` - клиентская логика (`app.js`, `echo-chat.js`).
- `database/migrations` - миграции БД.

## Требования

- PHP `>= 8.1`
- Composer
- Node.js `>= 18` и npm
- MySQL/MariaDB

## Установка

```bash
git clone <repo-url>
cd mono-net
composer install
npm install
cp .env.example .env
php artisan key:generate
```

## Настройка `.env`

Минимально проверь:

- `APP_NAME=MONO`
- `APP_URL=http://localhost` (или твой домен)
- `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`

Для realtime чата:

- Если realtime не нужен, оставь:
  - `BROADCAST_DRIVER=log`
- Если нужен realtime через Pusher Cloud:
  - `BROADCAST_DRIVER=pusher`
  - заполни `PUSHER_APP_ID`, `PUSHER_APP_KEY`, `PUSHER_APP_SECRET`, `PUSHER_APP_CLUSTER`
  - проверь `VITE_PUSHER_*` переменные (берутся из `PUSHER_*`)
- Если нужен локальный websocket без Pusher Cloud:
  - возьми шаблон из `.env.websocket.example`
  - это конфиг под `Soketi`, который понимает Pusher protocol
  - ключевые значения обычно такие:
    - `BROADCAST_DRIVER=pusher`
    - `PUSHER_HOST=127.0.0.1`
    - `PUSHER_PORT=6001`
    - `PUSHER_SCHEME=http`
    - `PUSHER_APP_ID=mono-net`
    - `PUSHER_APP_KEY=mono-net-key`
    - `PUSHER_APP_SECRET=mono-net-secret`
  - затем пересобери фронтенд, чтобы Vite подхватил `VITE_PUSHER_*`:
    - `npm run build` или `npm run dev`

### Локальный websocket через Soketi

Пример запуска Soketi в Docker:

```bash
docker run --rm -p 6001:6001 -p 9601:9601 -e SOKETI_DEFAULT_APP_ID=mono-net -e SOKETI_DEFAULT_APP_KEY=mono-net-key -e SOKETI_DEFAULT_APP_SECRET=mono-net-secret quay.io/soketi/soketi:latest-16-alpine
```

После этого:

1. Скопируй `.env.websocket.example` в `.env` или перенеси из него только блок `PUSHER_*`.
2. Выполни `php artisan config:clear`.
3. Пересобери фронтенд: `npm run build`.
4. Открой сайт заново.

Для загрузки файлов в чат и посты:

- `FILESYSTEM_DISK=public`
- затем обязательно выполни `php artisan storage:link`

## Миграции и запуск

```bash
php artisan migrate
php artisan storage:link
```

### Режим разработки

Открой два терминала:

```bash
php artisan serve
```

```bash
npm run dev
```

Приложение будет доступно по адресу из `APP_URL` или `http://127.0.0.1:8000`.

### Продакшн-сборка фронта

```bash
npm run build
```

## Полезные команды

```bash
php artisan route:list
php artisan config:clear
php artisan cache:clear
php artisan test
```

## Типичный сценарий первого запуска

1. Установить зависимости (`composer install`, `npm install`).
2. Настроить `.env`.
3. Выполнить миграции (`php artisan migrate`).
4. Создать symlink для файлов (`php artisan storage:link`).
5. Запустить backend (`php artisan serve`).
6. Запустить frontend (`npm run dev`).

## Частые проблемы

- Не отображаются загруженные изображения/файлы:
  - проверь `FILESYSTEM_DISK=public`;
  - убедись, что выполнен `php artisan storage:link`.
- Сообщения не приходят realtime:
  - проверь `BROADCAST_DRIVER`;
  - для Pusher/Soketi проверь корректность всех `PUSHER_*` ключей;
  - если websocket не поднят, сайт будет работать через polling, но не через сокет realtime.
- Не применяется frontend:
  - убедись, что запущен `npm run dev` или выполнен `npm run build`.
