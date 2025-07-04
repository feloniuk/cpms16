<?php
// Скрипт для отладки IT Support Bot

echo "🔍 Отладка IT Support Bot\n";
echo "=" . str_repeat("=", 50) . "\n\n";

// 1. Проверка файлов
echo "📁 Проверка файлов:\n";
$requiredFiles = [
    'telegram-bot/TelegramBot.php',
    'telegram-bot/keyboards/Keyboards.php', 
    'core/Database.php',
    'core/repositories/AdminRepository.php',
    'core/repositories/BranchRepository.php',
    'core/repositories/UserStateRepository.php'
];

foreach ($requiredFiles as $file) {
    $path = __DIR__ . '/' . $file;
    echo (file_exists($path) ? "✅" : "❌") . " $file\n";
    if (!file_exists($path)) {
        echo "   Файл отсутствует: $path\n";
    }
}

// 2. Проверка подключения к БД
echo "\n📊 Проверка базы данных:\n";
try {
    require_once __DIR__ . '/core/Database.php';
    $db = Database::getInstance();
    echo "✅ Подключение к БД успешно\n";
    
    // Проверка таблиц
    $tables = ['branches', 'admins', 'user_states', 'repair_requests'];
    foreach ($tables as $table) {
        try {
            $stmt = $db->query("SELECT COUNT(*) as count FROM $table");
            $count = $stmt->fetch()['count'];
            echo "✅ Таблица $table: $count записей\n";
        } catch (Exception $e) {
            echo "❌ Таблица $table: " . $e->getMessage() . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Ошибка БД: " . $e->getMessage() . "\n";
}

// 3. Проверка конфигурации Telegram
echo "\n🤖 Проверка конфигурации Telegram:\n";
$telegramConfig = require __DIR__ . '/config/telegram.php';
echo ($telegramConfig['bot_token'] !== 'YOUR_BOT_TOKEN_HERE' ? "✅" : "❌") . " Bot token настроен\n";
echo "📍 Webhook URL: " . $telegramConfig['webhook_url'] . "\n";

// 4. Проверка репозиториев
echo "\n🔧 Проверка репозиториев:\n";
try {
    require_once __DIR__ . '/core/repositories/BranchRepository.php';
    require_once __DIR__ . '/core/repositories/AdminRepository.php';
    require_once __DIR__ . '/core/repositories/UserStateRepository.php';
    
    $branchRepo = new BranchRepository();
    $adminRepo = new AdminRepository();
    $userStateRepo = new UserStateRepository();
    
    echo "✅ BranchRepository создан\n";
    echo "✅ AdminRepository создан\n"; 
    echo "✅ UserStateRepository создан\n";
    
    // Проверка филиалов
    $branches = $branchRepo->getActive();
    echo "📊 Активных филиалов: " . count($branches) . "\n";
    
    // Проверка админов
    $admins = $adminRepo->getActiveAdmins();
    echo "👥 Активных админов: " . count($admins) . "\n";
    
    foreach ($admins as $admin) {
        echo "   - " . $admin['name'] . " (ID: " . $admin['telegram_id'] . ")\n";
    }
    
} catch (Exception $e) {
    echo "❌ Ошибка репозиториев: " . $e->getMessage() . "\n";
}

// 5. Проверка классов бота
echo "\n🤖 Проверка классов бота:\n";
try {
    require_once __DIR__ . '/telegram-bot/keyboards/Keyboards.php';
    $keyboards = new Keyboards();
    echo "✅ Keyboards класс создан\n";
    
    // Проверка главного меню
    $mainMenu = $keyboards->getMainMenu();
    echo "✅ Главное меню: " . count($mainMenu['inline_keyboard']) . " кнопок\n";
    
    require_once __DIR__ . '/telegram-bot/TelegramBot.php';
    echo "✅ TelegramBot класс подключен\n";
    
} catch (Exception $e) {
    echo "❌ Ошибка классов бота: " . $e->getMessage() . "\n";
    echo "Детали: " . $e->getTraceAsString() . "\n";
}

// 6. Проверка логов
echo "\n📋 Проверка логов:\n";
$logFiles = ['webhook.log', 'telegram.log', 'errors.log'];
foreach ($logFiles as $logFile) {
    $path = __DIR__ . '/logs/' . $logFile;
    if (file_exists($path)) {
        $size = filesize($path);
        echo "📄 $logFile: " . round($size/1024, 2) . " KB\n";
        
        if ($size > 0) {
            echo "   Последние строки:\n";
            $lines = file($path);
            $lastLines = array_slice($lines, -3);
            foreach ($lastLines as $line) {
                echo "   " . trim($line) . "\n";
            }
        }
    } else {
        echo "❌ $logFile: файл не существует\n";
    }
}

echo "\n🔍 Проверка последних webhook вызовов:\n";
$webhookLog = __DIR__ . '/logs/webhook.log';
if (file_exists($webhookLog)) {
    $lines = file($webhookLog);
    $lastLines = array_slice($lines, -10);
    foreach ($lastLines as $line) {
        if (strpos($line, 'callback_query') !== false || strpos($line, 'repair_request') !== false) {
            echo "🔍 " . trim($line) . "\n";
        }
    }
} else {
    echo "❌ Webhook лог не найден\n";
}

// 7. Тест создания простого обновления
echo "\n🧪 Тест обработки простого callback:\n";
try {
    $testUpdate = [
        'callback_query' => [
            'id' => 'test_123',
            'from' => [
                'id' => 123456789,
                'username' => 'test_user'
            ],
            'message' => [
                'chat' => ['id' => 123456789],
                'message_id' => 1
            ],
            'data' => 'repair_request'
        ]
    ];
    
    echo "📝 Создан тестовый callback для 'repair_request'\n";
    echo "📊 Данные: " . json_encode($testUpdate, JSON_UNESCAPED_UNICODE) . "\n";
    
} catch (Exception $e) {
    echo "❌ Ошибка теста: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "🎯 Рекомендации:\n";
echo "1. Проверьте логи errors.log и telegram.log\n";
echo "2. Если есть ошибки PHP - исправьте синтаксис\n"; 
echo "3. Убедитесь что все админы добавлены в БД\n";
echo "4. Попробуйте команды /start, /help в боте\n";
echo "5. Включите отладку в config.php (debug = true)\n";
echo "\nДля детальной отладки добавьте в webhook.php:\n";
echo "file_put_contents('logs/debug.log', print_r(\$update, true), FILE_APPEND);\n";
?>