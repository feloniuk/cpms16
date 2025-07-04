<?php
// Комплексный скрипт отладки IT Support Bot

echo "🔍 Комплексна діагностика IT Support Bot\n";
echo "=" . str_repeat("=", 60) . "\n\n";

// Создание папки для логов если не существует
if (!file_exists(__DIR__ . '/logs')) {
    mkdir(__DIR__ . '/logs', 0777, true);
    echo "📁 Створено папку logs/\n";
}

// 1. Проверка PHP и расширений
echo "🔧 Перевірка PHP:\n";
echo "PHP версія: " . phpversion() . "\n";
echo "Розширення cURL: " . (extension_loaded('curl') ? "✅ Встановлено" : "❌ Відсутнє") . "\n";
echo "Розширення JSON: " . (extension_loaded('json') ? "✅ Встановлено" : "❌ Відсутнє") . "\n";
echo "Розширення PDO: " . (extension_loaded('pdo') ? "✅ Встановлено" : "❌ Відсутнє") . "\n";
echo "Розширення PDO MySQL: " . (extension_loaded('pdo_mysql') ? "✅ Встановлено" : "❌ Відсутнє") . "\n\n";

// 2. Проверка файлов
echo "📁 Перевірка файлів:\n";
$requiredFiles = [
    'config/database.php' => 'Конфігурація БД',
    'config/telegram.php' => 'Конфігурація Telegram',
    'config/config.php' => 'Основна конфігурація',
    'core/Database.php' => 'Клас Database',
    'core/repositories/BaseRepository.php' => 'Базовий репозиторій',
    'core/repositories/AdminRepository.php' => 'Репозиторій адмінів',
    'core/repositories/BranchRepository.php' => 'Репозиторій філій',
    'core/repositories/UserStateRepository.php' => 'Репозиторій станів',
    'telegram-bot/TelegramBot.php' => 'Основний клас бота',
    'telegram-bot/keyboards/Keyboards.php' => 'Клавіатури',
    'telegram-bot/webhook.php' => 'Webhook обробник',
    'sql/database.sql' => 'SQL структура'
];

foreach ($requiredFiles as $file => $description) {
    $path = __DIR__ . '/' . $file;
    $exists = file_exists($path);
    $size = $exists ? filesize($path) : 0;
    echo ($exists ? "✅" : "❌") . " $description ($file)";
    if ($exists) {
        echo " - " . round($size/1024, 2) . " KB";
    }
    echo "\n";
}

// 3. Проверка конфигурации
echo "\n⚙️ Перевірка конфігурації:\n";
try {
    $telegramConfig = require __DIR__ . '/config/telegram.php';
    $dbConfig = require __DIR__ . '/config/database.php';
    $appConfig = require __DIR__ . '/config/config.php';
    
    echo "Telegram bot token: " . (($telegramConfig['bot_token'] !== 'YOUR_BOT_TOKEN_HERE') ? "✅ Налаштовано" : "❌ Потрібно налаштувати") . "\n";
    echo "Webhook URL: " . $telegramConfig['webhook_url'] . "\n";
    echo "База даних: " . $dbConfig['dbname'] . " на " . $dbConfig['host'] . "\n";
    echo "Домен: " . $appConfig['app']['base_url'] . "\n";
    
} catch (Exception $e) {
    echo "❌ Помилка читання конфігурації: " . $e->getMessage() . "\n";
}

// 4. Проверка подключения к БД
echo "\n📊 Перевірка бази даних:\n";
try {
    require_once __DIR__ . '/core/Database.php';
    $db = Database::getInstance();
    echo "✅ Підключення до БД успішне\n";
    
    // Проверка таблиц
    $tables = [
        'branches' => 'Філії',
        'admins' => 'Адміністратори',
        'user_states' => 'Стани користувачів',
        'repair_requests' => 'Заявки на ремонт',
        'cartridge_replacements' => 'Заміни картриджів',
        'room_inventory' => 'Інвентар кабінетів',
        'inventory_templates' => 'Шаблони інвентарю',
        'api_tokens' => 'API токени'
    ];
    
    foreach ($tables as $table => $description) {
        try {
            $stmt = $db->query("SELECT COUNT(*) as count FROM $table");
            $count = $stmt->fetch()['count'];
            echo "✅ $description ($table): $count записів\n";
        } catch (Exception $e) {
            echo "❌ $description ($table): " . $e->getMessage() . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Помилка БД: " . $e->getMessage() . "\n";
}

// 5. Проверка репозиториев
echo "\n🔧 Перевірка репозиторіїв:\n";
try {
    require_once __DIR__ . '/core/repositories/BranchRepository.php';
    require_once __DIR__ . '/core/repositories/AdminRepository.php';
    require_once __DIR__ . '/core/repositories/UserStateRepository.php';
    
    $branchRepo = new BranchRepository();
    $adminRepo = new AdminRepository();
    $userStateRepo = new UserStateRepository();
    
    echo "✅ Репозиторії створено успішно\n";
    
    // Проверка филиалов
    $branches = $branchRepo->getActive();
    echo "📊 Активних філій: " . count($branches) . "\n";
    foreach ($branches as $branch) {
        echo "   - " . $branch['name'] . " (ID: " . $branch['id'] . ")\n";
    }
    
    // Проверка админов
    $admins = $adminRepo->getActiveAdmins();
    echo "👥 Активних адмінів: " . count($admins) . "\n";
    foreach ($admins as $admin) {
        echo "   - " . $admin['name'] . " (ID: " . $admin['telegram_id'] . ")\n";
    }
    
    // Проверка состояний
    $activeStates = $userStateRepo->getActiveStatesCount();
    echo "🔄 Активних станів: $activeStates\n";
    
} catch (Exception $e) {
    echo "❌ Помилка репозиторіїв: " . $e->getMessage() . "\n";
}

// 6. Проверка классов бота
echo "\n🤖 Перевірка класів бота:\n";
try {
    require_once __DIR__ . '/telegram-bot/keyboards/Keyboards.php';
    $keyboards = new Keyboards();
    echo "✅ Клас Keyboards створено\n";
    
    $mainMenu = $keyboards->getMainMenu();
    echo "✅ Головне меню: " . count($mainMenu['inline_keyboard']) . " кнопок\n";
    
    require_once __DIR__ . '/telegram-bot/TelegramBot.php';
    echo "✅ Клас TelegramBot підключено\n";
    
} catch (Exception $e) {
    echo "❌ Помилка класів бота: " . $e->getMessage() . "\n";
    echo "Детальний опис:\n";
    echo $e->getTraceAsString() . "\n";
}

// 7. Проверка Telegram API
echo "\n📡 Перевірка Telegram API:\n";
try {
    if (isset($telegramConfig) && $telegramConfig['bot_token'] !== 'YOUR_BOT_TOKEN_HERE') {
        $bot_token = $telegramConfig['bot_token'];
        $url = "https://api.telegram.org/bot$bot_token/getMe";
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true
        ]);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);
        
        if ($curl_error) {
            echo "❌ Помилка cURL: $curl_error\n";
        } elseif ($http_code === 200) {
            $data = json_decode($response, true);
            if ($data && $data['ok']) {
                echo "✅ Telegram API доступний\n";
                echo "Бот: @{$data['result']['username']} ({$data['result']['first_name']})\n";
            } else {
                echo "❌ Невірна відповідь API: $response\n";
            }
        } else {
            echo "❌ HTTP код: $http_code\n";
        }
    } else {
        echo "⚠️ Токен бота не налаштовано\n";
    }
} catch (Exception $e) {
    echo "❌ Помилка перевірки API: " . $e->getMessage() . "\n";
}

// 8. Проверка webhook
echo "\n🔗 Перевірка webhook:\n";
try {
    if (isset($telegramConfig) && $telegramConfig['bot_token'] !== 'YOUR_BOT_TOKEN_HERE') {
        $bot_token = $telegramConfig['bot_token'];
        $url = "https://api.telegram.org/bot$bot_token/getWebhookInfo";
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true
        ]);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code === 200) {
            $data = json_decode($response, true);
            if ($data && $data['ok']) {
                $info = $data['result'];
                echo "✅ Webhook інформація:\n";
                echo "URL: " . ($info['url'] ?: 'Не встановлено') . "\n";
                echo "Очікуючих оновлень: " . ($info['pending_update_count'] ?? 0) . "\n";
                
                if (isset($info['last_error_date'])) {
                    echo "⚠️ Остання помилка: " . date('Y-m-d H:i:s', $info['last_error_date']) . "\n";
                    echo "Опис помилки: " . $info['last_error_message'] . "\n";
                }
            }
        }
    }
} catch (Exception $e) {
    echo "❌ Помилка перевірки webhook: " . $e->getMessage() . "\n";
}

// 9. Проверка логов
echo "\n📋 Перевірка логів:\n";
$logFiles = [
    'webhook.log' => 'Webhook логи',
    'telegram.log' => 'Telegram логи',
    'errors.log' => 'Логи помилок'
];

foreach ($logFiles as $logFile => $description) {
    $path = __DIR__ . '/logs/' . $logFile;
    if (file_exists($path)) {
        $size = filesize($path);
        echo "📄 $description: " . round($size/1024, 2) . " KB\n";
        
        if ($size > 0) {
            $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $lastLines = array_slice($lines, -3);
            if (!empty($lastLines)) {
                echo "   Останні записи:\n";
                foreach ($lastLines as $line) {
                    echo "   " . substr($line, 0, 80) . "...\n";
                }
            }
        }
    } else {
        echo "❌ $description: файл не існує\n";
    }
}

// 10. Тест создания состояния пользователя
echo "\n🧪 Тест користувацьких станів:\n";
try {
    $testUserId = 999999999;
    
    // Установка состояния
    $result = $userStateRepo->setState($testUserId, 'test_state', ['test_key' => 'test_value']);
    echo ($result ? "✅" : "❌") . " Встановлення стану\n";
    
    // Получение состояния
    $state = $userStateRepo->getUserState($testUserId);
    if ($state && $state['current_state'] === 'test_state') {
        echo "✅ Отримання стану\n";
    } else {
        echo "❌ Отримання стану\n";
    }
    
    // Добавление к temp_data
    $result = $userStateRepo->addToTempData($testUserId, 'new_key', 'new_value');
    echo ($result ? "✅" : "❌") . " Додавання до temp_data\n";
    
    // Очистка тестовых данных
    $userStateRepo->clearState($testUserId);
    echo "✅ Очищення тестових даних\n";
    
} catch (Exception $e) {
    echo "❌ Помилка тестування станів: " . $e->getMessage() . "\n";
}

// 11. Рекомендации
echo "\n" . str_repeat("=", 60) . "\n";
echo "💡 Рекомендації:\n\n";

if (!file_exists(__DIR__ . '/telegram-bot/webhook.php')) {
    echo "❗ Створіть файл telegram-bot/webhook.php\n";
}

if (!isset($telegramConfig) || $telegramConfig['bot_token'] === 'YOUR_BOT_TOKEN_HERE') {
    echo "❗ Налаштуйте токен бота в config/telegram.php\n";
}

if (!isset($admins) || count($admins) === 0) {
    echo "❗ Додайте адміністраторів через: php add_admin.php TELEGRAM_ID \"Ім'я\"\n";
}

if (!isset($branches) || count($branches) === 0) {
    echo "❗ Створіть філії через: php test_db.php\n";
}

echo "✅ Для налаштування webhook: php setup_webhook.php\n";
echo "✅ Для отримання вашого ID: php get_my_id.php\n";
echo "✅ Для перевірки БД: php test_db.php\n";
echo "✅ Для перевірки станів: php check_states.php\n";

echo "\n📊 Загальний статус: ";
$issues = 0;
if (!file_exists(__DIR__ . '/telegram-bot/webhook.php')) $issues++;
if (!isset($telegramConfig) || $telegramConfig['bot_token'] === 'YOUR_BOT_TOKEN_HERE') $issues++;
if (!isset($admins) || count($admins) === 0) $issues++;

if ($issues === 0) {
    echo "✅ Все готово до роботи!\n";
} else {
    echo "⚠️ Знайдено $issues проблем, які потрібно вирішити\n";
}
?>