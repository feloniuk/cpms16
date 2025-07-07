<?php

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/repositories/AdminRepository.php';
require_once __DIR__ . '/../core/repositories/BranchRepository.php';
require_once __DIR__ . '/../core/repositories/UserStateRepository.php';
require_once __DIR__ . '/../core/repositories/RepairRepository.php';
require_once __DIR__ . '/../core/repositories/CartridgeRepository.php';
require_once __DIR__ . '/../core/repositories/InventoryRepository.php';
require_once __DIR__ . '/keyboards/Keyboards.php';
require_once __DIR__ . '/Messages.php';

class TelegramBot {
    private $token;
    private $api_url;
    private $config;
    
    private $adminRepo;
    private $branchRepo;
    private $userStateRepo;
    private $repairRepo;
    private $cartridgeRepo;
    private $inventoryRepo;
    private $keyboards;
    private $db;
    
    public function __construct() {
        $this->config = require __DIR__ . '/../config/telegram.php';
        $this->token = $this->config['bot_token'];
        $this->api_url = $this->config['api_url'] . $this->token . '/';
        
        $this->db = Database::getInstance();
        $this->adminRepo = new AdminRepository();
        $this->branchRepo = new BranchRepository();
        $this->userStateRepo = new UserStateRepository();
        $this->repairRepo = new RepairRepository();
        $this->cartridgeRepo = new CartridgeRepository();
        $this->inventoryRepo = new InventoryRepository();
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
        
        // Обработка команд
        if (strpos($text, '/') === 0) {
            $this->handleCommand($chat_id, $user_id, $username, $text);
            return;
        }
        
        // Обработка по состоянию пользователя
        $userState = $this->userStateRepo->getUserState($user_id);
        $current_state = $userState ? $userState['current_state'] : null;
        
        $this->logMessage("User $user_id current state: " . ($current_state ?? 'NULL'));
        
        if ($current_state) {
            $this->logMessage("Handling state message for user $user_id in state: $current_state");
            $this->handleStateMessage($chat_id, $user_id, $username, $text, $current_state, $userState['temp_data'] ?? []);
        } else {
            $this->sendMessage($chat_id, Messages::mainMenu(), $this->keyboards->getMainMenu());
        }
    }
    
    private function handleCommand($chat_id, $user_id, $username, $command) {
        $this->logMessage("Command from user $user_id: $command");
        
        switch ($command) {
            case '/start':
                $this->userStateRepo->clearState($user_id);
                $this->sendMessage($chat_id, Messages::welcome($username), $this->keyboards->getMainMenu());
                break;
                
            case '/help':
                $this->sendMessage($chat_id, Messages::help(), null, 'HTML');
                break;
                
            case '/cancel':
                $this->userStateRepo->clearState($user_id);
                $this->sendMessage($chat_id, Messages::actionCanceled(), $this->keyboards->getMainMenu());
                break;
                
            case '/admin':
                if ($this->adminRepo->isAdmin($user_id)) {
                    $this->sendMessage($chat_id, Messages::adminMenu(), $this->keyboards->getAdminMenu());
                } else {
                    $this->sendMessage($chat_id, Messages::noAccess());
                }
                break;
                
            default:
                $this->sendMessage($chat_id, Messages::unknownCommand());
        }
    }
    
    private function handleCallbackQuery($callback_query) {
        $chat_id = $callback_query['message']['chat']['id'];
        $user_id = $callback_query['from']['id'];
        $username = $callback_query['from']['username'] ?? null;
        $data = $callback_query['data'];
        $message_id = $callback_query['message']['message_id'];
        
        // Подтверждение получения callback
        $this->answerCallbackQuery($callback_query['id']);
        
        $this->logMessage("Callback from user $user_id: $data");
        
        // Разбор callback data
        $parts = explode(':', $data);
        $action = $parts[0];
        
        switch ($action) {
            case 'main_menu':
                $this->userStateRepo->clearState($user_id);
                $this->editMessage($chat_id, $message_id, Messages::mainMenu(), $this->keyboards->getMainMenu());
                break;
                
            case 'repair_request':
                $this->startRepairRequest($chat_id, $user_id, $message_id);
                break;
                
            case 'cartridge_request':
                $this->startCartridgeRequest($chat_id, $user_id, $message_id);
                break;
                
            case 'admin_menu':
                if ($this->adminRepo->isAdmin($user_id)) {
                    $this->editMessage($chat_id, $message_id, Messages::adminMenu(), $this->keyboards->getAdminMenu());
                } else {
                    $this->editMessage($chat_id, $message_id, Messages::noAccess());
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
                
            // Админские действия
            case 'admin_repairs':
                if ($this->adminRepo->isAdmin($user_id)) {
                    $this->showRepairsList($chat_id, $user_id, $message_id, 1);
                }
                break;
                
            case 'admin_cartridges':
                if ($this->adminRepo->isAdmin($user_id)) {
                    $this->showCartridgesList($chat_id, $user_id, $message_id, 1);
                }
                break;
                
            case 'admin_branches':
                if ($this->adminRepo->isAdmin($user_id)) {
                    $this->showBranchesList($chat_id, $user_id, $message_id);
                }
                break;
                
            case 'admin_inventory':
                if ($this->adminRepo->isAdmin($user_id)) {
                    $this->startInventory($chat_id, $user_id, $message_id);
                }
                break;
                
            case 'admin_search':
                if ($this->adminRepo->isAdmin($user_id)) {
                    $this->startSearch($chat_id, $user_id, $message_id);
                }
                break;
                
            case 'admin_reports':
                if ($this->adminRepo->isAdmin($user_id)) {
                    $this->showReports($chat_id, $user_id, $message_id);
                }
                break;
                
            case 'add_branch':
                if ($this->adminRepo->isAdmin($user_id)) {
                    $this->startAddBranch($chat_id, $user_id, $message_id);
                }
                break;
                
            case 'repair_details':
                if ($this->adminRepo->isAdmin($user_id) && isset($parts[1])) {
                    $this->showRepairDetails($chat_id, $user_id, $message_id, $parts[1]);
                }
                break;
                
            case 'status_update':
                if ($this->adminRepo->isAdmin($user_id) && isset($parts[1], $parts[2])) {
                    $this->updateRepairStatus($chat_id, $user_id, $message_id, $parts[1], $parts[2]);
                }
                break;
                
            case 'repairs_page':
                if ($this->adminRepo->isAdmin($user_id) && isset($parts[1])) {
                    $this->showRepairsList($chat_id, $user_id, $message_id, $parts[1]);
                }
                break;
                
            case 'cartridges_page':
                if ($this->adminRepo->isAdmin($user_id) && isset($parts[1])) {
                    $this->showCartridgesList($chat_id, $user_id, $message_id, $parts[1]);
                }
                break;
                
            default:
                $this->editMessage($chat_id, $message_id, Messages::unknownAction(), $this->keyboards->getMainMenu());
        }
    }
    
    private function handleStateMessage($chat_id, $user_id, $username, $text, $current_state, $temp_data) {
        $this->logMessage("State message from user $user_id in state $current_state: $text");
        $this->logMessage("Temp data: " . json_encode($temp_data));
        
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
                
            case 'admin_awaiting_branch_name':
                $this->handleAddBranchName($chat_id, $user_id, $text);
                break;
                
            case 'admin_awaiting_inventory_room':
                $this->logMessage("Handling inventory room input for user $user_id");
                $this->handleInventoryRoomInput($chat_id, $user_id, $text, $temp_data);
                break;
                
            case 'admin_awaiting_inventory_equipment':
                $this->logMessage("Handling inventory equipment input for user $user_id");
                $this->handleInventoryEquipmentInput($chat_id, $user_id, $text, $temp_data);
                break;
                
            case 'admin_awaiting_search_query':
                $this->handleSearchQuery($chat_id, $user_id, $text);
                break;
                
            default:
                $this->logMessage("Unknown state: $current_state for user $user_id");
                $this->sendMessage($chat_id, Messages::unknownState(), $this->keyboards->getMainMenu());
                $this->userStateRepo->clearState($user_id);
        }
    }
    
    // === МЕТОДЫ ДЛЯ ЗАЯВОК НА РЕМОНТ ===
    
    private function startRepairRequest($chat_id, $user_id, $message_id) {
        $this->logMessage("Starting repair request for user $user_id");
        
        $branches = $this->branchRepo->getActive();
        if (empty($branches)) {
            $this->editMessage($chat_id, $message_id, Messages::branchesUnavailable());
            return;
        }
        
        $this->userStateRepo->setState($user_id, 'repair_awaiting_branch');
        $keyboard = $this->keyboards->getBranchesKeyboard($branches);
        $this->editMessage($chat_id, $message_id, Messages::repairStart(), $keyboard, 'HTML');
    }
    
    private function handleBranchSelection($chat_id, $user_id, $message_id, $branch_id) {
        $this->logMessage("Branch selection for user $user_id: branch $branch_id");
        
        $userState = $this->userStateRepo->getUserState($user_id);
        $current_state = $userState ? $userState['current_state'] : null;
        
        $this->logMessage("Current state during branch selection: " . ($current_state ?? 'NULL'));
        
        $branch = $this->branchRepo->find($branch_id);
        if (!$branch) {
            $this->editMessage($chat_id, $message_id, Messages::branchNotFound());
            return;
        }
        
        // Сохраняем выбранную филию
        $this->userStateRepo->addToTempData($user_id, 'branch_id', $branch_id);
        $this->userStateRepo->addToTempData($user_id, 'branch_name', $branch['name']);
        
        if ($current_state === 'repair_awaiting_branch') {
            $this->userStateRepo->setState($user_id, 'repair_awaiting_room');
            $this->editMessage($chat_id, $message_id, 
                Messages::repairBranchSelected($branch['name']), 
                $this->keyboards->getCancelKeyboard(), 'HTML');
                
        } elseif ($current_state === 'cartridge_awaiting_branch') {
            $this->userStateRepo->setState($user_id, 'cartridge_awaiting_room');
            $this->editMessage($chat_id, $message_id, 
                Messages::cartridgeBranchSelected($branch['name']), 
                $this->keyboards->getCancelKeyboard(), 'HTML');
                
        } elseif ($current_state === 'admin_inventory_awaiting_branch') {
            $this->logMessage("Setting state to admin_awaiting_inventory_room for user $user_id");
            $this->userStateRepo->setState($user_id, 'admin_awaiting_inventory_room');
            $this->editMessage($chat_id, $message_id, 
                Messages::adminInventoryRoomPrompt($branch['name']), 
                $this->keyboards->getCancelKeyboard(), 'HTML');
        } else {
            $this->logMessage("Unexpected state during branch selection: $current_state");
        }
    }
    
    private function handleRepairRoomInput($chat_id, $user_id, $username, $room_number, $temp_data) {
        if (empty(trim($room_number)) || strlen($room_number) > 50) {
            $this->sendMessage($chat_id, Messages::repairErrorInvalidRoom());
            return;
        }
        
        $this->userStateRepo->addToTempData($user_id, 'room_number', trim($room_number));
        $this->userStateRepo->setState($user_id, 'repair_awaiting_description');
        
        $updated_state = $this->userStateRepo->getUserState($user_id);
        $updated_temp_data = $updated_state ? $updated_state['temp_data'] : [];
        
        $this->sendMessage($chat_id, 
            Messages::repairRoomSelected(
                $updated_temp_data['branch_name'] ?? 'Не вказано', 
                trim($room_number)
            ), 
            $this->keyboards->getCancelKeyboard(), 'HTML');
    }
    
    private function handleRepairDescriptionInput($chat_id, $user_id, $username, $description, $temp_data) {
        if (empty(trim($description)) || strlen($description) < 10 || strlen($description) > 1000) {
            $this->sendMessage($chat_id, Messages::repairErrorInvalidDescription());
            return;
        }
        
        $this->userStateRepo->addToTempData($user_id, 'description', trim($description));
        $this->userStateRepo->setState($user_id, 'repair_awaiting_phone');
        
        $updated_state = $this->userStateRepo->getUserState($user_id);
        $updated_temp_data = $updated_state ? $updated_state['temp_data'] : [];
        
        $this->sendMessage($chat_id, 
            Messages::repairDescriptionEntered(
                $updated_temp_data['branch_name'] ?? 'Не вказано',
                $updated_temp_data['room_number'] ?? 'Не вказано',
                $description
            ), 
            $this->keyboards->getPhoneKeyboard(), 'HTML');
    }
    
    private function handleRepairPhoneInput($chat_id, $user_id, $username, $phone, $temp_data) {
        $phone = trim($phone);
        if (!empty($phone) && !preg_match('/^\+?3?8?0\d{9}$/', $phone)) {
            $this->sendMessage($chat_id, Messages::repairErrorInvalidPhone());
            return;
        }
        
        $this->createRepairRequest($chat_id, $user_id, $username, $phone);
    }
    
    private function handleSkipPhone($chat_id, $user_id, $username, $message_id) {
        $this->createRepairRequest($chat_id, $user_id, $username, '');
    }
    
    private function createRepairRequest($chat_id, $user_id, $username, $phone) {
        try {
            $userState = $this->userStateRepo->getUserState($user_id);
            $temp_data = $userState ? $userState['temp_data'] : [];
            
            if (!$temp_data || !isset($temp_data['branch_id'], $temp_data['room_number'], $temp_data['description'])) {
                $this->sendMessage($chat_id, Messages::dataError(), $this->keyboards->getMainMenu());
                $this->userStateRepo->clearState($user_id);
                return;
            }
            
            $request_id = $this->createRepairRequestInDB($temp_data['branch_id'], $temp_data['room_number'], $temp_data['description'], $phone, $username, $user_id);
            
            $this->userStateRepo->clearState($user_id);
            
            $this->sendMessage($chat_id, 
                Messages::repairSuccess(
                    $request_id,
                    $temp_data['branch_name'] ?? 'Не вказано',
                    $temp_data['room_number'] ?? 'Не вказано',
                    $temp_data['description'],
                    $phone
                ), 
                $this->keyboards->getMainMenu(), 'HTML');
            
            $this->notifyAdminsAboutRepairRequest($request_id, $temp_data['branch_name'], $temp_data['room_number'], $temp_data['description'], $phone, $username, $user_id);
            
        } catch (Exception $e) {
            $this->logError("Error creating repair request: " . $e->getMessage());
            $this->sendMessage($chat_id, Messages::systemError());
            $this->userStateRepo->clearState($user_id);
        }
    }
    
    // === МЕТОДЫ ДЛЯ ЗАМЕНЫ КАРТРИДЖЕЙ ===
    
    private function startCartridgeRequest($chat_id, $user_id, $message_id) {
        $branches = $this->branchRepo->getActive();
        if (empty($branches)) {
            $this->editMessage($chat_id, $message_id, Messages::branchesUnavailable());
            return;
        }
        
        $this->userStateRepo->setState($user_id, 'cartridge_awaiting_branch');
        $keyboard = $this->keyboards->getBranchesKeyboard($branches);
        $this->editMessage($chat_id, $message_id, Messages::cartridgeStart(), $keyboard, 'HTML');
    }
    
    private function handleCartridgeRoomInput($chat_id, $user_id, $username, $room_number, $temp_data) {
        if (empty(trim($room_number)) || strlen($room_number) > 50) {
            $this->sendMessage($chat_id, Messages::cartridgeErrorInvalidRoom());
            return;
        }
        
        $this->userStateRepo->addToTempData($user_id, 'room_number', trim($room_number));
        $this->userStateRepo->setState($user_id, 'cartridge_awaiting_printer_search');
        
        $updated_state = $this->userStateRepo->getUserState($user_id);
        $updated_temp_data = $updated_state ? $updated_state['temp_data'] : [];
        
        $this->sendMessage($chat_id, 
            Messages::cartridgeRoomSelected(
                $updated_temp_data['branch_name'] ?? 'Не вказано',
                trim($room_number)
            ), 
            $this->keyboards->getCancelKeyboard(), 'HTML');
    }
    
    private function handleCartridgePrinterSearch($chat_id, $user_id, $username, $search_term, $temp_data) {
        if (empty(trim($search_term))) {
            $this->sendMessage($chat_id, Messages::cartridgeErrorInvalidPrinter());
            return;
        }
        
        $this->userStateRepo->addToTempData($user_id, 'printer_search', trim($search_term));
        $this->userStateRepo->setState($user_id, 'cartridge_awaiting_cartridge_type');
        
        $updated_state = $this->userStateRepo->getUserState($user_id);
        $updated_temp_data = $updated_state ? $updated_state['temp_data'] : [];
        
        $this->sendMessage($chat_id, 
            Messages::cartridgePrinterEntered(
                $updated_temp_data['branch_name'] ?? 'Не вказано',
                $updated_temp_data['room_number'] ?? 'Не вказано',
                trim($search_term)
            ), 
            $this->keyboards->getCancelKeyboard(), 'HTML');
    }
    
    private function handleCartridgeTypeInput($chat_id, $user_id, $username, $cartridge_type, $temp_data) {
        if (empty(trim($cartridge_type))) {
            $this->sendMessage($chat_id, Messages::cartridgeErrorInvalidType());
            return;
        }
        
        $this->createCartridgeRequest($chat_id, $user_id, $username, trim($cartridge_type));
    }
    
    private function createCartridgeRequest($chat_id, $user_id, $username, $cartridge_type) {
        try {
            $userState = $this->userStateRepo->getUserState($user_id);
            $temp_data = $userState ? $userState['temp_data'] : [];
            
            if (!$temp_data || !isset($temp_data['branch_id'], $temp_data['room_number'], $temp_data['printer_search'])) {
                $this->sendMessage($chat_id, Messages::dataError(), $this->keyboards->getMainMenu());
                $this->userStateRepo->clearState($user_id);
                return;
            }
            
            $request_id = $this->createCartridgeRequestInDB($temp_data['branch_id'], $temp_data['room_number'], $temp_data['printer_search'], $cartridge_type, $username, $user_id);
            
            $this->userStateRepo->clearState($user_id);
            
            $this->sendMessage($chat_id, 
                Messages::cartridgeSuccess(
                    $request_id,
                    $temp_data['branch_name'] ?? 'Не вказано',
                    $temp_data['room_number'] ?? 'Не вказано',
                    $temp_data['printer_search'],
                    $cartridge_type
                ), 
                $this->keyboards->getMainMenu(), 'HTML');
            
            $this->notifyAdminsAboutCartridgeRequest($request_id, $temp_data['branch_name'], $temp_data['room_number'], $temp_data['printer_search'], $cartridge_type, $username, $user_id);
            
        } catch (Exception $e) {
            $this->logError("Error creating cartridge request: " . $e->getMessage());
            $this->sendMessage($chat_id, Messages::systemError());
            $this->userStateRepo->clearState($user_id);
        }
    }
    
    // === АДМИНСКИЕ МЕТОДЫ ===
    
    private function showRepairsList($chat_id, $user_id, $message_id, $page = 1) {
        try {
            $page = max(1, intval($page));
            $limit = 5;
            $offset = ($page - 1) * $limit;
            
            // Получаем заявки с информацией о филиалах
            $repairs = $this->repairRepo->getWithBranches($limit, $offset);
            
            // Получаем общее количество
            $total = $this->repairRepo->count();
            $total_pages = ceil($total / $limit);
            
            $keyboard = $this->keyboards->getRepairsListKeyboard($repairs, $page, $total_pages);
            $this->editMessage($chat_id, $message_id, Messages::adminRepairsList($repairs, $page, $total_pages), $keyboard, 'HTML');
            
        } catch (Exception $e) {
            $this->logError("Error showing repairs list: " . $e->getMessage());
            $this->editMessage($chat_id, $message_id, "Помилка завантаження заявок.", $this->keyboards->getBackKeyboard('admin_menu'));
        }
    }
    
    private function showRepairDetails($chat_id, $user_id, $message_id, $repair_id) {
        try {
            $repair = $this->repairRepo->getWithBranchInfo($repair_id);
            
            if (!$repair) {
                $this->editMessage($chat_id, $message_id, "Заявку не знайдено.", $this->keyboards->getBackKeyboard('admin_repairs'));
                return;
            }
            
            $keyboard = $this->keyboards->getStatusKeyboard($repair_id);
            $this->editMessage($chat_id, $message_id, Messages::adminRepairDetails($repair), $keyboard, 'HTML');
            
        } catch (Exception $e) {
            $this->logError("Error showing repair details: " . $e->getMessage());
            $this->editMessage($chat_id, $message_id, "Помилка завантаження деталей.", $this->keyboards->getBackKeyboard('admin_repairs'));
        }
    }
    
    private function updateRepairStatus($chat_id, $user_id, $message_id, $repair_id, $new_status) {
        try {
            $result = $this->repairRepo->updateStatus($repair_id, $new_status);
            
            if ($result) {
                $this->sendMessage($chat_id, Messages::statusUpdated($new_status), null, 'HTML');
                $this->showRepairDetails($chat_id, $user_id, $message_id, $repair_id);
            } else {
                $this->sendMessage($chat_id, "Помилка оновлення статусу.");
            }
            
        } catch (Exception $e) {
            $this->logError("Error updating repair status: " . $e->getMessage());
            $this->sendMessage($chat_id, "Помилка оновлення статусу.");
        }
    }
    
    private function showCartridgesList($chat_id, $user_id, $message_id, $page = 1) {
        try {
            $page = max(1, intval($page));
            $limit = 5;
            $offset = ($page - 1) * $limit;
            
            $cartridges = $this->cartridgeRepo->getWithBranches($limit, $offset);
            
            $total = $this->cartridgeRepo->count();
            $total_pages = ceil($total / $limit);
            
            $keyboard = $this->keyboards->getCartridgesListKeyboard($page, $total_pages);
            $this->editMessage($chat_id, $message_id, Messages::adminCartridgesList($cartridges, $page, $total_pages), $keyboard, 'HTML');
            
        } catch (Exception $e) {
            $this->logError("Error showing cartridges list: " . $e->getMessage());
            $this->editMessage($chat_id, $message_id, "Помилка завантаження історії.", $this->keyboards->getBackKeyboard('admin_menu'));
        }
    }
    
    private function showBranchesList($chat_id, $user_id, $message_id) {
        try {
            $branches = $this->branchRepo->getAll();
            $keyboard = $this->keyboards->getBranchesManagementKeyboard();
            $this->editMessage($chat_id, $message_id, Messages::adminBranchesList($branches), $keyboard, 'HTML');
            
        } catch (Exception $e) {
            $this->logError("Error showing branches list: " . $e->getMessage());
            $this->editMessage($chat_id, $message_id, "Помилка завантаження філій.", $this->keyboards->getBackKeyboard('admin_menu'));
        }
    }
    
    private function startAddBranch($chat_id, $user_id, $message_id) {
        $this->userStateRepo->setState($user_id, 'admin_awaiting_branch_name');
        $this->editMessage($chat_id, $message_id, Messages::adminAddBranch(), $this->keyboards->getCancelKeyboard(), 'HTML');
    }
    
    private function handleAddBranchName($chat_id, $user_id, $branch_name) {
        try {
            $branch_name = trim($branch_name);
            
            if (empty($branch_name) || strlen($branch_name) < 2 || strlen($branch_name) > 255) {
                $this->sendMessage($chat_id, Messages::adminBranchInvalidName());
                return;
            }
            
            if ($this->branchRepo->exists($branch_name)) {
                $this->sendMessage($chat_id, Messages::adminBranchExists());
                return;
            }
            
            $branch_id = $this->branchRepo->create([
                'name' => $branch_name,
                'is_active' => 1
            ]);
            
            $this->userStateRepo->clearState($user_id);
            $this->sendMessage($chat_id, Messages::adminBranchAdded($branch_name), $this->keyboards->getMainMenu());
            
        } catch (Exception $e) {
            $this->logError("Error adding branch: " . $e->getMessage());
            $this->sendMessage($chat_id, Messages::systemError());
        }
    }
    
    private function startInventory($chat_id, $user_id, $message_id) {
        try {
            $this->logMessage("Starting inventory for user $user_id");
            
            $branches = $this->branchRepo->getActive();
            if (empty($branches)) {
                $this->editMessage($chat_id, $message_id, Messages::branchesUnavailable());
                return;
            }
            
            $this->logMessage("Setting state admin_inventory_awaiting_branch for user $user_id");
            $this->userStateRepo->setState($user_id, 'admin_inventory_awaiting_branch');
            
            // Проверяем что состояние установилось
            $check_state = $this->userStateRepo->getUserState($user_id);
            $this->logMessage("State after setting: " . ($check_state['current_state'] ?? 'NULL'));
            
            $keyboard = $this->keyboards->getBranchesKeyboard($branches);
            $this->editMessage($chat_id, $message_id, Messages::adminInventoryStart(), $keyboard, 'HTML');
            
        } catch (Exception $e) {
            $this->logError("Error starting inventory: " . $e->getMessage());
            $this->editMessage($chat_id, $message_id, Messages::systemError(), $this->keyboards->getBackKeyboard('admin_menu'));
        }
    }
    
    private function handleInventoryRoomInput($chat_id, $user_id, $room_number, $temp_data) {
        try {
            $this->logMessage("Handling inventory room input for user $user_id: $room_number");
            $this->logMessage("Temp data received: " . json_encode($temp_data));
            
            if (empty(trim($room_number)) || strlen($room_number) > 50) {
                $this->sendMessage($chat_id, "❌ Некоректний номер кабінету. Введіть номер кабінету (до 50 символів):");
                return;
            }
            
            if (!isset($temp_data['branch_id']) || !isset($temp_data['branch_name'])) {
                $this->logError("Missing branch data in temp_data for user $user_id");
                $this->sendMessage($chat_id, "❌ Помилка: дані про філію втрачено. Спробуйте ще раз.");
                $this->userStateRepo->clearState($user_id);
                return;
            }
            
            $this->userStateRepo->addToTempData($user_id, 'room_number', trim($room_number));
            $this->userStateRepo->setState($user_id, 'admin_awaiting_inventory_equipment');
            
            $updated_state = $this->userStateRepo->getUserState($user_id);
            $updated_temp_data = $updated_state ? $updated_state['temp_data'] : [];
            
            $this->logMessage("Updated temp data: " . json_encode($updated_temp_data));
            
            $this->sendMessage($chat_id, 
                Messages::adminInventoryEquipmentPrompt(
                    $updated_temp_data['branch_name'] ?? 'Не вказано',
                    trim($room_number)
                ), 
                $this->keyboards->getCancelKeyboard(), 'HTML');
                
        } catch (Exception $e) {
            $this->logError("Error handling inventory room input: " . $e->getMessage());
            $this->sendMessage($chat_id, Messages::systemError());
            $this->userStateRepo->clearState($user_id);
        }
    }
    
    private function handleInventoryEquipmentInput($chat_id, $user_id, $equipment_data, $temp_data) {
        try {
            $this->logMessage("Handling inventory equipment input for user $user_id: $equipment_data");
            $this->logMessage("Temp data: " . json_encode($temp_data));
            
            $parts = array_map('trim', explode(',', $equipment_data));
            
            if (count($parts) < 5) {
                $this->sendMessage($chat_id, Messages::adminInventoryError(), null, 'HTML');
                return;
            }
            
            $equipment_type = $parts[0];
            $brand = $parts[1];
            $model = $parts[2];
            $serial_number = $parts[3];
            $inventory_number = $parts[4];
            
            if (empty($equipment_type) || empty($inventory_number)) {
                $this->sendMessage($chat_id, Messages::adminInventoryError(), null, 'HTML');
                return;
            }
            
            if (!isset($temp_data['branch_id']) || !isset($temp_data['room_number'])) {
                $this->logError("Missing required data in temp_data for user $user_id");
                $this->sendMessage($chat_id, "❌ Помилка: дані втрачено. Спробуйте ще раз.");
                $this->userStateRepo->clearState($user_id);
                return;
            }
            
            // Проверяем уникальность инвентарного номера
            if ($this->inventoryRepo->inventoryNumberExists($inventory_number)) {
                $this->sendMessage($chat_id, "❌ Інвентарний номер '$inventory_number' вже існує!");
                return;
            }
            
            $id = $this->inventoryRepo->addEquipment(
                $user_id,
                $temp_data['branch_id'],
                $temp_data['room_number'],
                $equipment_type,
                $brand,
                $model,
                $serial_number ?: null,
                $inventory_number
            );
            
            $this->logMessage("Equipment added with ID: $id");
            
            $this->userStateRepo->clearState($user_id);
            $this->sendMessage($chat_id, Messages::adminInventoryAdded($equipment_type, $inventory_number), $this->keyboards->getMainMenu());
            
        } catch (Exception $e) {
            $this->logError("Error adding inventory equipment: " . $e->getMessage());
            $this->sendMessage($chat_id, Messages::systemError());
            $this->userStateRepo->clearState($user_id);
        }
    }
    
    private function startSearch($chat_id, $user_id, $message_id) {
        $this->userStateRepo->setState($user_id, 'admin_awaiting_search_query');
        $this->editMessage($chat_id, $message_id, Messages::adminSearchStart(), $this->keyboards->getCancelKeyboard(), 'HTML');
    }
    
    private function handleSearchQuery($chat_id, $user_id, $query) {
        try {
            $query = trim($query);
            
            if (empty($query)) {
                $this->sendMessage($chat_id, Messages::adminSearchStart());
                return;
            }
            
            $results = $this->inventoryRepo->searchByQuery($query);
            
            $this->userStateRepo->clearState($user_id);
            $this->sendMessage($chat_id, Messages::adminSearchResults($results), $this->keyboards->getMainMenu(), 'HTML');
            
        } catch (Exception $e) {
            $this->logError("Error searching inventory: " . $e->getMessage());
            $this->sendMessage($chat_id, Messages::systemError());
        }
    }
    
    private function showReports($chat_id, $user_id, $message_id) {
        $this->editMessage($chat_id, $message_id, Messages::adminReports(), $this->keyboards->getReportsKeyboard(), 'HTML');
    }
    
    // === МЕТОДЫ РАБОТЫ С БД ===
    
    private function createRepairRequestInDB($branch_id, $room_number, $description, $phone, $username, $user_id) {
        return $this->repairRepo->createRequest($user_id, $username, $branch_id, $room_number, $description, $phone ?: null);
    }
    
    private function createCartridgeRequestInDB($branch_id, $room_number, $printer_info, $cartridge_type, $username, $user_id) {
        return $this->cartridgeRepo->createReplacement($user_id, $username, $branch_id, $room_number, null, $printer_info, $cartridge_type);
    }
    
    // === УВЕДОМЛЕНИЯ ===
    
    private function notifyAdminsAboutRepairRequest($request_id, $branch_name, $room_number, $description, $phone, $username, $user_id) {
        $admins = $this->adminRepo->getActiveAdmins();
        $message = Messages::notifyNewRepair($request_id, $branch_name, $room_number, $description, $phone, $username, $user_id);
        
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
        $message = Messages::notifyNewCartridge($request_id, $branch_name, $room_number, $printer_info, $cartridge_type, $username, $user_id);
        
        foreach ($admins as $admin) {
            try {
                $this->sendMessage($admin['telegram_id'], $message, null, 'HTML');
            } catch (Exception $e) {
                $this->logError("Failed to notify admin {$admin['telegram_id']}: " . $e->getMessage());
            }
        }
    }
    
    // === МЕТОДЫ TELEGRAM API ===
    
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