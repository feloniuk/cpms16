<?php
// Включение обработки ошибок
error_reporting(E_ALL);
ini_set('display_errors', 0); // Отключаем вывод ошибок в ответ

// Установка часового пояса
date_default_timezone_set('Europe/Kiev');

// Подключение основного класса бота
require_once __DIR__ . '/TelegramBot.php';

try {
    // Логирование начала обработки
    $log_entry = date('Y-m-d H:i:s') . " - Webhook called from IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . "\n";
    file_put_contents(__DIR__ . '/../logs/webhook.log', $log_entry, FILE_APPEND | LOCK_EX);
    
    // Получение входящих данных
    $input = file_get_contents('php://input');
    
    if (empty($input)) {
        http_response_code(400);
        echo "No input data";
        exit;
    }
    
    // Логирование входящих данных
    $log_entry = date('Y-m-d H:i:s') . " - Input data: " . $input . "\n";
    file_put_contents(__DIR__ . '/../logs/webhook.log', $log_entry, FILE_APPEND | LOCK_EX);
    
    // Декодирование JSON
    $update = json_decode($input, true);
    
    if ($update === null) {
        http_response_code(400);
        echo "Invalid JSON";
        exit;
    }
    
    // Создание экземпляра бота и обработка обновления
    $bot = new TelegramBot();
    $bot->handleUpdate($update);
    
    // Успешный ответ
    http_response_code(200);
    echo "OK";
    
} catch (Exception $e) {
    // Логирование ошибки
    $error_entry = date('Y-m-d H:i:s') . " - WEBHOOK ERROR: " . $e->getMessage() . "\n";
    $error_entry .= "Trace: " . $e->getTraceAsString() . "\n";
    file_put_contents(__DIR__ . '/../logs/errors.log', $error_entry, FILE_APPEND | LOCK_EX);
    
    // Ответ об ошибке
    http_response_code(500);
    echo "Internal Server Error";
}
?>