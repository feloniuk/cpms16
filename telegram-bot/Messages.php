<?php

class Messages {
    
    // Основные сообщения
    public static function welcome($username) {
        $name = $username ? "@$username" : "Користувач";
        return "🤖 Вітаю, $name!\n\n" .
               "Я бот для подачі заявок на ремонт обладнання та замін картриджів.\n\n" .
               "Що ви хочете зробити?";
    }
    
    public static function help() {
        return "📋 Довідка по боту:\n\n" .
               "🔧 <b>Виклик ІТ майстра</b> - подати заявку на ремонт обладнання\n" .
               "🖨️ <b>Заміна картриджа</b> - запит на заміну картриджа\n\n" .
               "📞 Команди:\n" .
               "/start - Головне меню\n" .
               "/help - Ця довідка\n" .
               "/cancel - Скасувати поточну дію\n" .
               "/admin - Адмін-панель (тільки для адміністраторів)\n\n" .
               "❓ Якщо у вас виникли питання, зверніться до адміністратора.";
    }
    
    public static function mainMenu() {
        return "Оберіть дію з головного меню:";
    }
    
    public static function actionCanceled() {
        return "Дію скасовано. Оберіть нову дію:";
    }
    
    public static function unknownCommand() {
        return "Невідома команда. Скористайтеся /help для довідки.";
    }
    
    public static function unknownAction() {
        return "Невідома дія. Поверніться до головного меню:";
    }
    
    public static function unknownState() {
        return "Невідомий стан. Поверніться до головного меню:";
    }
    
    public static function noAccess() {
        return "У вас немає прав адміністратора.";
    }
    
    // Сообщения для заявок на ремонт
    public static function repairStart() {
        return "🔧 <b>Виклик ІТ майстра</b>\n\nОберіть філію:";
    }
    
    public static function repairBranchSelected($branchName) {
        return "🔧 <b>Виклик ІТ майстра</b>\n" .
               "Філія: <b>$branchName</b>\n\n" .
               "Введіть номер кабінету:";
    }
    
    public static function repairRoomSelected($branchName, $roomNumber) {
        return "🔧 <b>Виклик ІТ майстра</b>\n" .
               "Філія: <b>$branchName</b>\n" .
               "Кабінет: <b>$roomNumber</b>\n\n" .
               "Опишіть проблему (від 10 до 1000 символів):";
    }
    
    public static function repairDescriptionEntered($branchName, $roomNumber, $description) {
        return "🔧 <b>Виклик ІТ майстра</b>\n" .
               "Філія: <b>$branchName</b>\n" .
               "Кабінет: <b>$roomNumber</b>\n" .
               "Проблема: <b>" . htmlspecialchars(substr($description, 0, 100)) . "...</b>\n\n" .
               "Введіть номер телефону для зв'язку або натисніть 'Пропустити':";
    }
    
    public static function repairSuccess($requestId, $branchName, $roomNumber, $description, $phone = null) {
        $message = "✅ <b>Заявку створено успішно!</b>\n\n" .
                   "📋 <b>Деталі заявки № $requestId:</b>\n" .
                   "🏢 Філія: $branchName\n" .
                   "🚪 Кабінет: $roomNumber\n" .
                   "📝 Проблема: " . htmlspecialchars($description) . "\n";
        
        if (!empty($phone)) {
            $message .= "📞 Телефон: $phone\n";
        }
        
        $message .= "\n📧 Адміністратори отримали сповіщення про вашу заявку.\n" .
                    "⏰ Очікуйте на зв'язок від ІТ майстра.";
        
        return $message;
    }
    
    public static function repairErrorInvalidRoom() {
        return "❌ Некоректний номер кабінету. Введіть номер кабінету (до 50 символів):";
    }
    
    public static function repairErrorInvalidDescription() {
        return "❌ Опис повинен містити від 10 до 1000 символів. Спробуйте ще раз:";
    }
    
    public static function repairErrorInvalidPhone() {
        return "❌ Некоректний формат телефону. Введіть номер у форматі +380XXXXXXXXX або натисніть 'Пропустити':";
    }
    
    // Сообщения для замены картриджей
    public static function cartridgeStart() {
        return "🖨️ <b>Заміна картриджа</b>\n\nОберіть філію:";
    }
    
    public static function cartridgeBranchSelected($branchName) {
        return "🖨️ <b>Заміна картриджа</b>\n" .
               "Філія: <b>$branchName</b>\n\n" .
               "Введіть номер кабінету:";
    }
    
    public static function cartridgeRoomSelected($branchName, $roomNumber) {
        return "🖨️ <b>Заміна картриджа</b>\n" .
               "Філія: <b>$branchName</b>\n" .
               "Кабінет: <b>$roomNumber</b>\n\n" .
               "Введіть інвентарний або серійний номер принтера:";
    }
    
    public static function cartridgePrinterEntered($branchName, $roomNumber, $printerInfo) {
        return "🖨️ <b>Заміна картриджа</b>\n" .
               "Філія: <b>$branchName</b>\n" .
               "Кабінет: <b>$roomNumber</b>\n" .
               "Принтер: <b>$printerInfo</b>\n\n" .
               "Введіть тип картриджа (наприклад, HP CF217A):";
    }
    
    public static function cartridgeSuccess($requestId, $branchName, $roomNumber, $printerInfo, $cartridgeType) {
        return "✅ <b>Запит на заміну картриджа створено!</b>\n\n" .
               "📋 <b>Деталі запиту № $requestId:</b>\n" .
               "🏢 Філія: $branchName\n" .
               "🚪 Кабінет: $roomNumber\n" .
               "🖨️ Принтер: $printerInfo\n" .
               "🛒 Картридж: " . htmlspecialchars($cartridgeType) . "\n" .
               "\n📧 Адміністратори отримали сповіщення про ваш запит.";
    }
    
    public static function cartridgeErrorInvalidRoom() {
        return "❌ Некоректний номер кабінету. Введіть номер кабінету (до 50 символів):";
    }
    
    public static function cartridgeErrorInvalidPrinter() {
        return "❌ Введіть номер для пошуку принтера:";
    }
    
    public static function cartridgeErrorInvalidType() {
        return "❌ Введіть тип картриджа:";
    }
    
    // Админские сообщения
    public static function adminMenu() {
        return "⚙️ Адмін-панель:";
    }
    
    public static function adminRepairsList($repairs, $page, $totalPages) {
        if (empty($repairs)) {
            return "📋 <b>Заявки на ремонт</b>\n\nЗаявок не знайдено.";
        }
        
        $message = "📋 <b>Заявки на ремонт</b> (стор. $page з $totalPages)\n\n";
        
        foreach ($repairs as $repair) {
            $status = self::getStatusEmoji($repair['status']);
            $date = date('d.m.Y H:i', strtotime($repair['created_at']));
            $username = $repair['username'] ? "@{$repair['username']}" : "ID: {$repair['user_telegram_id']}";
            
            $message .= "🔧 <b>#{$repair['id']}</b> $status\n";
            $message .= "📍 {$repair['branch_name']} - каб. {$repair['room_number']}\n";
            $message .= "📝 " . self::truncateText($repair['description'], 50) . "\n";
            $message .= "👤 $username | ⏰ $date\n\n";
        }
        
        return $message;
    }
    
    public static function adminRepairDetails($repair) {
        $status = self::getStatusEmoji($repair['status']);
        $date = date('d.m.Y H:i', strtotime($repair['created_at']));
        $updated = date('d.m.Y H:i', strtotime($repair['updated_at']));
        $username = $repair['username'] ? "@{$repair['username']}" : "ID: {$repair['user_telegram_id']}";
        
        $message = "🔧 <b>Заявка № {$repair['id']}</b> $status\n\n";
        $message .= "📍 <b>Філія:</b> {$repair['branch_name']}\n";
        $message .= "🚪 <b>Кабінет:</b> {$repair['room_number']}\n";
        $message .= "📝 <b>Проблема:</b>\n" . htmlspecialchars($repair['description']) . "\n\n";
        $message .= "👤 <b>Користувач:</b> $username\n";
        
        if (!empty($repair['phone'])) {
            $message .= "📞 <b>Телефон:</b> {$repair['phone']}\n";
        }
        
        $message .= "⏰ <b>Створено:</b> $date\n";
        $message .= "🔄 <b>Оновлено:</b> $updated\n";
        
        return $message;
    }
    
    public static function adminCartridgesList($cartridges, $page, $totalPages) {
        if (empty($cartridges)) {
            return "🖨️ <b>Історія картриджів</b>\n\nЗаписів не знайдено.";
        }
        
        $message = "🖨️ <b>Історія картриджів</b> (стор. $page з $totalPages)\n\n";
        
        foreach ($cartridges as $cartridge) {
            $date = date('d.m.Y', strtotime($cartridge['replacement_date']));
            $username = $cartridge['username'] ? "@{$cartridge['username']}" : "ID: {$cartridge['user_telegram_id']}";
            
            $message .= "🖨️ <b>#{$cartridge['id']}</b>\n";
            $message .= "📍 {$cartridge['branch_name']} - каб. {$cartridge['room_number']}\n";
            $message .= "🛒 {$cartridge['cartridge_type']}\n";
            $message .= "👤 $username | 📅 $date\n\n";
        }
        
        return $message;
    }
    
    public static function adminBranchesList($branches) {
        if (empty($branches)) {
            return "🏢 <b>Управління філіями</b>\n\nФілій не знайдено.";
        }
        
        $message = "🏢 <b>Управління філіями</b>\n\n";
        
        foreach ($branches as $branch) {
            $status = $branch['is_active'] ? '✅' : '❌';
            $message .= "$status <b>{$branch['name']}</b> (ID: {$branch['id']})\n";
        }
        
        return $message;
    }
    
    public static function adminAddBranch() {
        return "🏢 <b>Додати філію</b>\n\nВведіть назву нової філії:";
    }
    
    public static function adminBranchAdded($branchName) {
        return "✅ Філію '$branchName' успішно додано!";
    }
    
    public static function adminBranchExists() {
        return "❌ Філія з такою назвою вже існує!";
    }
    
    public static function adminBranchInvalidName() {
        return "❌ Назва філії повинна містити від 2 до 255 символів. Спробуйте ще раз:";
    }
    
    public static function adminInventoryStart() {
        return "📋 <b>Інвентаризація кабінету</b>\n\nОберіть філію:";
    }
    
    public static function adminInventoryRoomPrompt($branchName) {
        return "📋 <b>Інвентаризація кабінету</b>\n" .
               "Філія: <b>$branchName</b>\n\n" .
               "Введіть номер кабінету:";
    }
    
    public static function adminInventoryEquipmentPrompt($branchName, $roomNumber) {
        return "📋 <b>Інвентаризація кабінету</b>\n" .
               "Філія: <b>$branchName</b>\n" .
               "Кабінет: <b>$roomNumber</b>\n\n" .
               "Введіть дані обладнання через кому:\n" .
               "<code>Тип, Бренд, Модель, Серійний номер, Інвентарний номер</code>\n\n" .
               "Приклад:\n" .
               "<code>Принтер, HP, LaserJet Pro, SN123456, INV001</code>";
    }
    
    public static function adminInventoryAdded($equipmentType, $inventoryNumber) {
        return "✅ Обладнання додано:\n" .
               "🖥️ $equipmentType\n" .
               "🔢 Інв. номер: $inventoryNumber";
    }
    
    public static function adminInventoryError() {
        return "❌ Некоректний формат даних. Введіть через кому:\n" .
               "<code>Тип, Бренд, Модель, Серійний номер, Інвентарний номер</code>";
    }
    
    public static function adminSearchStart() {
        return "🔍 <b>Пошук по інвентарю</b>\n\nВведіть пошуковий запит (інвентарний номер, серійний номер або назву):";
    }
    
    public static function adminSearchResults($results) {
        if (empty($results)) {
            return "🔍 <b>Результати пошуку</b>\n\nНічого не знайдено.";
        }
        
        $message = "🔍 <b>Результати пошуку</b>\n\n";
        
        foreach ($results as $item) {
            $message .= "🖥️ <b>{$item['equipment_type']}</b>\n";
            $message .= "🏢 {$item['branch_name']} - каб. {$item['room_number']}\n";
            $message .= "🔧 {$item['brand']} {$item['model']}\n";
            $message .= "🔢 Інв: {$item['inventory_number']}";
            
            if (!empty($item['serial_number'])) {
                $message .= " | SN: {$item['serial_number']}";
            }
            
            $message .= "\n\n";
        }
        
        return $message;
    }
    
    public static function adminReports() {
        return "📊 <b>Звіти</b>\n\nОберіть тип звіту:";
    }
    
    public static function statusUpdated($newStatus) {
        $statusText = self::getStatusText($newStatus);
        return "✅ Статус заявки змінено на: <b>$statusText</b>";
    }
    
    // Уведомления для админов
    public static function notifyNewRepair($requestId, $branchName, $roomNumber, $description, $phone, $username, $userId) {
        $message = "🔧 <b>Нова заявка на ремонт № $requestId!</b>\n\n";
        $message .= "📍 Філія: <b>$branchName</b>\n";
        $message .= "🏢 Кабінет: <b>$roomNumber</b>\n";
        $message .= "📝 Проблема: " . htmlspecialchars($description) . "\n";
        $message .= "👤 Користувач: " . ($username ? "@$username" : "ID: $userId") . "\n";
        
        if (!empty($phone)) {
            $message .= "📞 Телефон: $phone\n";
        }
        
        $message .= "\n⏰ " . date('d.m.Y H:i');
        
        return $message;
    }
    
    public static function notifyNewCartridge($requestId, $branchName, $roomNumber, $printerInfo, $cartridgeType, $username, $userId) {
        $message = "🖨️ <b>Запит на заміну картриджа № $requestId!</b>\n\n";
        $message .= "📍 Філія: <b>$branchName</b>\n";
        $message .= "🏢 Кабінет: <b>$roomNumber</b>\n";
        $message .= "🖨️ Принтер: " . htmlspecialchars($printerInfo) . "\n";
        $message .= "🛒 Картридж: " . htmlspecialchars($cartridgeType) . "\n";
        $message .= "👤 Користувач: " . ($username ? "@$username" : "ID: $userId") . "\n";
        $message .= "\n⏰ " . date('d.m.Y H:i');
        
        return $message;
    }
    
    // Общие ошибки
    public static function branchesUnavailable() {
        return "На жаль, філії недоступні. Зверніться до адміністратора.";
    }
    
    public static function branchNotFound() {
        return "Помилка: філію не знайдено.";
    }
    
    public static function dataError() {
        return "❌ Помилка: не всі дані збережено. Спробуйте ще раз:";
    }
    
    public static function systemError() {
        return "❌ Виникла помилка. Спробуйте пізніше.";
    }
    
    // Вспомогательные методы
    private static function getStatusEmoji($status) {
        switch ($status) {
            case 'нова': return '🆕';
            case 'в_роботі': return '⚙️';
            case 'виконана': return '✅';
            default: return '❓';
        }
    }
    
    private static function getStatusText($status) {
        switch ($status) {
            case 'нова': return 'Нова';
            case 'в_роботі': return 'В роботі';
            case 'виконана': return 'Виконана';
            default: return 'Невідомий';
        }
    }
    
    private static function truncateText($text, $length) {
        return strlen($text) > $length ? substr($text, 0, $length) . '...' : $text;
    }
}