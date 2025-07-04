<?php
// Улучшенный скрипт для настройки webhook Telegram бота

$config = require __DIR__ . '/config/telegram.php';
$appConfig = require __DIR__ . '/config/config.php';

$bot_token = $config['bot_token'];
$webhook_url = $config['webhook_url'];

if ($bot_token === 'YOUR_BOT_TOKEN_HERE' || $bot_token === '7663510884:AAE1BAZZpW8EqJRasTD8eG07usMx_ZZzMdQ') {
    echo "❌ Помилка: Потрібно налаштувати токен бота!\n";
    echo "Відредагуйте файл config/telegram.php та додайте токен від @BotFather\n";
    exit(1);
}

echo "🔄 Налаштування webhook для Telegram бота...\n";
echo "Bot Token: " . substr($bot_token, 0, 10) . "...\n";
echo "Webhook URL: $webhook_url\n\n";

// Проверка доступности файла webhook
if (!file_exists(__DIR__ . '/telegram-bot/webhook.php')) {
    echo "❌ Помилка: Файл webhook.php не знайдено!\n";
    echo "Створіть файл telegram-bot/webhook.php\n";
    exit(1);
}

// Функция для выполнения запросов к Telegram API
function telegramRequest($method, $data, $token) {
    $url = "https://api.telegram.org/bot$token/$method";
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $data,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_USERAGENT => 'IT-Support-Bot/1.0'
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_error($ch)) {
        $error = curl_error($ch);
        curl_close($ch);
        throw new Exception("cURL error: $error");
    }
    
    curl_close($ch);
    
    $decoded = json_decode($response, true);
    
    if ($http_code !== 200) {
        throw new Exception("HTTP error: $http_code. Response: " . ($response ?: 'empty'));
    }
    
    if (!$decoded) {
        throw new Exception("Invalid JSON response: " . ($response ?: 'empty'));
    }
    
    return $decoded;
}

try {
    // 1. Проверка токена - получение информации о боте
    echo "🔄 Перевірка токена бота...\n";
    $botInfo = telegramRequest('getMe', [], $bot_token);
    
    if (!$botInfo['ok']) {
        throw new Exception("Невірний токен бота: " . ($botInfo['description'] ?? 'Unknown error'));
    }
    
    echo "✅ Бот знайдено: @{$botInfo['result']['username']} ({$botInfo['result']['first_name']})\n\n";
    
    // 2. Удаление старого webhook
    echo "🔄 Видалення старого webhook...\n";
    $deleteResult = telegramRequest('deleteWebhook', ['drop_pending_updates' => true], $bot_token);
    
    if ($deleteResult['ok']) {
        echo "✅ Старий webhook видалено\n";
    } else {
        echo "⚠️ Помилка при видаленні webhook: {$deleteResult['description']}\n";
    }
    
    // Пауза для обработки
    sleep(1);
    
    // 3. Проверка доступности webhook URL
    echo "🔄 Перевірка доступності webhook URL...\n";
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $webhook_url,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => '{"test": true}',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_FOLLOWLOCATION => true
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);
    
    if ($curl_error) {
        echo "⚠️ Помилка доступу до webhook: $curl_error\n";
    } elseif ($http_code === 200) {
        echo "✅ Webhook URL доступний (HTTP $http_code)\n";
    } else {
        echo "⚠️ Webhook URL повертає код: HTTP $http_code\n";
        if ($response) {
            echo "Відповідь: " . substr($response, 0, 200) . "\n";
        }
    }
    
    // 4. Установка нового webhook
    echo "🔄 Встановлення нового webhook...\n";
    $webhookData = [
        'url' => $webhook_url,
        'max_connections' => 100,
        'allowed_updates' => json_encode(['message', 'callback_query', 'inline_query']),
        'drop_pending_updates' => true
    ];
    
    $setResult = telegramRequest('setWebhook', $webhookData, $bot_token);
    
    if (!$setResult['ok']) {
        throw new Exception("Помилка встановлення webhook: " . $setResult['description']);
    }
    
    echo "✅ Webhook встановлено успішно!\n";
    
    // Пауза для обработки
    sleep(1);
    
    // 5. Проверка webhook
    echo "🔄 Перевірка webhook...\n";
    $webhookInfo = telegramRequest('getWebhookInfo', [], $bot_token);
    
    if ($webhookInfo['ok']) {
        $info = $webhookInfo['result'];
        echo "✅ Інформація про webhook:\n";
        echo "   URL: {$info['url']}\n";
        echo "   Має сертифікат: " . ($info['has_custom_certificate'] ? 'Так' : 'Ні') . "\n";
        echo "   Кількість очікуючих оновлень: {$info['pending_update_count']}\n";
        
        if (isset($info['last_error_date'])) {
            echo "   ⚠️ Остання помилка: " . date('Y-m-d H:i:s', $info['last_error_date']) . "\n";
            echo "   Опис помилки: {$info['last_error_message']}\n";
        }
        
        if (isset($info['max_connections'])) {
            echo "   Максимум з'єднань: {$info['max_connections']}\n";
        }
        
        if (isset($info['allowed_updates'])) {
            echo "   Дозволені оновлення: " . implode(', ', $info['allowed_updates']) . "\n";
        }
    }
    
    // 6. Настройка команд бота
    echo "\n🔄 Налаштування команд бота...\n";
    $commands = [
        ['command' => 'start', 'description' => 'Головне меню'],
        ['command' => 'help', 'description' => 'Довідка по боту'],
        ['command' => 'cancel', 'description' => 'Скасувати поточну дію'],
        ['command' => 'admin', 'description' => 'Адмін-панель (тільки для адміністраторів)']
    ];
    
    $commandsResult = telegramRequest('setMyCommands', [
        'commands' => json_encode($commands),
        'scope' => json_encode(['type' => 'default'])
    ], $bot_token);
    
    if ($commandsResult['ok']) {
        echo "✅ Команди бота налаштовано\n";
    } else {
        echo "⚠️ Помилка налаштування команд: {$commandsResult['description']}\n";
    }
    
    // 7. Тестовый запрос к webhook
    echo "\n🔄 Тестовий запит до webhook...\n";
    $testData = json_encode([
        'update_id' => 999999999,
        'message' => [
            'message_id' => 1,
            'from' => [
                'id' => 999999999,
                'is_bot' => false,
                'first_name' => 'Test',
                'username' => 'test_user'
            ],
            'chat' => [
                'id' => 999999999,
                'first_name' => 'Test',
                'username' => 'test_user',
                'type' => 'private'
            ],
            'date' => time(),
            'text' => '/start'
        ]
    ]);
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $webhook_url,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $testData,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_SSL_VERIFYPEER => true
    ]);
    
    $testResponse = curl_exec($ch);
    $testHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $testError = curl_error($ch);
    curl_close($ch);
    
    if ($testError) {
        echo "⚠️ Помилка тестового запиту: $testError\n";
    } elseif ($testHttpCode === 200) {
        echo "✅ Тестовий запит успішний (HTTP $testHttpCode)\n";
        if ($testResponse === 'OK') {
            echo "✅ Webhook відповідає коректно\n";
        } else {
            echo "⚠️ Неочікувана відповідь: $testResponse\n";
        }
    } else {
        echo "⚠️ Тестовий запит повернув код: HTTP $testHttpCode\n";
        echo "Відповідь: " . substr($testResponse, 0, 200) . "\n";
    }
    
    echo "\n🎉 Налаштування завершено!\n\n";
    echo "📝 Наступні кроки:\n";
    echo "1. Додайте свій Telegram ID як адміністратора:\n";
    echo "   php add_admin.php YOUR_TELEGRAM_ID \"Ваше ім'я\"\n";
    echo "2. Напишіть боту /start для тестування\n";
    echo "3. Перевірте логи в папці logs/ при проблемах\n";
    echo "4. Якщо щось не працює, використайте debug.php\n\n";
    echo "🔗 Посилання на бота: https://t.me/{$botInfo['result']['username']}\n";
    
    // Создание файла со статусом
    file_put_contents(__DIR__ . '/logs/webhook_status.txt', 
        "Webhook налаштовано: " . date('Y-m-d H:i:s') . "\n" .
        "Bot: @{$botInfo['result']['username']}\n" .
        "URL: $webhook_url\n" .
        "Статус: Активний\n"
    );
    
} catch (Exception $e) {
    echo "❌ Помилка: " . $e->getMessage() . "\n";
    
    // Логирование ошибки
    file_put_contents(__DIR__ . '/logs/webhook_setup_error.txt', 
        date('Y-m-d H:i:s') . " - " . $e->getMessage() . "\n", 
        FILE_APPEND
    );
    
    exit(1);
}
?>