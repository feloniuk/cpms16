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
                    ['text' => '🏢 Управління філіями', 'callback_data' => 'admin_branches']
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
        
        // Кнопка возврата
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
    
    public function getRepairsListKeyboard($repairs, $page, $total_pages) {
        $keyboard = [];
        
        // Кнопки заявок
        foreach ($repairs as $repair) {
            $status = $this->getStatusEmoji($repair['status']);
            $text = "#{$repair['id']} $status {$repair['branch_name']} - {$repair['room_number']}";
            // Обрезаем текст если он слишком длинный
            if (strlen($text) > 40) {
                $text = substr($text, 0, 37) . '...';
            }
            $keyboard[] = [
                ['text' => $text, 'callback_data' => "repair_details:{$repair['id']}"]
            ];
        }
        
        // Пагинация
        if ($total_pages > 1) {
            $pagination_row = [];
            
            if ($page > 1) {
                $pagination_row[] = ['text' => '◀️', 'callback_data' => 'repairs_page:' . ($page - 1)];
            }
            
            $pagination_row[] = ['text' => "$page / $total_pages", 'callback_data' => 'noop'];
            
            if ($page < $total_pages) {
                $pagination_row[] = ['text' => '▶️', 'callback_data' => 'repairs_page:' . ($page + 1)];
            }
            
            $keyboard[] = $pagination_row;
        }
        
        // Кнопки навигации
        $keyboard[] = [
            ['text' => '🔄 Оновити', 'callback_data' => 'admin_repairs'],
            ['text' => '◀️ Адмін-панель', 'callback_data' => 'admin_menu']
        ];
        
        return ['inline_keyboard' => $keyboard];
    }
    
    public function getCartridgesListKeyboard($page, $total_pages) {
        $keyboard = [];
        
        // Пагинация
        if ($total_pages > 1) {
            $pagination_row = [];
            
            if ($page > 1) {
                $pagination_row[] = ['text' => '◀️', 'callback_data' => 'cartridges_page:' . ($page - 1)];
            }
            
            $pagination_row[] = ['text' => "$page / $total_pages", 'callback_data' => 'noop'];
            
            if ($page < $total_pages) {
                $pagination_row[] = ['text' => '▶️', 'callback_data' => 'cartridges_page:' . ($page + 1)];
            }
            
            $keyboard[] = $pagination_row;
        }
        
        // Кнопки навигации
        $keyboard[] = [
            ['text' => '🔄 Оновити', 'callback_data' => 'admin_cartridges'],
            ['text' => '◀️ Адмін-панель', 'callback_data' => 'admin_menu']
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
                    ['text' => '🔄 Оновити', 'callback_data' => "repair_details:$repair_id"],
                    ['text' => '◀️ До списку', 'callback_data' => 'admin_repairs']
                ]
            ]
        ];
    }
    
    public function getBranchesManagementKeyboard() {
        return [
            'inline_keyboard' => [
                [
                    ['text' => '➕ Додати філію', 'callback_data' => 'add_branch']
                ],
                [
                    ['text' => '🔄 Оновити список', 'callback_data' => 'admin_branches']
                ],
                [
                    ['text' => '◀️ Адмін-панель', 'callback_data' => 'admin_menu']
                ]
            ]
        ];
    }
    
    public function getReportsKeyboard() {
        return [
            'inline_keyboard' => [
                [
                    ['text' => '🔄 Оновити звіти', 'callback_data' => 'admin_reports']
                ],
                [
                    ['text' => '◀️ Адмін-панель', 'callback_data' => 'admin_menu']
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
    
    private function getStatusEmoji($status) {
        switch ($status) {
            case 'нова': return '🆕';
            case 'в_роботі': return '⚙️';
            case 'виконана': return '✅';
            default: return '❓';
        }
    }
}