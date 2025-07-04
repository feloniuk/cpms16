<?php
// Временный webhook с расширенной отладкой
// Замените им обычный webhook.php для отладки

error_reporting(E_ALL);
ini_set('display_errors', 0);
date_default_timezone_set('Europe/Kiev');

// Функция детального логирования
function debugLog($message, $data = null) {
    $logEntry = date('Y-m-d H:i:s') . " [DEBUG] " . $message;
    if ($data !== null) {
        $logEntry .= "\nData: " . print_r($data, true);
    }
    $logEntry .= "\n" . str_repeat("-", 50) . "\n";
    file_put_contents(__DIR__ . '/../logs/debug.log', $logEntry, FILE_APPEND | LOCK_EX);
}

try {
    debugLog("Webhook вызван", [
        'IP' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'Method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
        'Headers' => getallheaders()
    ]);
    
    // Получение входящих данных
    $input = file_get_contents('php://input');
    debugLog("Получены данные", ['input_length' => strlen($input), 'input' => $input]);
    
    if (empty($input)) {
        debugLog("Пустые входящие данные");
        http_response_code(400);
        echo "No input data";
        exit;
    }
    
    // Декодирование JSON
    $update = json_decode($input, true);
    
    if ($update === null) {
        debugLog("Ошибка декодирования JSON", ['json_last_error' => json_last_error_msg()]);
        http_response_code(400);
        echo "Invalid JSON";
        exit;
    }
    
    debugLog("JSON декодирован успешно", $update);
    
    // Определение типа обновления
    if (isset($update['message'])) {
        debugLog("Получено сообщение", [
            'user_id' => $update['message']['from']['id'],
            'username' => $update['message']['from']['username'] ?? 'N/A',
            'text' => $update['message']['text'] ?? 'N/A'
        ]);
    } elseif (isset($update['callback_query'])) {
        debugLog("Получен callback", [
            'user_id' => $update['callback_query']['from']['id'],
            'username' => $update['callback_query']['from']['username'] ?? 'N/A', 
            'data' => $update['callback_query']['data']
        ]);
    }
    
    // Проверка подключения к БД
    debugLog("Проверка подключения к БД");
    require_once __DIR__ . '/../core/Database.php';
    $db = Database::getInstance();
    debugLog("БД подключена успешно");
    
    // Проверка репозиториев
    debugLog("Загрузка репозиториев");
    require_once __DIR__ . '/../core/repositories/AdminRepository.php';
    require_once __DIR__ . '/../core/repositories/BranchRepository.php';
    require_once __DIR__ . '/../core/repositories/UserStateRepository.php';
    
    $adminRepo = new AdminRepository();
    $branchRepo = new BranchRepository();
    $userStateRepo = new UserStateRepository();
    debugLog("Репозитории созданы успешно");
    
    // Проверка филиалов
    $branches = $branchRepo->getActive();
    debugLog("Филиалы загружены", ['count' => count($branches), 'branches' => $branches]);
    
    // Проверка админов
    $user_id = $update['message']['from']['id'] ?? $update['callback_query']['from']['id'] ?? 0;
    $isAdmin = $adminRepo->isAdmin($user_id);
    debugLog("Проверка прав пользователя", ['user_id' => $user_id, 'is_admin' => $isAdmin]);
    
    // Загрузка основного класса бота
    debugLog("Загрузка TelegramBot класса");
    require_once __DIR__ . '/TelegramBot.php';
    
    $bot = new TelegramBot();
    debugLog("TelegramBot создан успешно");
    
    // Обработка обновления
    debugLog("Начало обработки обновления");
    $bot->handleUpdate($update);
    debugLog("Обработка завершена успешно");
    
    // Успешный ответ
    http_response_code(200);
    echo "OK";
    
} catch (Exception $e) {
    debugLog("КРИТИЧЕСКАЯ ОШИБКА", [
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);
    
    // Дополнительно в error лог
    $error_entry = date('Y-m-d H:i:s') . " - WEBHOOK ERROR: " . $e->getMessage() . "\n";
    $error_entry .= "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
    $error_entry .= "Trace: " . $e->getTraceAsString() . "\n\n";
    file_put_contents(__DIR__ . '/../logs/errors.log', $error_entry, FILE_APPEND | LOCK_EX);
    
    http_response_code(500);
    echo "Internal Server Error";
}

debugLog("Webhook завершен");
?>