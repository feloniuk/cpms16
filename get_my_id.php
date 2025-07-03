<?php
// Временный скрипт для получения Telegram ID
// ВАЖНО: Удалите этот файл после получения ID!

// Получение входящих данных
$input = file_get_contents('php://input');
$update = json_decode($input, true);

// Логирование
$log_entry = date('Y-m-d H:i:s') . " - ID Request: " . $input . "\n";
file_put_contents(__DIR__ . '/logs/telegram_ids.log', $log_entry, FILE_APPEND | LOCK_EX);

if (isset($update['message'])) {
    $user = $update['message']['from'];
    $chat_id = $update['message']['chat']['id'];
    
    $user_id = $user['id'];
    $username = $user['username'] ?? 'N/A';
    $first_name = $user['first_name'] ?? '';
    $last_name = $user['last_name'] ?? '';
    $full_name = trim($first_name . ' ' . $last_name);
    
    // Сохранение в лог
    $id_info = "========================================\n";
    $id_info .= "Дата: " . date('Y-m-d H:i:s') . "\n";
    $id_info .= "Telegram ID: $user_id\n";
    $id_info .= "Username: @$username\n";
    $id_info .= "Полное имя: $full_name\n";
    $id_info .= "Chat ID: $chat_id\n";
    $id_info .= "========================================\n\n";
    
    file_put_contents(__DIR__ . '/logs/telegram_ids.log', $id_info, FILE_APPEND | LOCK_EX);
    
    // Отправка ответа пользователю
    $config = require __DIR__ . '/config/telegram.php';
    $bot_token = $config['bot_token'];
    
    if ($bot_token !== 'YOUR_BOT_TOKEN_HERE') {
        $response_text = "🆔 Ваш Telegram ID: <code>$user_id</code>\n\n";
        $response_text .= "📋 Додаткова інформація:\n";
        $response_text .= "👤 Ім'я: $full_name\n";
        $response_text .= "🔗 Username: @$username\n";
        $response_text .= "💬 Chat ID: $chat_id\n\n";
        $response_text .= "📝 Скопіюйте ваш ID та використайте команду:\n";
        $response_text .= "<code>php add_admin.php $user_id \"$full_name\"</code>\n\n";
        $response_text .= "⚠️ Після отримання ID видаліть файл get_my_id.php з сервера!";
        
        $api_url = "https://api.telegram.org/bot$bot_token/sendMessage";
        
        $data = [
            'chat_id' => $chat_id,
            'text' => $response_text,
            'parse_mode' => 'HTML'
        ];
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $api_url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10
        ]);
        
        curl_exec($ch);
        curl_close($ch);
    }
    
    echo "ID saved: $user_id";
} else {
    echo "No message data";
}

// Отображение сохраненных ID (только при прямом доступе через браузер)
if (!isset($_POST) || empty(file_get_contents('php://input'))) {
    echo "<html><head><meta charset='utf-8'><title>Telegram ID Collector</title></head><body>";
    echo "<h2>🆔 Сборщик Telegram ID</h2>";
    echo "<p><strong>Инструкция:</strong></p>";
    echo "<ol>";
    echo "<li>Временно измените webhook на этот файл</li>";
    echo "<li>Напишите любое сообщение боту</li>";
    echo "<li>Ваш ID появится ниже и в логах</li>";
    echo "<li>Верните webhook обратно на webhook.php</li>";
    echo "<li><strong>УДАЛИТЕ этот файл!</strong></li>";
    echo "</ol>";
    
    if (file_exists(__DIR__ . '/logs/telegram_ids.log')) {
        echo "<h3>📋 Собранные ID:</h3>";
        echo "<pre>" . htmlspecialchars(file_get_contents(__DIR__ . '/logs/telegram_ids.log')) . "</pre>";
    } else {
        echo "<p>ID еще не собраны.</p>";
    }
    
    echo "</body></html>";
}
?>