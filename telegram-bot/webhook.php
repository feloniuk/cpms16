<?php
// Простой webhook для IT Support Bot
error_reporting(E_ALL);
ini_set('display_errors', 0);
date_default_timezone_set('Europe/Kiev');

// Создание директории для логов если не существует
$logsDir = __DIR__ . '/../logs/';
if (!file_exists($logsDir)) {
    mkdir($logsDir, 0777, true);
}

// Функция логирования
function logMessage($message, $filename = 'webhook.log') {
    $logsDir = __DIR__ . '/../logs/';
    $logEntry = date('Y-m-d H:i:s') . " - " . $message . "\n";
    file_put_contents($logsDir . $filename, $logEntry, FILE_APPEND | LOCK_EX);
}

try {
    // Логирование начала обработки
    logMessage("=== WEBHOOK START ===");
    logMessage("Webhook called from IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
    
    // Получение входящих данных
    $input = file_get_contents('php://input');
    
    if (empty($input)) {
        logMessage("Empty input data");
        http_response_code(400);
        echo "No input data";
        exit;
    }
    
    // Логирование входящих данных
    logMessage("Input data: " . $input);
    
    // Декодирование JSON
    $update = json_decode($input, true);
    
    if ($update === null) {
        logMessage("Invalid JSON: " . json_last_error_msg());
        http_response_code(400);
        echo "Invalid JSON";
        exit;
    }
    
    // Определение пользователя
    $user_id = null;
    if (isset($update['message'])) {
        $user_id = $update['message']['from']['id'];
        logMessage("Message from user: $user_id");
    } elseif (isset($update['callback_query'])) {
        $user_id = $update['callback_query']['from']['id'];
        logMessage("Callback from user: $user_id");
    }
    
    // Проверка наличия необходимых файлов
    $requiredFiles = [
        __DIR__ . '/../core/Database.php',
        __DIR__ . '/TelegramBot.php'
    ];
    
    foreach ($requiredFiles as $file) {
        if (!file_exists($file)) {
            logMessage("Missing required file: " . $file, 'errors.log');
            http_response_code(500);
            echo "Configuration error";
            exit;
        }
    }
    
    // Подключение основного класса бота
    require_once __DIR__ . '/TelegramBot.php';
    
    // Создание экземпляра бота и обработка обновления
    $bot = new TelegramBot();
    
    // Обработка обновления
    $bot->handleUpdate($update);
    
    logMessage("=== WEBHOOK END SUCCESS ===");
    
    // Успешный ответ
    http_response_code(200);
    echo "OK";
    
} catch (Exception $e) {
    // Логирование ошибки
    $errorMessage = "WEBHOOK ERROR: " . $e->getMessage() . "\n";
    $errorMessage .= "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
    $errorMessage .= "Trace: " . $e->getTraceAsString() . "\n";
    
    logMessage($errorMessage, 'errors.log');
    logMessage("=== WEBHOOK END ERROR ===");
    
    // Ответ об ошибке
    http_response_code(500);
    echo "Internal Server Error";
}
?>