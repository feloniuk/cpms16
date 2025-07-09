<?php
// routes/web.php
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RepairRequestController;
use App\Http\Controllers\CartridgeReplacementController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ReportsController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});

// Аутентификация (Laravel Breeze)
require __DIR__.'/auth.php';

// Защищенные маршруты
Route::middleware(['auth', 'verified'])->group(function () {
    
    // Главная страница - дашборд
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Профиль пользователя
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Заявки на ремонт
    Route::resource('repairs', RepairRequestController::class)->only(['index', 'show', 'update']);
    
    // Замены картриджей
    Route::resource('cartridges', CartridgeReplacementController::class)->only(['index', 'show']);
    
    // Отчеты
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportsController::class, 'index'])->name('index');
        Route::get('/repairs', [ReportsController::class, 'repairs'])->name('repairs');
        Route::get('/cartridges', [ReportsController::class, 'cartridges'])->name('cartridges');
        Route::get('/inventory', [ReportsController::class, 'inventory'])->name('inventory');
        Route::get('/export', [ReportsController::class, 'export'])->name('export');
    });
    
    // Только для администраторов
    Route::middleware('role:admin')->group(function () {
        // Филиалы
        Route::resource('branches', BranchController::class);
        
        // Инвентарь
        Route::resource('inventory', InventoryController::class);
        Route::get('/inventory-export', [InventoryController::class, 'export'])->name('inventory.export');
    });

    // API маршруты для AJAX запросов
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/stats/monthly', [DashboardController::class, 'monthlyStats'])->name('stats.monthly');
        Route::get('/repairs/chart-data', [RepairRequestController::class, 'chartData'])->name('repairs.chart');
        Route::get('/branches/stats', [BranchController::class, 'stats'])->name('branches.stats');
    });
});

// Маршруты для интеграции с Telegram ботом
Route::prefix('api/telegram')->group(function () {
    Route::post('/user-info', function(\Illuminate\Http\Request $request) {
        // Получение информации о пользователе по Telegram ID
        $telegramId = $request->input('telegram_id');
        $admin = \App\Models\Admin::where('telegram_id', $telegramId)->first();
        
        return response()->json([
            'is_admin' => (bool) $admin,
            'user_info' => $admin ? [
                'id' => $admin->id,
                'name' => $admin->name,
                'is_active' => $admin->is_active
            ] : null
        ]);
    });
    
    Route::post('/repair-notification', function(\Illuminate\Http\Request $request) {
        // Webhook для уведомлений о новых заявках
        \Illuminate\Support\Facades\Log::info('Repair notification received', $request->all());
        return response()->json(['status' => 'ok']);
    });
});

// Middleware для регистрации
Route::middleware('guest')->group(function () {
    // Дополнительные маршруты для гостей при необходимости
});