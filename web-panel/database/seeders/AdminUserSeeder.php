<?php
// database/seeders/AdminUserSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        // Создаем первого администратора из существующих данных Telegram бота
        $telegramAdmin = DB::table('admins')->where('is_active', 1)->first();
        
        if ($telegramAdmin) {
            // Проверяем, не создан ли уже пользователь
            $existingUser = User::where('telegram_id', $telegramAdmin->telegram_id)->first();
            
            if (!$existingUser) {
                User::create([
                    'name' => $telegramAdmin->name,
                    'email' => 'admin@' . parse_url(config('app.url'), PHP_URL_HOST),
                    'password' => Hash::make('password'), // Измените на безопасный пароль
                    'telegram_id' => $telegramAdmin->telegram_id,
                    'role' => 'admin',
                    'is_active' => true,
                    'email_verified_at' => now()
                ]);
                
                $this->command->info("Администратор создан: {$telegramAdmin->name}");
                $this->command->info("Email: admin@" . parse_url(config('app.url'), PHP_URL_HOST));
                $this->command->info("Пароль: password (измените после входа!)");
            } else {
                $this->command->info("Пользователь уже существует: {$telegramAdmin->name}");
            }
        } else {
            // Создаем дефолтного админа если нет данных из Telegram
            $defaultAdmin = User::firstOrCreate(
                ['email' => 'admin@' . parse_url(config('app.url'), PHP_URL_HOST)],
                [
                    'name' => 'Администратор',
                    'password' => Hash::make('password'),
                    'role' => 'admin',
                    'is_active' => true,
                    'email_verified_at' => now()
                ]
            );
            
            $this->command->info("Создан дефолтный администратор");
            $this->command->info("Email: admin@" . parse_url(config('app.url'), PHP_URL_HOST));
            $this->command->info("Пароль: password");
        }
        
        // Создаем тестового директора
        User::firstOrCreate(
            ['email' => 'director@' . parse_url(config('app.url'), PHP_URL_HOST)],
            [
                'name' => 'Директор поликлиники',
                'password' => Hash::make('password'),
                'role' => 'director',
                'is_active' => true,
                'email_verified_at' => now()
            ]
        );
        
        $this->command->info("Создан директор");
        $this->command->info("Email: director@" . parse_url(config('app.url'), PHP_URL_HOST));
        $this->command->info("Пароль: password");
    }
}