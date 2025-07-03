<?php
// Скрипт для настройки webhook Telegram бота

$config = require __DIR__ . '/config/telegram.php';
$appConfig = require __DIR__ . '/config/config.php';

$bot_token = $config['bot_token'];
$webhook_url = $config['webhook_url'];

if ($bot_token === 'YOUR_BOT_TOKEN_HERE') {
    echo "❌ Помилка: Не налаштований токен бота!\n";
    echo "Відредагуйте файл config/telegram.php та додайте токен від @BotFather\n";
    exit(1);
}

echo "🔄 Налаштування webhook для Telegram бота...\n";
echo "Bot Token: " . substr($bot_token, 0, 10) . "...\n";
echo "Webhook URL: $webhook_url\n\n";

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
        CURLOPT_TIMEOUT => 30
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
        throw new Exception("HTTP error: $http_code");
    }
    
    return $decoded;
}

try {
    // 1. Проверка токена - получение информации о боте
    echo "🔄 Перевірка токена бота...\n";
    $botInfo = telegramRequest('getMe', [], $bot_token);
    
    if (!$botInfo['ok']) {
        throw new Exception("Невірний токен бота: " . $botInfo['description']);
    }
    
    echo "✅ Бот знайдено: @{$botInfo['result']['username']} ({$botInfo['result']['first_name']})\n\n";
    
    // 2. Удаление старого webhook
    echo "🔄 Видалення старого webhook...\n";
    $deleteResult = telegramRequest('deleteWebhook', [], $bot_token);
    
    if ($deleteResult['ok']) {
        echo "✅ Старий webhook видалено\n";
    } else {
        echo "⚠️ Помилка при видаленні webhook: {$deleteResult['description']}\n";
    }
    
    // 3. Установка нового webhook
    echo "🔄 Встановлення нового webhook...\n";
    $webhookData = [
        'url' => $webhook_url,
        'max_connections' => 100,
        'allowed_updates' => json_encode(['message', 'callback_query'])
    ];
    
    $setResult = telegramRequest('setWebhook', $webhookData, $bot_token);
    
    if (!$setResult['ok']) {
        throw new Exception("Помилка встановлення webhook: " . $setResult['description']);
    }
    
    echo "✅ Webhook встановлено успішно!\n\n";
    
    // 4. Проверка webhook
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
    }
    
    echo "\n🔄 Тестування webhook...\n";
    
    // Проверка доступности webhook URL
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $webhook_url,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => '{"test": true}',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json']
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200) {
        echo "✅ Webhook URL доступний (HTTP $http_code)\n";
    } else {
        echo "⚠️ Webhook URL повертає код: HTTP $http_code\n";
        echo "Відповідь: $response\n";
    }
    
    // 5. Настройка команд бота
    echo "\n🔄 Налаштування команд бота...\n";
    $commands = [
        ['command' => 'start', 'description' => 'Головне меню'],
        ['command' => 'help', 'description' => 'Довідка по боту'],
        ['command' => 'cancel', 'description' => 'Скасувати поточну дію'],
        ['command' => 'admin', 'description' => 'Адмін-панель']
    ];
    
    $commandsResult = telegramRequest('setMyCommands', [
        'commands' => json_encode($commands)
    ], $bot_token);
    
    if ($commandsResult['ok']) {
        echo "✅ Команди бота налаштовано\n";
    } else {
        echo "⚠️ Помилка налаштування команд: {$commandsResult['description']}\n";
    }
    
    echo "\n🎉 Налаштування завершено успішно!\n\n";
    echo "📝 Наступні кроки:\n";
    echo "1. Додайте свій Telegram ID як адміністратора в БД\n";
    echo "2. Напишіть боту /start для тестування\n";
    echo "3. Перевірте логи в папці logs/ при проблемах\n\n";
    echo "🔗 Посилання на бота: https://t.me/{$botInfo['result']['username']}\n";
    
} catch (Exception $e) {
    echo "❌ Помилка: " . $e->getMessage() . "\n";
    exit(1);
}
?>