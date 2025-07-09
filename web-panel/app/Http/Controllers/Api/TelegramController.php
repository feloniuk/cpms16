<?php
// app/Http/Controllers/Api/TelegramController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\RepairRequest;
use App\Models\CartridgeReplacement;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramController extends Controller
{
    /**
     * Получить информацию о пользователе по Telegram ID
     */
    public function getUserInfo(Request $request)
    {
        $request->validate([
            'telegram_id' => 'required|numeric'
        ]);

        $telegramId = $request->telegram_id;
        
        // Проверяем существование администратора
        $admin = Admin::where('telegram_id', $telegramId)
            ->where('is_active', true)
            ->first();

        // Проверяем существование веб-пользователя
        $webUser = User::where('telegram_id', $telegramId)
            ->where('is_active', true)
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'telegram_id' => $telegramId,
                'is_admin' => (bool) $admin,
                'has_web_access' => (bool) $webUser,
                'admin_info' => $admin ? [
                    'id' => $admin->id,
                    'name' => $admin->name,
                    'is_active' => $admin->is_active,
                    'created_at' => $admin->created_at
                ] : null,
                'web_user_info' => $webUser ? [
                    'id' => $webUser->id,
                    'name' => $webUser->name,
                    'email' => $webUser->email,
                    'role' => $webUser->role
                ] : null
            ]
        ]);
    }

    /**
     * Уведомление о новой заявке на ремонт
     */
    public function repairNotification(Request $request)
    {
        $request->validate([
            'repair_id' => 'required|numeric|exists:repair_requests,id',
            'type' => 'required|in:created,updated,completed'
        ]);

        $repair = RepairRequest::with('branch')->find($request->repair_id);
        
        if (!$repair) {
            return response()->json(['error' => 'Repair request not found'], 404);
        }

        // Логируем уведомление
        Log::info('Telegram notification received', [
            'repair_id' => $repair->id,
            'type' => $request->type,
            'branch' => $repair->branch->name,
            'status' => $repair->status
        ]);

        // Здесь можно добавить реальную логику уведомлений
        // например, отправка email, push-уведомления и т.д.

        return response()->json([
            'success' => true,
            'message' => 'Notification processed',
            'repair' => [
                'id' => $repair->id,
                'branch' => $repair->branch->name,
                'room' => $repair->room_number,
                'status' => $repair->status,
                'created_at' => $repair->created_at
            ]
        ]);
    }

    /**
     * Уведомление о замене картриджа
     */
    public function cartridgeNotification(Request $request)
    {
        $request->validate([
            'cartridge_id' => 'required|numeric|exists:cartridge_replacements,id'
        ]);

        $cartridge = CartridgeReplacement::with('branch')->find($request->cartridge_id);
        
        if (!$cartridge) {
            return response()->json(['error' => 'Cartridge replacement not found'], 404);
        }

        Log::info('Cartridge notification received', [
            'cartridge_id' => $cartridge->id,
            'branch' => $cartridge->branch->name,
            'type' => $cartridge->cartridge_type
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Cartridge notification processed',
            'cartridge' => [
                'id' => $cartridge->id,
                'branch' => $cartridge->branch->name,
                'room' => $cartridge->room_number,
                'type' => $cartridge->cartridge_type,
                'replacement_date' => $cartridge->replacement_date
            ]
        ]);
    }

    /**
     * Получить статистику для бота
     */
    public function getStats(Request $request)
    {
        $branchId = $request->get('branch_id');
        $userId = $request->get('user_id');

        // Общая статистика
        $stats = [
            'repairs' => [
                'total' => RepairRequest::count(),
                'new' => RepairRequest::where('status', 'нова')->count(),
                'in_progress' => RepairRequest::where('status', 'в_роботі')->count(),
                'completed' => RepairRequest::where('status', 'виконана')->count()
            ],
            'cartridges' => [
                'total' => CartridgeReplacement::count(),
                'this_month' => CartridgeReplacement::whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->count()
            ],
            'branches' => Branch::where('is_active', true)->count()
        ];

        // Статистика по конкретной филиале
        if ($branchId) {
            $stats['branch'] = [
                'repairs' => RepairRequest::where('branch_id', $branchId)->count(),
                'cartridges' => CartridgeReplacement::where('branch_id', $branchId)->count()
            ];
        }

        // Статистика по пользователю
        if ($userId) {
            $stats['user'] = [
                'repairs' => RepairRequest::where('user_telegram_id', $userId)->count(),
                'cartridges' => CartridgeReplacement::where('user_telegram_id', $userId)->count()
            ];
        }

        return response()->json([
            'success' => true,
            'stats' => $stats,
            'generated_at' => now()
        ]);
    }

    /**
     * Получить активные филиалы
     */
    public function getBranches(Request $request)
    {
        $branches = Branch::where('is_active', true)
            ->withCount(['repairRequests', 'cartridgeReplacements'])
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'branches' => $branches->map(function ($branch) {
                return [
                    'id' => $branch->id,
                    'name' => $branch->name,
                    'repair_requests_count' => $branch->repair_requests_count,
                    'cartridge_replacements_count' => $branch->cartridge_replacements_count,
                    'created_at' => $branch->created_at
                ];
            })
        ]);
    }

    /**
     * Получить последние заявки
     */
    public function getRecentRepairs(Request $request)
    {
        $limit = $request->get('limit', 10);
        $branchId = $request->get('branch_id');
        $userId = $request->get('user_id');

        $query = RepairRequest::with('branch')->orderBy('created_at', 'desc');

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        if ($userId) {
            $query->where('user_telegram_id', $userId);
        }

        $repairs = $query->limit($limit)->get();

        return response()->json([
            'success' => true,
            'repairs' => $repairs->map(function ($repair) {
                return [
                    'id' => $repair->id,
                    'branch' => $repair->branch->name,
                    'room_number' => $repair->room_number,
                    'description' => $repair->description,
                    'status' => $repair->status,
                    'username' => $repair->username,
                    'created_at' => $repair->created_at,
                    'updated_at' => $repair->updated_at
                ];
            })
        ]);
    }

    /**
     * Обновить статус заявки через API
     */
    public function updateRepairStatus(Request $request)
    {
        $request->validate([
            'repair_id' => 'required|numeric|exists:repair_requests,id',
            'status' => 'required|in:нова,в_роботі,виконана',
            'admin_telegram_id' => 'required|numeric'
        ]);

        // Проверяем права администратора
        $admin = Admin::where('telegram_id', $request->admin_telegram_id)
            ->where('is_active', true)
            ->first();

        if (!$admin) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $repair = RepairRequest::find($request->repair_id);
        $oldStatus = $repair->status;
        
        $repair->update([
            'status' => $request->status,
            'updated_at' => now()
        ]);

        Log::info('Repair status updated via API', [
            'repair_id' => $repair->id,
            'old_status' => $oldStatus,
            'new_status' => $request->status,
            'admin_id' => $admin->id
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully',
            'repair' => [
                'id' => $repair->id,
                'old_status' => $oldStatus,
                'new_status' => $repair->status,
                'updated_at' => $repair->updated_at
            ]
        ]);
    }

    /**
     * Создать заявку через API (для тестирования)
     */
    public function createRepair(Request $request)
    {
        $request->validate([
            'user_telegram_id' => 'required|numeric',
            'username' => 'nullable|string',
            'branch_id' => 'required|exists:branches,id',
            'room_number' => 'required|string|max:50',
            'description' => 'required|string|max:1000',
            'phone' => 'nullable|string|max:20'
        ]);

        $repair = RepairRequest::create([
            'user_telegram_id' => $request->user_telegram_id,
            'username' => $request->username,
            'branch_id' => $request->branch_id,
            'room_number' => $request->room_number,
            'description' => $request->description,
            'phone' => $request->phone,
            'status' => 'нова'
        ]);

        Log::info('Repair created via API', [
            'repair_id' => $repair->id,
            'user_id' => $request->user_telegram_id,
            'branch_id' => $request->branch_id
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Repair request created successfully',
            'repair' => [
                'id' => $repair->id,
                'branch_id' => $repair->branch_id,
                'room_number' => $repair->room_number,
                'status' => $repair->status,
                'created_at' => $repair->created_at
            ]
        ], 201);
    }

    /**
     * Webhook для синхронизации данных
     */
    public function webhook(Request $request)
    {
        // Простая проверка безопасности
        $expectedToken = config('app.telegram_webhook_token', 'default_token');
        $providedToken = $request->header('X-Telegram-Token');

        if ($providedToken !== $expectedToken) {
            return response()->json(['error' => 'Invalid token'], 401);
        }

        $action = $request->get('action');
        $data = $request->get('data', []);

        try {
            switch ($action) {
                case 'repair_created':
                    $this->handleRepairCreated($data);
                    break;
                case 'repair_updated':
                    $this->handleRepairUpdated($data);
                    break;
                case 'cartridge_created':
                    $this->handleCartridgeCreated($data);
                    break;
                default:
                    Log::warning('Unknown webhook action', ['action' => $action]);
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Webhook processing error', [
                'action' => $action,
                'error' => $e->getMessage(),
                'data' => $data
            ]);

            return response()->json(['error' => 'Processing failed'], 500);
        }
    }

    private function handleRepairCreated($data)
    {
        Log::info('Repair created webhook', $data);
        // Здесь можно добавить логику обработки создания заявки
        // например, отправка уведомлений, обновление кэша и т.д.
    }

    private function handleRepairUpdated($data)
    {
        Log::info('Repair updated webhook', $data);
        // Логика обработки обновления заявки
    }

    private function handleCartridgeCreated($data)
    {
        Log::info('Cartridge created webhook', $data);
        // Логика обработки создания записи о замене картриджа
    }

    /**
     * Получить конфигурацию для бота
     */
    public function getConfig(Request $request)
    {
        return response()->json([
            'success' => true,
            'config' => [
                'web_panel_url' => config('app.url'),
                'api_version' => '1.0',
                'features' => [
                    'repairs' => true,
                    'cartridges' => true,
                    'inventory' => true,
                    'reports' => true
                ],
                'limits' => [
                    'max_description_length' => 1000,
                    'max_room_number_length' => 50,
                    'max_cartridge_type_length' => 255
                ]
            ]
        ]);
    }
}