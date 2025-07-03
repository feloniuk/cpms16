<?php
// Тест подключения к базе данных и создание тестовых данных

require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/core/repositories/BranchRepository.php';
require_once __DIR__ . '/core/repositories/AdminRepository.php';

try {
    echo "🔄 Тестування підключення до бази даних...\n";
    
    // Тест подключения
    $db = Database::getInstance();
    echo "✅ Підключення до БД успішне!\n\n";
    
    // Создание репозиториев
    $branchRepo = new BranchRepository();
    $adminRepo = new AdminRepository();
    
    echo "🔄 Перевірка структури таблиць...\n";
    
    // Проверка таблиц
    $tables = [
        'branches' => 'Філії',
        'admins' => 'Адміністратори', 
        'repair_requests' => 'Заявки на ремонт',
        'cartridge_replacements' => 'Заміни картриджів',
        'room_inventory' => 'Інвентар кабінетів',
        'inventory_templates' => 'Шаблони інвентарю',
        'api_tokens' => 'API токени',
        'user_states' => 'Стани користувачів'
    ];
    
    foreach ($tables as $table => $description) {
        $stmt = $db->query("SHOW TABLES LIKE '$table'");
        if ($stmt->fetch()) {
            echo "✅ Таблиця '$table' ($description) існує\n";
        } else {
            echo "❌ Таблиця '$table' ($description) НЕ існує!\n";
        }
    }
    
    echo "\n🔄 Створення тестових даних...\n";
    
    // Создание тестовых филиалов
    $testBranches = [
        'Центральний офіс',
        'Філія №1 (Київ)',
        'Філія №2 (Львів)', 
        'Склад'
    ];
    
    foreach ($testBranches as $branchName) {
        if (!$branchRepo->findByName($branchName)) {
            $branchId = $branchRepo->create([
                'name' => $branchName,
                'is_active' => 1
            ]);
            echo "✅ Створено філію: $branchName (ID: $branchId)\n";
        } else {
            echo "ℹ️ Філія '$branchName' вже існує\n";
        }
    }
    
    // Создание API токена для бота
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM api_tokens WHERE name = 'Telegram Bot'");
    $stmt->execute();
    $result = $stmt->fetch();
    
    if ($result['count'] == 0) {
        $botToken = bin2hex(random_bytes(32));
        $stmt = $db->prepare("INSERT INTO api_tokens (name, token, permissions, is_active) VALUES (?, ?, ?, 1)");
        $stmt->execute(['Telegram Bot', $botToken, json_encode(['user', 'admin'])]);
        echo "✅ Створено API токен для бота: $botToken\n";
        echo "📝 Додайте цей токен до конфігурації API\n";
    } else {
        echo "ℹ️ API токен для бота вже існує\n";
    }
    
    // Создание тестовых шаблонов инвентаря
    $testTemplates = [
        ['name' => 'Комп\'ютер стандартний', 'equipment_type' => 'Комп\'ютер', 'brand' => '', 'model' => '', 'requires_serial' => 1, 'requires_inventory' => 1],
        ['name' => 'Монітор стандартний', 'equipment_type' => 'Монітор', 'brand' => '', 'model' => '', 'requires_serial' => 1, 'requires_inventory' => 1],
        ['name' => 'Принтер HP LaserJet', 'equipment_type' => 'Принтер', 'brand' => 'HP', 'model' => 'LaserJet', 'requires_serial' => 1, 'requires_inventory' => 1],
        ['name' => 'Клавіатура', 'equipment_type' => 'Клавіатура', 'brand' => '', 'model' => '', 'requires_serial' => 0, 'requires_inventory' => 1],
        ['name' => 'Миша', 'equipment_type' => 'Миша', 'brand' => '', 'model' => '', 'requires_serial' => 0, 'requires_inventory' => 1]
    ];
    
    foreach ($testTemplates as $template) {
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM inventory_templates WHERE name = ?");
        $stmt->execute([$template['name']]);
        $result = $stmt->fetch();
        
        if ($result['count'] == 0) {
            $stmt = $db->prepare("INSERT INTO inventory_templates (name, equipment_type, brand, model, requires_serial, requires_inventory) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $template['name'],
                $template['equipment_type'],
                $template['brand'],
                $template['model'],
                $template['requires_serial'],
                $template['requires_inventory']
            ]);
            echo "✅ Створено шаблон: {$template['name']}\n";
        } else {
            echo "ℹ️ Шаблон '{$template['name']}' вже існує\n";
        }
    }
    
    echo "\n📊 Статистика:\n";
    echo "Філії: " . $branchRepo->count() . "\n";
    echo "Адміністратори: " . $adminRepo->count() . "\n";
    
    $stmt = $db->query("SELECT COUNT(*) as count FROM inventory_templates");
    $result = $stmt->fetch();
    echo "Шаблони інвентарю: " . $result['count'] . "\n";
    
    $stmt = $db->query("SELECT COUNT(*) as count FROM api_tokens");
    $result = $stmt->fetch();
    echo "API токени: " . $result['count'] . "\n";
    
    echo "\n✅ Тест завершено успішно!\n";
    echo "\n📝 Наступні кроки:\n";
    echo "1. Створіть Telegram бота через @BotFather\n";
    echo "2. Додайте токен бота до config/telegram.php\n";
    echo "3. Додайте свій Telegram ID як адміністратора:\n";
    echo "   INSERT INTO admins (telegram_id, name) VALUES (YOUR_TELEGRAM_ID, 'Ваше ім\'я');\n";
    echo "4. Налаштуйте webhook для бота\n";
    
} catch (Exception $e) {
    echo "❌ Помилка: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
?>