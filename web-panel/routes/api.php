<?php
// routes/api.php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Маршруты для интеграции с Telegram ботом
Route::prefix('telegram')->group(function () {
    Route::post('/user-info', function(Request $request) {
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
    
    Route::post('/repair-notification', function(Request $request) {
        \Illuminate\Support\Facades\Log::info('Repair notification received', $request->all());
        return response()->json(['status' => 'ok']);
    });
    
    Route::get('/stats', function(Request $request) {
        return response()->json([
            'repairs_total' => \App\Models\RepairRequest::count(),
            'repairs_new' => \App\Models\RepairRequest::where('status', 'нова')->count(),
            'cartridges_total' => \App\Models\CartridgeReplacement::count(),
            'branches_total' => \App\Models\Branch::where('is_active', true)->count(),
        ]);
    });
});

// Дополнительные API маршруты для внутреннего использования
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/dashboard-stats', function() {
        return response()->json([
            'repairs' => [
                'total' => \App\Models\RepairRequest::count(),
                'new' => \App\Models\RepairRequest::where('status', 'нова')->count(),
                'in_progress' => \App\Models\RepairRequest::where('status', 'в_роботі')->count(),
                'completed' => \App\Models\RepairRequest::where('status', 'виконана')->count(),
            ],
            'cartridges' => [
                'total' => \App\Models\CartridgeReplacement::count(),
                'this_month' => \App\Models\CartridgeReplacement::whereMonth('created_at', now()->month)->count(),
            ],
            'branches' => \App\Models\Branch::where('is_active', true)->count(),
        ]);
    });
});