<?php
// Скрипт для проверки сессий пользователей

require_once __DIR__ . '/telegram-bot/SessionManager.php';

echo "🔍 Проверка сессий пользователей\n";
echo "=================================\n\n";

try {
    $sessionManager = SessionManager::getInstance();
    
    // Получаем все активные сессии
    $allSessions = $sessionManager->getAllActiveSessions();
    
    if (empty($allSessions)) {
        echo "📭 Нет активных сессий пользователей\n";
    } else {
        echo "📊 Найдено активных сессий: " . count($allSessions) . "\n\n";
        
        foreach ($allSessions as $user_id => $session) {
            echo "👤 Пользователь: $user_id\n";
            echo "📍 Состояние: " . ($session['current_state'] ?? 'NULL') . "\n";
            echo "⏰ Последняя активность: " . date('Y-m-d H:i:s', $session['last_activity']) . "\n";
            
            if (!empty($session['temp_data'])) {
                echo "📋 Временные данные:\n";
                foreach ($session['temp_data'] as $key => $value) {
                    echo "   $key: $value\n";
                }
            } else {
                echo "📋 Временные данные: отсутствуют\n";
            }
            echo str_repeat("-", 40) . "\n";
        }
    }
    
    // Тест работы с сессиями
    echo "\n🧪 Тест работы SessionManager:\n";
    
    $test_user_id = 999999999; // Тестовый ID
    
    // Тест установки состояния
    echo "1. Тестируем setState...\n";
    $sessionManager->setState($test_user_id, 'test_state');
    $sessionManager->setTempData($test_user_id, 'test_key', 'test_value');
    echo "✅ setState выполнен\n";
    
    // Тест получения состояния
    echo "2. Тестируем getState...\n";
    $state = $sessionManager->getState($test_user_id);
    if ($state === 'test_state') {
        echo "✅ getState вернул правильное состояние: $state\n";
    } else {
        echo "❌ getState вернул неправильное состояние: $state\n";
    }
    
    // Тест получения временных данных
    echo "3. Тестируем getTempData...\n";
    $tempData = $sessionManager->getTempData($test_user_id);
    if (isset($tempData['test_key']) && $tempData['test_key'] === 'test_value') {
        echo "✅ getTempData работает корректно\n";
    } else {
        echo "❌ getTempData не работает\n";
    }
    
    // Тест добавления данных
    echo "4. Тестируем setTempData...\n";
    $sessionManager->setTempData($test_user_id, 'new_key', 'new_value');
    $newValue = $sessionManager->getTempData($test_user_id, 'new_key');
    if ($newValue === 'new_value') {
        echo "✅ setTempData работает корректно\n";
    } else {
        echo "❌ setTempData не работает\n";
    }
    
    // Показать финальное состояние тестовой сессии
    echo "5. Финальное состояние тестовой сессии:\n";
    $sessionInfo = $sessionManager->getSessionInfo($test_user_id);
    if ($sessionInfo) {
        echo "   Состояние: " . $sessionInfo['current_state'] . "\n";
        echo "   Данные: " . json_encode($sessionInfo['temp_data'], JSON_UNESCAPED_UNICODE) . "\n";
        echo "   Активность: " . date('Y-m-d H:i:s', $sessionInfo['last_activity']) . "\n";
    }
    
    // Очистка тестовых данных
    echo "6. Очищаем тестовые данные...\n";
    $sessionManager->clearSession($test_user_id);
    echo "✅ Очищено\n";
    
    // Тест очистки старых сессий
    echo "7. Тестируем очистку старых сессий...\n";
    $sessionManager->clearOldSessions(0); // Очистить все старше 0 секунд
    echo "✅ Очистка старых сессий выполнена\n";
    
    echo "\n💡 Преимущества сессий в памяти:\n";
    echo "- Быстрое чтение/запись данных\n";
    echo "- Нет нагрузки на базу данных\n";
    echo "- Автоматическая очистка старых сессий\n";
    echo "- Простота отладки\n";
    echo "- Надежность работы\n";
    
    echo "\n📊 Рекомендации:\n";
    echo "- Сессии хранятся в памяти только во время работы скрипта\n";
    echo "- Для каждого webhook вызова создается новый экземпляр\n";
    echo "- Состояния сохраняются между сообщениями одного пользователя\n";
    echo "- Старые сессии автоматически очищаются\n";
    
} catch (Exception $e) {
    echo "❌ Ошибка: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
?>