<?php

class Keyboards {
    
    public function getMainMenu() {
        return [
            'inline_keyboard' => [
                [
                    ['text' => '🔧 Виклик ІТ майстра', 'callback_data' => 'repair_request']
                ],
                [
                    ['text' => '🖨️ Заміна картриджа', 'callback_data' => 'cartridge_request']
                ],
                [
                    ['text' => '⚙️ Адмін-панель', 'callback_data' => 'admin_menu']
                ]
            ]
        ];
    }
    
    public function getAdminMenu() {
        return [
            'inline_keyboard' => [
                [
                    ['text' => '📋 Інвентаризація кабінету', 'callback_data' => 'admin_inventory']
                ],
                [
                    ['text' => '📊 Заявки на ремонт', 'callback_data' => 'admin_repairs'],
                    ['text' => '🖨️ Історія картриджів', 'callback_data' => 'admin_cartridges']
                ],
                [
                    ['text' => '🔍 Пошук по інвентарю', 'callback_data' => 'admin_search'],
                    ['text' => '📈 Звіти', 'callback_data' => 'admin_reports']
                ],
                [
                    ['text' => '🏢 Управління філіями', 'callback_data' => 'admin_branches'],
                    ['text' => '👥 Управління адмінами', 'callback_data' => 'admin_users']
                ],
                [
                    ['text' => '🏠 Головне меню', 'callback_data' => 'main_menu']
                ]
            ]
        ];
    }
    
    public function getBranchesKeyboard($branches) {
        $keyboard = [];
        
        foreach ($branches as $branch) {
            $keyboard[] = [
                ['text' => $branch['name'], 'callback_data' => "branch_select:{$branch['id']}"]
            ];
        }
        
        // Кнопка повернення
        $keyboard[] = [
            ['text' => '🏠 Головне меню', 'callback_data' => 'main_menu']
        ];
        
        return ['inline_keyboard' => $keyboard];
    }
    
    public function getCancelKeyboard() {
        return [
            'inline_keyboard' => [
                [
                    ['text' => '❌ Скасувати', 'callback_data' => 'main_menu']
                ]
            ]
        ];
    }
    
    public function getPhoneKeyboard() {
        return [
            'inline_keyboard' => [
                [
                    ['text' => '⏭️ Пропустити', 'callback_data' => 'skip_phone']
                ],
                [
                    ['text' => '❌ Скасувати', 'callback_data' => 'main_menu']
                ]
            ]
        ];
    }
    
    public function getConfirmKeyboard($confirm_action, $cancel_action = 'main_menu') {
        return [
            'inline_keyboard' => [
                [
                    ['text' => '✅ Підтвердити', 'callback_data' => $confirm_action],
                    ['text' => '❌ Скасувати', 'callback_data' => $cancel_action]
                ]
            ]
        ];
    }
    
    public function getBackKeyboard($back_action) {
        return [
            'inline_keyboard' => [
                [
                    ['text' => '◀️ Назад', 'callback_data' => $back_action]
                ],
                [
                    ['text' => '🏠 Головне меню', 'callback_data' => 'main_menu']
                ]
            ]
        ];
    }
    
    public function getPaginationKeyboard($current_page, $total_pages, $base_action, $additional_buttons = []) {
        $keyboard = [];
        
        // Додаткові кнопки зверху
        foreach ($additional_buttons as $button) {
            $keyboard[] = [$button];
        }
        
        // Кнопки пагінації
        if ($total_pages > 1) {
            $pagination_row = [];
            
            if ($current_page > 1) {
                $pagination_row[] = ['text' => '◀️', 'callback_data' => $base_action . ':' . ($current_page - 1)];
            }
            
            $pagination_row[] = ['text' => "$current_page / $total_pages", 'callback_data' => 'noop'];
            
            if ($current_page < $total_pages) {
                $pagination_row[] = ['text' => '▶️', 'callback_data' => $base_action . ':' . ($current_page + 1)];
            }
            
            $keyboard[] = $pagination_row;
        }
        
        // Кнопка повернення
        $keyboard[] = [
            ['text' => '🏠 Головне меню', 'callback_data' => 'main_menu']
        ];
        
        return ['inline_keyboard' => $keyboard];
    }
    
    public function getStatusKeyboard($repair_id) {
        return [
            'inline_keyboard' => [
                [
                    ['text' => '🔄 В роботі', 'callback_data' => "status_update:$repair_id:в_роботі"]
                ],
                [
                    ['text' => '✅ Виконана', 'callback_data' => "status_update:$repair_id:виконана"]
                ],
                [
                    ['text' => '🔙 Нова', 'callback_data' => "status_update:$repair_id:нова"]
                ],
                [
                    ['text' => '◀️ Назад до списку', 'callback_data' => 'admin_repairs']
                ]
            ]
        ];
    }
    
    public function getInventoryTemplatesKeyboard($templates) {
        $keyboard = [];
        
        foreach ($templates as $template) {
            $keyboard[] = [
                ['text' => $template['name'], 'callback_data' => "template_select:{$template['id']}"]
            ];
        }
        
        $keyboard[] = [
            ['text' => '➕ Створити новий', 'callback_data' => 'template_create_new']
        ];
        
        $keyboard[] = [
            ['text' => '◀️ Назад', 'callback_data' => 'admin_menu']
        ];
        
        return ['inline_keyboard' => $keyboard];
    }
    
    public function getSearchTypeKeyboard() {
        return [
            'inline_keyboard' => [
                [
                    ['text' => '🔢 По інвентарному номеру', 'callback_data' => 'search_type:inventory']
                ],
                [
                    ['text' => '📟 По серійному номеру', 'callback_data' => 'search_type:serial']
                ],
                [
                    ['text' => '🏢 По філії та кабінету', 'callback_data' => 'search_type:location']
                ],
                [
                    ['text' => '🖥️ По типу обладнання', 'callback_data' => 'search_type:equipment']
                ],
                [
                    ['text' => '◀️ Назад', 'callback_data' => 'admin_menu']
                ]
            ]
        ];
    }
    
    public function getEquipmentTypesKeyboard() {
        $types = [
            'Комп\'ютер' => 'computer',
            'Монітор' => 'monitor', 
            'Принтер' => 'printer',
            'Клавіатура' => 'keyboard',
            'Миша' => 'mouse',
            'Сканер' => 'scanner',
            'ІБП' => 'ups',
            'Роутер' => 'router',
            'Інше' => 'other'
        ];
        
        $keyboard = [];
        foreach ($types as $name => $code) {
            $keyboard[] = [
                ['text' => $name, 'callback_data' => "equipment_type:$code"]
            ];
        }
        
        $keyboard[] = [
            ['text' => '◀️ Назад', 'callback_data' => 'admin_search']
        ];
        
        return ['inline_keyboard' => $keyboard];
    }
    
    public function getReportsKeyboard() {
        return [
            'inline_keyboard' => [
                [
                    ['text' => '🏢 Звіт по філіях', 'callback_data' => 'report_branches']
                ],
                [
                    ['text' => '🚪 Звіт по кабінетах', 'callback_data' => 'report_rooms']
                ],
                [
                    ['text' => '🔧 Заявки за період', 'callback_data' => 'report_repairs']
                ],
                [
                    ['text' => '🖨️ Картриджі за період', 'callback_data' => 'report_cartridges']
                ],
                [
                    ['text' => '◀️ Назад', 'callback_data' => 'admin_menu']
                ]
            ]
        ];
    }
}