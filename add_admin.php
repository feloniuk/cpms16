<?php
// Скрипт для добавления администратора

require_once __DIR__ . '/core/repositories/AdminRepository.php';

echo "👤 Додавання адміністратора\n";
echo "===========================\n\n";

if ($argc < 3) {
    echo "Використання: php add_admin.php TELEGRAM_ID \"ІМ'Я\"\n";
    echo "Приклад: php add_admin.php 123456789 \"Іван Петренко\"\n\n";
    echo "Щоб дізнатися свій Telegram ID:\n";
    echo "1. Напишіть @userinfobot в Telegram\n";
    echo "2. Або використайте @getmyid_bot\n";
    echo "3. Або тимчасово скористайтеся скриптом get_my_id.php\n";
    exit(1);
}

$telegram_id = trim($argv[1]);
$admin_name = trim($argv[2]);

// Валідація Telegram ID
if (!is_numeric($telegram_id) || $telegram_id <= 0) {
    echo "❌ Помилка: Telegram ID повинен бути додатнім числом\n";
    exit(1);
}

// Валідація імені
if (empty($admin_name) || strlen($admin_name) < 2) {
    echo "❌ Помилка: Ім'я повинно містити мінімум 2 символи\n";
    exit(1);
}

try {
    $adminRepo = new AdminRepository();
    
    // Перевірка чи вже існує такий адміністратор
    if ($adminRepo->exists($telegram_id)) {
        echo "⚠️ Адміністратор з Telegram ID $telegram_id вже існує!\n";
        
        $existing = $adminRepo->getByTelegramId($telegram_id);
        echo "Поточні дані:\n";
        echo "ID: {$existing['id']}\n";
        echo "Ім'я: {$existing['name']}\n";
        echo "Активний: " . ($existing['is_active'] ? 'Так' : 'Ні') . "\n";
        echo "Створено: {$existing['created_at']}\n\n";
        
        echo "Оновити дані? (y/N): ";
        $input = trim(fgets(STDIN));
        
        if (strtolower($input) === 'y' || strtolower($input) === 'yes') {
            $adminRepo->update($existing['id'], [
                'name' => $admin_name,
                'is_active' => 1
            ]);
            echo "✅ Дані адміністратора оновлено!\n";
        } else {
            echo "❌ Операція скасована\n";
            exit(0);
        }
    } else {
        // Створення нового адміністратора
        $admin_id = $adminRepo->addAdmin($telegram_id, $admin_name);
        echo "✅ Адміністратора додано успішно!\n";
        echo "ID в БД: $admin_id\n";
    }
    
    echo "\n📋 Інформація про адміністратора:\n";
    echo "Telegram ID: $telegram_id\n";
    echo "Ім'я: $admin_name\n";
    echo "Статус: Активний\n\n";
    
    // Показати всіх адмінів
    echo "📊 Всі адміністратори:\n";
    $all_admins = $adminRepo->getAllAdmins();
    
    if (empty($all_admins)) {
        echo "Адміністраторів не знайдено\n";
    } else {
        echo "ID\tTelegram ID\tІм'я\t\t\tАктивний\tДата створення\n";
        echo str_repeat("-", 80) . "\n";
        
        foreach ($all_admins as $admin) {
            $status = $admin['is_active'] ? 'Так' : 'Ні';
            $name = str_pad($admin['name'], 20);
            echo "{$admin['id']}\t{$admin['telegram_id']}\t$name\t$status\t\t{$admin['created_at']}\n";
        }
    }
    
    echo "\n🎉 Готово! Тепер адміністратор може використовувати команду /admin в боті.\n";
    
} catch (Exception $e) {
    echo "❌ Помилка: " . $e->getMessage() . "\n";
    echo "Переконайтеся, що база даних налаштована та доступна.\n";
    exit(1);
}
?>