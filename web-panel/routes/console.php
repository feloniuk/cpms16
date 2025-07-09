<?php
// routes/console.php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Кастомные консольные команды для IT Support Panel
Artisan::command('support:clear-old-states', function () {
    $this->info('Очистка старых состояний пользователей...');
    
    $deleted = \App\Models\User::where('updated_at', '<', now()->subDays(7))->delete();
    
    $this->info("Удалено $deleted старых состояний");
})->purpose('Очистить старые состояния пользователей');

Artisan::command('support:stats', function () {
    $this->info('=== Статистика IT Support Panel ===');
    
    $repairs = \App\Models\RepairRequest::count();
    $repairsNew = \App\Models\RepairRequest::where('status', 'нова')->count();
    $cartridges = \App\Models\CartridgeReplacement::count();
    $branches = \App\Models\Branch::where('is_active', true)->count();
    
    $this->table(['Метрика', 'Значение'], [
        ['Всего заявок на ремонт', $repairs],
        ['Новых заявок', $repairsNew],
        ['Замен картриджей', $cartridges],
        ['Активных филиалов', $branches],
    ]);
})->purpose('Показать статистику системы');

Artisan::command('support:create-admin {telegram_id} {name}', function ($telegram_id, $name) {
    $this->info("Создание администратора...");
    
    // Проверяем существование
    $existing = \App\Models\Admin::where('telegram_id', $telegram_id)->first();
    if ($existing) {
        $this->error("Администратор с Telegram ID $telegram_id уже существует!");
        return;
    }
    
    // Создаем администратора
    $admin = \App\Models\Admin::create([
        'telegram_id' => $telegram_id,
        'name' => $name,
        'is_active' => true
    ]);
    
    $this->info("Администратор создан: ID {$admin->id}, Telegram ID: $telegram_id, Имя: $name");
})->purpose('Создать администратора');

Artisan::command('support:backup', function () {
    $this->info('Создание резервной копии...');
    
    $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
    
    // Команда mysqldump (настройте под свои данные)
    $command = sprintf(
        'mysqldump -u %s -p%s %s > %s',
        env('DB_USERNAME'),
        env('DB_PASSWORD'),
        env('DB_DATABASE'),
        storage_path('app/backups/' . $filename)
    );
    
    // Создаем директорию для бэкапов
    if (!is_dir(storage_path('app/backups'))) {
        mkdir(storage_path('app/backups'), 0755, true);
    }
    
    $this->info("Резервная копия создана: $filename");
})->purpose('Создать резервную копию базы данных');