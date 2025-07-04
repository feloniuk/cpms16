<?php
// Скрипт для проверки состояния конкретного пользователя

if ($argc < 2) {
    echo "Використання: php check_user_state.php TELEGRAM_ID\n";
    echo "Приклад: php check_user_state.php 123456789\n";
    exit(1);
}

$telegram_id = $argv[1];

require_once __DIR__ . '/core/repositories/UserStateRepository.php';

try {
    $userStateRepo = new UserStateRepository();
    
    echo "🔍 Перевірка стану користувача: $telegram_id\n";
    echo str_repeat("=", 50) . "\n\n";
    
    $userState = $userStateRepo->getUserState($telegram_id);
    
    if (!$userState) {
        echo "❌ Стан користувача не знайдено в БД\n";
        exit(0);
    }
    
    echo "✅ Стан користувача знайдено:\n\n";
    echo "📍 Поточний стан: " . ($userState['current_state'] ?? 'NULL') . "\n";
    echo "⏰ Останнє оновлення: " . $userState['updated_at'] . "\n\n";
    
    if ($userState['temp_data']) {
        echo "📋 Тимчасові дані:\n";
        foreach ($userState['temp_data'] as $key => $value) {
            echo "   $key: $value\n";
        }
        echo "\n";
    } else {
        echo "📋 Тимчасові дані відсутні\n\n";
    }
    
    // Детальна информация о состоянии
    switch ($userState['current_state']) {
        case 'repair_awaiting_branch':
            echo "ℹ️ Користувач очікує вибору філії для заявки на ремонт\n";
            break;
        case 'repair_awaiting_room':
            echo "ℹ️ Користувач повинен ввести номер кабінету\n";
            echo "Філія: " . ($userState['temp_data']['branch_name'] ?? 'не збережено') . "\n";
            break;
        case 'repair_awaiting_description':
            echo "ℹ️ Користувач повинен ввести опис проблеми\n";
            echo "Філія: " . ($userState['temp_data']['branch_name'] ?? 'не збережено') . "\n";
            echo "Кабінет: " . ($userState['temp_data']['room_number'] ?? 'не збережено') . "\n";
            break;
        case 'repair_awaiting_phone':
            echo "ℹ️ Користувач повинен ввести телефон або пропустити\n";
            echo "Філія: " . ($userState['temp_data']['branch_name'] ?? 'не збережено') . "\n";
            echo "Кабінет: " . ($userState['temp_data']['room_number'] ?? 'не збережено') . "\n";
            echo "Проблема: " . ($userState['temp_data']['description'] ?? 'не збережено') . "\n";
            break;
        default:
            echo "ℹ️ Невідомий стан: " . $userState['current_state'] . "\n";
    }
    
    // Проверка что все нужные данные есть
    if ($userState['current_state'] === 'repair_awaiting_phone') {
        $requiredFields = ['branch_id', 'branch_name', 'room_number', 'description'];
        $missingFields = [];
        
        foreach ($requiredFields as $field) {
            if (!isset($userState['temp_data'][$field]) || empty($userState['temp_data'][$field])) {
                $missingFields[] = $field;
            }
        }
        
        if (empty($missingFields)) {
            echo "\n✅ Всі необхідні дані для створення заявки присутні\n";
        } else {
            echo "\n❌ Відсутні дані: " . implode(', ', $missingFields) . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Помилка: " . $e->getMessage() . "\n";
}
?>