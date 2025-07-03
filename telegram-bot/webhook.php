<?php
// Тимчасовий webhook для тестування
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Логування
file_put_contents(__DIR__ . '/../logs/webhook.log', 
    date('Y-m-d H:i:s') . " - Webhook called\n", FILE_APPEND);

// Отримання даних
$input = file_get_contents('php://input');
$update = json_decode($input, true);

// Логування даних
file_put_contents(__DIR__ . '/../logs/webhook.log', 
    date('Y-m-d H:i:s') . " - Data: " . $input . "\n", FILE_APPEND);

// Відповідь
http_response_code(200);
echo "OK";
?>