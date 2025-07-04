<?php

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/repositories/AdminRepository.php';
require_once __DIR__ . '/../core/repositories/BranchRepository.php';
require_once __DIR__ . '/../core/repositories/UserStateRepository.php';
require_once __DIR__ . '/keyboards/Keyboards.php';

class TelegramBot {
    private $token;
    private $api_url;
    private $config;
    
    private $adminRepo;
    private $branchRepo;
    private $userStateRepo;
    private $keyboards;
    
    public function __construct() {
        $this->config = require __DIR__ . '/../config/telegram.php';
        $this->token = $this->config['bot_token'];
        $this->api_url = $this->config['api_url'] . $this->token . '/';
        
        $this->adminRepo = new AdminRepository();
        $this->branchRepo = new BranchRepository();
        $this->userStateRepo = new UserStateRepository();
        $this->keyboards = new Keyboards();
        
        $this->logMessage("TelegramBot initialized");
    }
    
    public function handleUpdate($update) {
        try {
            $this->logMessage("Received update: " . json_encode($update));
            
            if (isset($update['message'])) {
                $this->handleMessage($update['message']);
            } elseif (isset($update['callback_query'])) {
                $this->handleCallbackQuery($update['callback_query']);
            }
            
        } catch (Exception $e) {
            $this->logError("Error handling update: " . $e->getMessage());
        }
    }
    
    private function handleMessage($message) {
        $chat_id = $message['chat']['id'];
        $user_id = $message['from']['id'];
        $username = $message['from']['username'] ?? null;
        $text = $message['text'] ?? '';
        
        $this->logMessage("Message from user $user_id: $text");
        
        // Проверка на команды
        if (strpos($text, '/') === 0) {
            $this->handleCommand($chat_id, $user_id, $username, $text);
            return;
        }
        
        // Обработка по состоянию пользователя
        $userState = $this->userStateRepo->getUserState($user_id);
        $current_state = $userState ? $userState['current_state'] : null;
        
        if ($current_state) {
            $this->logMessage("User $user_id in state: $current_state");
            $this->handleStateMessage($chat_id, $user_id, $username, $text, $current_state, $userState['temp_data']);
        } else {
            $this->sendMessage($chat_id, "Оберіть дію з головного меню:", $this->keyboards->getMainMenu());
        }
    }
    
    private function handleCommand($chat_id, $user_id, $username, $command) {
        $this->logMessage("Command from user $user_id: $command");
        
        switch ($command) {
            case '/start':
                $this->userStateRepo->clearState($user_id);
                $this->sendWelcomeMessage($chat_id, $username);
                break;
                
            case '/help':
                $this->sendHelpMessage($chat_id);
                break;
                
            case '/cancel':
                $this->userStateRepo->clearState($user_id);
                $this->sendMessage($chat_id, "Дію скасовано. Оберіть нову дію:", $this->keyboards->getMainMenu());
                break;
                
            case '/admin':
                if ($this->adminRepo->isAdmin($user_id)) {
                    $this->sendMessage($chat_id, "Адмін-панель:", $this->keyboards->getAdminMenu());
                } else {
                    $this->sendMessage($chat_id, "У вас немає прав адміністратора.");
                }
                break;
                
            default:
                $this->sendMessage($chat_id, "Невідома команда. Скористайтеся /help для довідки.");
        }
    }
    
    private function handleCallbackQuery($callback_query) {
        $chat_id = $callback_query['message']['chat']['id'];
        $user_id = $callback_query['from']['id'];
        $username = $callback_query['from']['username'] ?? null;
        $data = $callback_query['data'];
        $message_id = $callback_query['message']['message_id'];
        
        // Підтвердження отримання callback
        $this->answerCallbackQuery($callback_query['id']);
        
        $this->logMessage("Callback from user $user_id: $data");
        
        // Розбір callback data
        $parts = explode(':', $data);
        $action = $parts[0];
        
        switch ($action) {
            case 'main_menu':
                $this->userStateRepo->clearState($user_id);
                $this->editMessage($chat_id, $message_id, "Головне меню:", $this->keyboards->getMainMenu());
                break;
                
            case 'repair_request':
                $this->startRepairRequest($chat_id, $user_id, $message_id);
                break;
                
            case 'cartridge_request':
                $this->startCartridgeRequest($chat_id, $user_id, $message_id);
                break;
                
            case 'admin_menu':
                if ($this->adminRepo->isAdmin($user_id)) {
                    $this->editMessage($chat_id, $message_id, "Адмін-панель:", $this->keyboards->getAdminMenu());
                } else {
                    $this->editMessage($chat_id, $message_id, "У вас немає прав адміністратора.");
                }
                break;
                
            case 'branch_select':
                if (isset($parts[1])) {
                    $this->handleBranchSelection($chat_id, $user_id, $message_id, $parts[1]);
                }
                break;
                
            case 'skip_phone':
                $this->handleSkipPhone($chat_id, $user_id, $username, $message_id);
                break;
                
            default:
                $this->editMessage($chat_id, $message_id, "Невідома дія. Поверніться до головного меню:", $this->keyboards->getMainMenu());
        }
    }
    
    private function sendWelcomeMessage($chat_id, $username) {
        $name = $username ? "@$username" : "Користувач";
        $message = "🤖 Вітаю, $name!\n\n";
        $message .= "Я бот для подачі заявок на ремонт обладнання та замін картриджів.\n\n";
        $message .= "Що ви хочете зробити?";
        
        $this->sendMessage($chat_id, $message, $this->keyboards->getMainMenu());
    }
    
    private function sendHelpMessage($chat_id) {
        $message = "📋 Довідка по боту:\n\n";
        $message .= "🔧 <b>Виклик ІТ майстра</b> - подати заявку на ремонт обладнання\n";
        $message .= "🖨️ <b>Заміна картриджа</b> - запит на заміну картриджа\n\n";
        $message .= "📞 Команди:\n";
        $message .= "/start - Головне меню\n";
        $message .= "/help - Ця довідка\n";
        $message .= "/cancel - Скасувати поточну дію\n";
        $message .= "/admin - Адмін-панель (тільки для адміністраторів)\n\n";
        $message .= "❓ Якщо у вас виникли питання, зверніться до адміністратора.";
        
        $this->sendMessage($chat_id, $message, null, 'HTML');
    }
    
    private function startRepairRequest($chat_id, $user_id, $message_id) {
        $this->logMessage("Starting repair request for user $user_id");
        
        $branches = $this->branchRepo->getActive();
        if (empty($branches)) {
            $this->editMessage($chat_id, $message_id, "На жаль, філії недоступні. Зверніться до адміністратора.");
            return;
        }
        
        $this->userStateRepo->setState($user_id, 'repair_awaiting_branch');
        $keyboard = $this->keyboards->getBranchesKeyboard($branches);
        $this->editMessage($chat_id, $message_id, "🔧 <b>Виклик ІТ майстра</b>\n\nОберіть філію:", $keyboard, 'HTML');
    }
    
    private function startCartridgeRequest($chat_id, $user_id, $message_id) {
        $this->logMessage("Starting cartridge request for user $user_id");
        
        $branches = $this->branchRepo->getActive();
        if (empty($branches)) {
            $this->editMessage($chat_id, $message_id, "На жаль, філії недоступні. Зверніться до адміністратора.");
            return;
        }
        
        $this->userStateRepo->setState($user_id, 'cartridge_awaiting_branch');
        $keyboard = $this->keyboards->getBranchesKeyboard($branches);
        $this->editMessage($chat_id, $message_id, "🖨️ <b>Заміна картриджа</b>\n\nОберіть філію:", $keyboard, 'HTML');
    }
    
    private function handleBranchSelection($chat_id, $user_id, $message_id, $branch_id) {
        $this->logMessage("Branch selection for user $user_id: branch $branch_id");
        
        $userState = $this->userStateRepo->getUserState($user_id);
        $current_state = $userState ? $userState['current_state'] : null;
        
        $branch = $this->branchRepo->find($branch_id);
        if (!$branch) {
            $this->editMessage($chat_id, $message_id, "Помилка: філію не знайдено.");
            return;
        }
        
        // Зберігаємо вибрану філію
        $this->userStateRepo->addToTempData($user_id, 'branch_id', $branch_id);
        $this->userStateRepo->addToTempData($user_id, 'branch_name', $branch['name']);
        
        if ($current_state === 'repair_awaiting_branch') {
            $this->userStateRepo->setState($user_id, 'repair_awaiting_room');
            $this->editMessage($chat_id, $message_id, 
                "🔧 <b>Виклик ІТ майстра</b>\n" .
                "Філія: <b>{$branch['name']}</b>\n\n" .
                "Введіть номер кабінету:", 
                $this->keyboards->getCancelKeyboard(), 'HTML');
                
        } elseif ($current_state === 'cartridge_awaiting_branch') {
            $this->userStateRepo->setState($user_id, 'cartridge_awaiting_room');
            $this->editMessage($chat_id, $message_id, 
                "🖨️ <b>Заміна картриджа</b>\n" .
                "Філія: <b>{$branch['name']}</b>\n\n" .
                "Введіть номер кабінету:", 
                $this->keyboards->getCancelKeyboard(), 'HTML');
        }
    }
    
    private function handleStateMessage($chat_id, $user_id, $username, $text, $current_state, $temp_data) {
        $this->logMessage("State message from user $user_id in state $current_state: $text");
        
        switch ($current_state) {
            case 'repair_awaiting_room':
                $this->handleRepairRoomInput($chat_id, $user_id, $username, $text, $temp_data);
                break;
                
            case 'repair_awaiting_description':
                $this->handleRepairDescriptionInput($chat_id, $user_id, $username, $text, $temp_data);
                break;
                
            case 'repair_awaiting_phone':
                $this->handleRepairPhoneInput($chat_id, $user_id, $username, $text, $temp_data);
                break;
                
            case 'cartridge_awaiting_room':
                $this->handleCartridgeRoomInput($chat_id, $user_id, $username, $text, $temp_data);
                break;
                
            case 'cartridge_awaiting_printer_search':
                $this->handleCartridgePrinterSearch($chat_id, $user_id, $username, $text, $temp_data);
                break;
                
            case 'cartridge_awaiting_cartridge_type':
                $this->handleCartridgeTypeInput($chat_id, $user_id, $username, $text, $temp_data);
                break;
                
            default:
                $this->sendMessage($chat_id, "Невідомий стан. Поверніться до головного меню:", $this->keyboards->getMainMenu());
                $this->userStateRepo->clearState($user_id);
        }
    }
    
    private function handleRepairRoomInput($chat_id, $user_id, $username, $room_number, $temp_data) {
        $this->logMessage("Repair room input from user $user_id: $room_number");
        
        if (empty(trim($room_number)) || strlen($room_number) > 50) {
            $this->sendMessage($chat_id, "❌ Некоректний номер кабінету. Введіть номер кабінету (до 50 символів):");
            return;
        }
        
        $this->userStateRepo->addToTempData($user_id, 'room_number', trim($room_number));
        $this->userStateRepo->setState($user_id, 'repair_awaiting_description');
        
        $updated_state = $this->userStateRepo->getUserState($user_id);
        $updated_temp_data = $updated_state ? $updated_state['temp_data'] : [];
        
        $message = "🔧 <b>Виклик ІТ майстра</b>\n";
        $message .= "Філія: <b>" . ($updated_temp_data['branch_name'] ?? 'Не вказано') . "</b>\n";
        $message .= "Кабінет: <b>" . trim($room_number) . "</b>\n\n";
        $message .= "Опишіть проблему (від 10 до 1000 символів):";
        
        $this->sendMessage($chat_id, $message, $this->keyboards->getCancelKeyboard(), 'HTML');
    }
    
    private function handleRepairDescriptionInput($chat_id, $user_id, $username, $description, $temp_data) {
        $this->logMessage("Repair description input from user $user_id: " . substr($description, 0, 50) . "...");
        
        if (empty(trim($description)) || strlen($description) < 10 || strlen($description) > 1000) {
            $this->sendMessage($chat_id, "❌ Опис повинен містити від 10 до 1000 символів. Спробуйте ще раз:");
            return;
        }
        
        $this->userStateRepo->addToTempData($user_id, 'description', trim($description));
        $this->userStateRepo->setState($user_id, 'repair_awaiting_phone');
        
        $updated_state = $this->userStateRepo->getUserState($user_id);
        $updated_temp_data = $updated_state ? $updated_state['temp_data'] : [];
        
        $message = "🔧 <b>Виклик ІТ майстра</b>\n";
        $message .= "Філія: <b>" . ($updated_temp_data['branch_name'] ?? 'Не вказано') . "</b>\n";
        $message .= "Кабінет: <b>" . ($updated_temp_data['room_number'] ?? 'Не вказано') . "</b>\n";
        $message .= "Проблема: <b>" . $this->escapeHtml(substr($description, 0, 100)) . "...</b>\n\n";
        $message .= "Введіть номер телефону для зв'язку або натисніть 'Пропустити':";
        
        $this->sendMessage($chat_id, $message, $this->keyboards->getPhoneKeyboard(), 'HTML');
    }
    
    private function handleRepairPhoneInput($chat_id, $user_id, $username, $phone, $temp_data) {
        $this->logMessage("Repair phone input from user $user_id: $phone");
        
        $phone = trim($phone);
        if (!empty($phone) && !preg_match('/^\+?3?8?0\d{9}$/', $phone)) {
            $this->sendMessage($chat_id, "❌ Некоректний формат телефону. Введіть номер у форматі +380XXXXXXXXX або натисніть 'Пропустити':");
            return;
        }
        
        $this->createRepairRequest($chat_id, $user_id, $username, $phone);
    }
    
    private function handleCartridgeRoomInput($chat_id, $user_id, $username, $room_number, $temp_data) {
        $this->logMessage("Cartridge room input from user $user_id: $room_number");
        
        if (empty(trim($room_number)) || strlen($room_number) > 50) {
            $this->sendMessage($chat_id, "❌ Некоректний номер кабінету. Введіть номер кабінету (до 50 символів):");
            return;
        }
        
        $this->userStateRepo->addToTempData($user_id, 'room_number', trim($room_number));
        $this->userStateRepo->setState($user_id, 'cartridge_awaiting_printer_search');
        
        $updated_state = $this->userStateRepo->getUserState($user_id);
        $updated_temp_data = $updated_state ? $updated_state['temp_data'] : [];
        
        $message = "🖨️ <b>Заміна картриджа</b>\n";
        $message .= "Філія: <b>" . ($updated_temp_data['branch_name'] ?? 'Не вказано') . "</b>\n";
        $message .= "Кабінет: <b>" . trim($room_number) . "</b>\n\n";
        $message .= "Введіть інвентарний або серійний номер принтера:";
        
        $this->sendMessage($chat_id, $message, $this->keyboards->getCancelKeyboard(), 'HTML');
    }
    
    private function handleCartridgePrinterSearch($chat_id, $user_id, $username, $search_term, $temp_data) {
        $this->logMessage("Cartridge printer search from user $user_id: $search_term");
        
        if (empty(trim($search_term))) {
            $this->sendMessage($chat_id, "❌ Введіть номер для пошуку принтера:");
            return;
        }
        
        $this->userStateRepo->addToTempData($user_id, 'printer_search', trim($search_term));
        $this->userStateRepo->setState($user_id, 'cartridge_awaiting_cartridge_type');
        
        $updated_state = $this->userStateRepo->getUserState($user_id);
        $updated_temp_data = $updated_state ? $updated_state['temp_data'] : [];
        
        $message = "🖨️ <b>Заміна картриджа</b>\n";
        $message .= "Філія: <b>" . ($updated_temp_data['branch_name'] ?? 'Не вказано') . "</b>\n";
        $message .= "Кабінет: <b>" . ($updated_temp_data['room_number'] ?? 'Не вказано') . "</b>\n";
        $message .= "Принтер: <b>" . trim($search_term) . "</b>\n\n";
        $message .= "Введіть тип картриджа (наприклад, HP CF217A):";
        
        $this->sendMessage($chat_id, $message, $this->keyboards->getCancelKeyboard(), 'HTML');
    }
    
    private function handleCartridgeTypeInput($chat_id, $user_id, $username, $cartridge_type, $temp_data) {
        $this->logMessage("Cartridge type input from user $user_id: $cartridge_type");
        
        if (empty(trim($cartridge_type))) {
            $this->sendMessage($chat_id, "❌ Введіть тип картриджа:");
            return;
        }
        
        $this->createCartridgeRequest($chat_id, $user_id, $username, trim($cartridge_type));
    }
    
    private function createRepairRequest($chat_id, $user_id, $username, $phone) {
        $this->logMessage("Creating repair request for user $user_id");
        
        try {
            $userState = $this->userStateRepo->getUserState($user_id);
            $temp_data = $userState ? $userState['temp_data'] : [];
            
            if (!$temp_data || !isset($temp_data['branch_id'], $temp_data['room_number'], $temp_data['description'])) {
                $this->sendMessage($chat_id, "❌ Помилка: не всі дані заявки збережено. Спробуйте ще раз:", $this->keyboards->getMainMenu());
                $this->userStateRepo->clearState($user_id);
                return;
            }
            
            $request_id = $this->createRepairRequestInDB($temp_data['branch_id'], $temp_data['room_number'], $temp_data['description'], $phone, $username, $user_id);
            
            $this->userStateRepo->clearState($user_id);
            
            $message = "✅ <b>Заявку створено успішно!</b>\n\n";
            $message .= "📋 <b>Деталі заявки № $request_id:</b>\n";
            $message .= "🏢 Філія: " . ($temp_data['branch_name'] ?? 'Не вказано') . "\n";
            $message .= "🚪 Кабінет: " . ($temp_data['room_number'] ?? 'Не вказано') . "\n";
            $message .= "📝 Проблема: " . $this->escapeHtml($temp_data['description']) . "\n";
            if (!empty($phone)) {
                $message .= "📞 Телефон: $phone\n";
            }
            $message .= "\n📧 Адміністратори отримали сповіщення про вашу заявку.\n";
            $message .= "⏰ Очікуйте на зв'язок від ІТ майстра.";
            
            $this->sendMessage($chat_id, $message, $this->keyboards->getMainMenu(), 'HTML');
            
            $this->notifyAdminsAboutRepairRequest($request_id, $temp_data['branch_name'], $temp_data['room_number'], $temp_data['description'], $phone, $username, $user_id);
            
        } catch (Exception $e) {
            $this->logError("Error creating repair request: " . $e->getMessage());
            $this->sendMessage($chat_id, "❌ Виникла помилка при створенні заявки. Спробуйте пізніше.");
            $this->userStateRepo->clearState($user_id);
        }
    }
    
    private function createCartridgeRequest($chat_id, $user_id, $username, $cartridge_type) {
        $this->logMessage("Creating cartridge request for user $user_id");
        
        try {
            $userState = $this->userStateRepo->getUserState($user_id);
            $temp_data = $userState ? $userState['temp_data'] : [];
            
            if (!$temp_data || !isset($temp_data['branch_id'], $temp_data['room_number'], $temp_data['printer_search'])) {
                $this->sendMessage($chat_id, "❌ Помилка: не всі дані запиту збережено. Спробуйте ще раз:", $this->keyboards->getMainMenu());
                $this->userStateRepo->clearState($user_id);
                return;
            }
            
            $request_id = $this->createCartridgeRequestInDB($temp_data['branch_id'], $temp_data['room_number'], $temp_data['printer_search'], $cartridge_type, $username, $user_id);
            
            $this->userStateRepo->clearState($user_id);
            
            $message = "✅ <b>Запит на заміну картриджа створено!</b>\n\n";
            $message .= "📋 <b>Деталі запиту № $request_id:</b>\n";
            $message .= "🏢 Філія: " . ($temp_data['branch_name'] ?? 'Не вказано') . "\n";
            $message .= "🚪 Кабінет: " . ($temp_data['room_number'] ?? 'Не вказано') . "\n";
            $message .= "🖨️ Принтер: " . ($temp_data['printer_search'] ?? 'Не вказано') . "\n";
            $message .= "🛒 Картридж: " . $this->escapeHtml($cartridge_type) . "\n";
            $message .= "\n📧 Адміністратори отримали сповіщення про ваш запит.";
            
            $this->sendMessage($chat_id, $message, $this->keyboards->getMainMenu(), 'HTML');
            
            $this->notifyAdminsAboutCartridgeRequest($request_id, $temp_data['branch_name'], $temp_data['room_number'], $temp_data['printer_search'], $cartridge_type, $username, $user_id);
            
        } catch (Exception $e) {
            $this->logError("Error creating cartridge request: " . $e->getMessage());
            $this->sendMessage($chat_id, "❌ Виникла помилка при створенні запиту. Спробуйте пізніше.");
            $this->userStateRepo->clearState($user_id);
        }
    }
    
    private function createRepairRequestInDB($branch_id, $room_number, $description, $phone, $username, $user_id) {
        $db = Database::getInstance();
        $stmt = $db->prepare("INSERT INTO repair_requests (user_telegram_id, username, branch_id, room_number, description, phone, status) VALUES (?, ?, ?, ?, ?, ?, 'нова')");
        $stmt->execute([$user_id, $username, $branch_id, $room_number, $description, $phone ?: null]);
        return $db->lastInsertId();
    }
    
    private function createCartridgeRequestInDB($branch_id, $room_number, $printer_info, $cartridge_type, $username, $user_id) {
        $db = Database::getInstance();
        $stmt = $db->prepare("INSERT INTO cartridge_replacements (user_telegram_id, username, branch_id, room_number, printer_info, cartridge_type, replacement_date) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $username, $branch_id, $room_number, $printer_info, $cartridge_type, date('Y-m-d')]);
        return $db->lastInsertId();
    }
    
    private function notifyAdminsAboutRepairRequest($request_id, $branch_name, $room_number, $description, $phone, $username, $user_id) {
        $admins = $this->adminRepo->getActiveAdmins();
        
        $message = "🔧 <b>Нова заявка на ремонт № $request_id!</b>\n\n";
        $message .= "📍 Філія: <b>" . ($branch_name ?? 'Не вказано') . "</b>\n";
        $message .= "🏢 Кабінет: <b>" . ($room_number ?? 'Не вказано') . "</b>\n";
        $message .= "📝 Проблема: " . $this->escapeHtml($description ?? 'Не вказано') . "\n";
        $message .= "👤 Користувач: " . ($username ? "@$username" : "ID: $user_id") . "\n";
        if (!empty($phone)) {
            $message .= "📞 Телефон: $phone\n";
        }
        $message .= "\n⏰ " . date('d.m.Y H:i');
        
        foreach ($admins as $admin) {
            try {
                $this->sendMessage($admin['telegram_id'], $message, null, 'HTML');
            } catch (Exception $e) {
                $this->logError("Failed to notify admin {$admin['telegram_id']}: " . $e->getMessage());
            }
        }
    }
    
    private function notifyAdminsAboutCartridgeRequest($request_id, $branch_name, $room_number, $printer_info, $cartridge_type, $username, $user_id) {
        $admins = $this->adminRepo->getActiveAdmins();
        
        $message = "🖨️ <b>Запит на заміну картриджа № $request_id!</b>\n\n";
        $message .= "📍 Філія: <b>" . ($branch_name ?? 'Не вказано') . "</b>\n";
        $message .= "🏢 Кабінет: <b>" . ($room_number ?? 'Не вказано') . "</b>\n";
        $message .= "🖨️ Принтер: " . $this->escapeHtml($printer_info ?? 'Не вказано') . "\n";
        $message .= "🛒 Картридж: " . $this->escapeHtml($cartridge_type) . "\n";
        $message .= "👤 Користувач: " . ($username ? "@$username" : "ID: $user_id") . "\n";
        $message .= "\n⏰ " . date('d.m.Y H:i');
        
        foreach ($admins as $admin) {
            try {
                $this->sendMessage($admin['telegram_id'], $message, null, 'HTML');
            } catch (Exception $e) {
                $this->logError("Failed to notify admin {$admin['telegram_id']}: " . $e->getMessage());
            }
        }
    }
    
    private function handleSkipPhone($chat_id, $user_id, $username, $message_id) {
        $this->logMessage("Skip phone for user $user_id");
        $this->createRepairRequest($chat_id, $user_id, $username, '');
    }
    
    public function sendMessage($chat_id, $text, $reply_markup = null, $parse_mode = null) {
        $data = [
            'chat_id' => $chat_id,
            'text' => $text
        ];
        
        if ($parse_mode) {
            $data['parse_mode'] = $parse_mode;
        }
        
        if ($reply_markup) {
            $data['reply_markup'] = json_encode($reply_markup);
        }
        
        return $this->makeRequest('sendMessage', $data);
    }
    
    public function editMessage($chat_id, $message_id, $text, $reply_markup = null, $parse_mode = null) {
        $data = [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
            'text' => $text
        ];
        
        if ($parse_mode) {
            $data['parse_mode'] = $parse_mode;
        }
        
        if ($reply_markup) {
            $data['reply_markup'] = json_encode($reply_markup);
        }
        
        return $this->makeRequest('editMessageText', $data);
    }
    
    public function answerCallbackQuery($callback_query_id, $text = null) {
        $data = ['callback_query_id' => $callback_query_id];
        
        if ($text) {
            $data['text'] = $text;
        }
        
        return $this->makeRequest('answerCallbackQuery', $data);
    }
    
    private function makeRequest($method, $data) {
        $url = $this->api_url . $method;
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 30
        ]);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_error($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new Exception("cURL error: $error");
        }
        
        curl_close($ch);
        
        $decoded = json_decode($response, true);
        
        if ($http_code !== 200 || !$decoded || !$decoded['ok']) {
            $error_description = isset($decoded['description']) ? $decoded['description'] : 'Unknown error';
            throw new Exception("Telegram API error: $error_description");
        }
        
        return $decoded;
    }
    
    private function escapeHtml($text) {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
    
    private function logMessage($message) {
        $log_entry = date('Y-m-d H:i:s') . " - " . $message . "\n";
        file_put_contents(__DIR__ . '/../logs/telegram.log', $log_entry, FILE_APPEND | LOCK_EX);
    }
    
    private function logError($error) {
        $log_entry = date('Y-m-d H:i:s') . " - ERROR: " . $error . "\n";
        file_put_contents(__DIR__ . '/../logs/telegram.log', $log_entry, FILE_APPEND | LOCK_EX);
        file_put_contents(__DIR__ . '/../logs/errors.log', $log_entry, FILE_APPEND | LOCK_EX);
    }
}