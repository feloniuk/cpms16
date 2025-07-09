<?php
namespace App\Http\Controllers;

use App\Models\RepairRequest;
use App\Models\CartridgeReplacement;
use App\Models\Branch;
use App\Models\RoomInventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        if ($user->role === 'director') {
            return $this->directorDashboard();
        }
        
        return $this->adminDashboard();
    }

    private function adminDashboard()
    {
        // Статистика заявок на ремонт
        $repairStats = [
            'total' => RepairRequest::count(),
            'new' => RepairRequest::where('status', 'нова')->count(),
            'in_progress' => RepairRequest::where('status', 'в_роботі')->count(),
            'completed' => RepairRequest::where('status', 'виконана')->count(),
        ];

        // Последние заявки
        $recentRepairs = RepairRequest::with('branch')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Статистика картриджей за последний месяц
        $cartridgeCount = CartridgeReplacement::where('created_at', '>=', Carbon::now()->subMonth())->count();

        // Статистика по филиалам
        $branchStats = Branch::withCount(['repairRequests', 'cartridgeReplacements'])
            ->orderBy('repair_requests_count', 'desc')
            ->get();

        // Общий инвентарь
        $inventoryCount = RoomInventory::count();

        return view('dashboard.admin', compact(
            'repairStats', 
            'recentRepairs', 
            'cartridgeCount', 
            'branchStats', 
            'inventoryCount'
        ));
    }

    private function directorDashboard()
    {
        // Общая статистика
        $totalStats = [
            'branches' => Branch::where('is_active', true)->count(),
            'total_repairs' => RepairRequest::count(),
            'total_cartridges' => CartridgeReplacement::count(),
            'total_inventory' => RoomInventory::count(),
        ];

        // Статистика за периоды
        $monthlyStats = $this->getMonthlyStats();
        
        // Статистика по статусам заявок
        $statusStats = RepairRequest::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();

        // Топ филиалы по активности
        $topBranches = Branch::withCount(['repairRequests', 'cartridgeReplacements'])
            ->orderBy('repair_requests_count', 'desc')
            ->limit(5)
            ->get();

        // Динамика заявок по месяцам (последние 6 месяцев)
        $monthlyRepairs = RepairRequest::select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', Carbon::now()->subMonths(6))
            ->groupBy('year', 'month')
            ->orderBy('year', 'month')
            ->get();

        return view('dashboard.director', compact(
            'totalStats',
            'monthlyStats', 
            'statusStats',
            'topBranches',
            'monthlyRepairs'
        ));
    }

    private function getMonthlyStats()
    {
        $currentMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();

        return [
            'repairs_this_month' => RepairRequest::where('created_at', '>=', $currentMonth)->count(),
            'repairs_last_month' => RepairRequest::whereBetween('created_at', [
                $lastMonth, 
                $lastMonth->copy()->endOfMonth()
            ])->count(),
            'cartridges_this_month' => CartridgeReplacement::where('created_at', '>=', $currentMonth)->count(),
            'cartridges_last_month' => CartridgeReplacement::whereBetween('created_at', [
                $lastMonth,
                $lastMonth->copy()->endOfMonth()
            ])->count(),
        ];
    }
}