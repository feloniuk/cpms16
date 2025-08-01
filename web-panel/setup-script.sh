#!/bin/bash

# IT Support Panel - Final Setup Script
# Этот скрипт выполняет полную настройку Laravel веб-панели

echo "👤 Учетные данные по умолчанию:"
echo "   Администратор:"
echo "   Email: admin@cpms16.online"
echo "   Пароль: password"
echo ""
echo "   Директор:"
echo "   Email: director@cpms16.online"
echo "   Пароль: password"
echo ""
echo "⚠️  ВАЖНО: Обязательно смените пароли после первого входа!"
echo ""
echo "🔗 Интеграция с Telegram ботом:"
echo "   Telegram бот: https://cpms16.online/telegram-bot/webhook.php"
echo "   Веб-панель: https://cpms16.online/web-panel"
echo "   Общая БД: u442651334_main"
echo ""
echo "📊 Функционал веб-панели:"
echo "   ✅ Дашборд с аналитикой"
echo "   ✅ Управление заявками на ремонт"
echo "   ✅ История замен картриджей"
echo "   ✅ Управление филиалами"
echo "   ✅ Инвентаризация оборудования"
echo "   ✅ Отчеты и экспорт данных"
echo "   ✅ Роли: Администратор и Директор"
echo ""
echo "🛠️ Следующие шаги:"
echo "1. Откройте https://cpms16.online/web-panel"
echo "2. Войдите используя учетные данные выше"
echo "3. Смените пароль в разделе 'Профиль'"
echo "4. Настройте дополнительных пользователей при необходимости"
echo "5. Проверьте работу интеграции с Telegram ботом"
echo ""
echo "📖 Документация:"
echo "   Логи Laravel: web-panel/storage/logs/laravel.log"
echo "   Логи Telegram: logs/telegram.log"
echo "   Резервные копии: создавайте регулярно!"
echo ""
echo "🆘 Поддержка:"
echo "   При проблемах проверьте логи и права доступа"
echo "   Убедитесь, что SSL сертификат настроен"
echo "   Проверьте работу Telegram бота отдельно"
echo ""
echo "✨ Готово! Система готова к использованию." "🚀 Начинаем настройку IT Support Panel..."
echo "======================================"

# Переход в директорию проекта
cd /domains/cpms16.online/public_html

# Проверяем, что мы в правильной директории
if [ ! -f "telegram-bot/webhook.php" ]; then
    echo "❌ Ошибка: Не найден файл telegram-bot/webhook.php"
    echo "Убедитесь, что вы находитесь в корректной директории"
    exit 1
fi

echo "✅ Telegram бот найден, продолжаем..."

# Создание Laravel проекта
echo "📦 Создание Laravel проекта..."
if [ ! -d "web-panel" ]; then
    composer create-project laravel/laravel web-panel --no-dev
    if [ $? -ne 0 ]; then
        echo "❌ Ошибка при создании Laravel проекта"
        exit 1
    fi
else
    echo "ℹ️ Директория web-panel уже существует"
fi

cd web-panel

# Установка зависимостей
echo "📦 Установка дополнительных пакетов..."
composer require laravel/breeze --dev
composer require spatie/laravel-permission

# Настройка Breeze
echo "🔧 Настройка аутентификации..."
php artisan breeze:install blade --pest
npm install && npm run build

# Настройка .env файла
echo "⚙️ Настройка окружения..."
cat > .env << 'EOF'
APP_NAME="IT Support Panel"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://cpms16.online/web-panel

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=u442651334_main
DB_USERNAME=u442651334_main
DB_PASSWORD=Cthutq19971816!

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MEMCACHED_HOST=127.0.0.1

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@cpms16.online"
MAIL_FROM_NAME="${APP_NAME}"
EOF

# Генерация ключа приложения
echo "🔑 Генерация ключа приложения..."
php artisan key:generate

# Создание директорий для кастомных файлов
echo "📁 Создание структуры каталогов..."
mkdir -p app/Http/Middleware
mkdir -p app/Http/Controllers
mkdir -p resources/views/dashboard
mkdir -p resources/views/repairs
mkdir -p resources/views/cartridges
mkdir -p resources/views/branches
mkdir -p resources/views/inventory
mkdir -p resources/views/reports
mkdir -p database/seeders

# Настройка прав доступа
echo "🔒 Настройка прав доступа..."
chmod -R 755 .
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data . 2>/dev/null || echo "⚠️ Не удалось изменить владельца файлов (нужны права root)"

# Создание .htaccess для веб-панели
echo "🌐 Настройка веб-сервера..."
cat > public/.htaccess << 'EOF'
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>

# Security
<Files ".env">
    Require all denied
</Files>

<Files "composer.json">
    Require all denied
</Files>

<Files "composer.lock">
    Require all denied
</Files>
EOF

# Обновление основного .htaccess
echo "🔧 Обновление основного .htaccess..."
cd ..
if ! grep -q "web-panel" .htaccess; then
    echo "" >> .htaccess
    echo "# Laravel Web Panel" >> .htaccess
    echo "RewriteRule ^web-panel/(.*)$ web-panel/public/\$1 [L]" >> .htaccess
    echo "✅ Добавлено перенаправление для web-panel"
fi

cd web-panel

# Выполнение миграций
echo "🗄️ Настройка базы данных..."
php artisan migrate --force

# Публикация миграций для ролей
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider" --force

# Создание начальных пользователей
echo "👤 Создание начальных пользователей..."
php artisan db:seed --force

# Очистка и кэширование конфигурации
echo "⚡ Оптимизация производительности..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Создание символической ссылки для storage
php artisan storage:link

# Проверка статуса
echo ""
echo "🔍 Проверка статуса установки..."

# Проверка подключения к БД
echo -n "База данных: "
if php artisan tinker --execute="DB::connection()->getPdo(); echo 'OK';" 2>/dev/null | grep -q "OK"; then
    echo "✅ Подключена"
else
    echo "❌ Ошибка подключения"
fi

# Проверка конфигурации
echo -n "Конфигурация: "
if [ -f "bootstrap/cache/config.php" ]; then
    echo "✅ Кэширована"
else
    echo "❌ Не кэширована"
fi

# Проверка прав доступа
echo -n "Права доступа: "
if [ -w "storage" ] && [ -w "bootstrap/cache" ]; then
    echo "✅ Корректные"
else
    echo "❌ Требуют исправления"
fi

echo ""
echo "🎉 Установка завершена!"
echo "========================"
echo ""
echo "📋 Информация для входа:"
echo "🌐 URL веб-панели: https://cpms16.online/web-panel"
echo ""
echo