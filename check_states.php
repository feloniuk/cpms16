<?php
// Скрипт для проверки состояний пользователей

require_once __DIR__ . '/core/repositories/UserStateRepository.php';

echo "🔍 Проверка состояний пользователей\n";
echo "===================================\n\n";

try {
    $userStateRepo = new UserStateRepository();
    
    // Получаем все состояния
    $db = Database::getInstance();
    $stmt = $db->query("SELECT * FROM user_states ORDER BY updated_at DESC");
    $states = $stmt->fetchAll();
    
    if (empty($states)) {
        echo "📭 Нет сохраненных состояний пользователей\n";
    } else {
        echo "📊 Найдено состояний: " . count($states) . "\n\n";
        
        foreach ($states as $state) {
            echo "👤 Пользователь: {$state['telegram_id']}\n";
            echo "📍 Состояние: " . ($state['current_state'] ?? 'NULL') . "\n";
            echo "⏰ Обновлено: {$state['updated_at']}\n";
            
            if ($state['temp_data']) {
                $temp_data = json_decode($state['temp_data'], true);
                echo "📋 Временные данные:\n";
                foreach ($temp_data as $key => $value) {
                    echo "   $key: $value\n";
                }
            } else {
                echo "📋 Временные данные: отсутствуют\n";
            }
            echo str_repeat("-", 40) . "\n";
        }
    }
    
    // Проверка работы методов репозитория
    echo "\n🧪 Тест методов UserStateRepository:\n";
    
    $test_user_id = 999999999; // Тестовый ID
    
    // Тест установки состояния
    echo "1. Тестируем setState...\n";
    $result = $userStateRepo->setState($test_user_id, 'test_state', ['test_key' => 'test_value']);
    echo ($result ? "✅" : "❌") . " setState выполнен\n";
    
    // Тест получения состояния
    echo "2. Тестируем getUserState...\n";
    $testState = $userStateRepo->getUserState($test_user_id);
    if ($testState) {
        echo "✅ getUserState вернул данные:\n";
        echo "   current_state: {$testState['current_state']}\n";
        echo "   temp_data: " . json_encode($testState['temp_data'], JSON_UNESCAPED_UNICODE) . "\n";
    } else {
        echo "❌ getUserState не вернул данные\n";
    }
    
    // Тест addToTempData
    echo "3. Тестируем addToTempData...\n";
    $result = $userStateRepo->addToTempData($test_user_id, 'new_key', 'new_value');
    echo ($result ? "✅" : "❌") . " addToTempData выполнен\n";
    
    // Проверка обновленных данных
    $updatedState = $userStateRepo->getUserState($test_user_id);
    if ($updatedState && isset($updatedState['temp_data']['new_key'])) {
        echo "✅ Данные обновлены корректно\n";
    } else {
        echo "❌ Данные не обновились\n";
    }
    
    // Очистка тестовых данных
    echo "4. Очищаем тестовые данные...\n";
    $userStateRepo->clearState($test_user_id);
    echo "✅ Очищено\n";
    
    echo "\n💡 Рекомендации:\n";
    echo "- Если состояния не сохраняются, проверьте права на запись в БД\n";
    echo "- Проверьте что в user_states есть записи для активных пользователей\n";
    echo "- Убедитесь что temp_data корректно сериализуется в JSON\n";
    
} catch (Exception $e) {
    echo "❌ Ошибка: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
?>