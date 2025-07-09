<?php 

namespace App\Http\Controllers;

use App\Models\RepairRequest;
use App\Models\CartridgeReplacement;
use App\Models\Branch;
use App\Models\RoomInventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportsController extends Controller
{
    public function index()
    {
        return view('reports.index');
    }

    public function repairs(Request $request)
    {
        $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'branch_id' => 'nullable|exists:branches,id'
        ]);

        $dateFrom = $request->date_from ? Carbon::parse($request->date_from) : Carbon::now()->subMonth();
        $dateTo = $request->date_to ? Carbon::parse($request->date_to) : Carbon::now();

        $query = RepairRequest::with('branch')
            ->whereBetween('created_at', [$dateFrom->startOfDay(), $dateTo->endOfDay()]);

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        $repairs = $query->orderBy('created_at', 'desc')->get();

        // Статистика
        $stats = [
            'total' => $repairs->count(),
            'new' => $repairs->where('status', 'нова')->count(),
            'in_progress' => $repairs->where('status', 'в_роботі')->count(),
            'completed' => $repairs->where('status', 'виконана')->count(),
        ];

        // Группировка по дням
        $dailyStats = $repairs->groupBy(function($repair) {
            return $repair->created_at->format('Y-m-d');
        })->map(function($group) {
            return $group->count();
        });

        // Группировка по филиалам
        $branchStats = $repairs->groupBy('branch.name')->map(function($group) {
            return [
                'total' => $group->count(),
                'new' => $group->where('status', 'нова')->count(),
                'completed' => $group->where('status', 'виконана')->count(),
            ];
        });

        $branches = Branch::where('is_active', true)->get();

        return view('reports.repairs', compact(
            'repairs', 'stats', 'dailyStats', 'branchStats', 
            'branches', 'dateFrom', 'dateTo'
        ));
    }

    public function cartridges(Request $request)
    {
        $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'branch_id' => 'nullable|exists:branches,id'
        ]);

        $dateFrom = $request->date_from ? Carbon::parse($request->date_from) : Carbon::now()->subMonth();
        $dateTo = $request->date_to ? Carbon::parse($request->date_to) : Carbon::now();

        $query = CartridgeReplacement::with('branch')
            ->whereBetween('replacement_date', [$dateFrom, $dateTo]);

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        $cartridges = $query->orderBy('replacement_date', 'desc')->get();

        // Статистика по типам картриджей
        $typeStats = $cartridges->groupBy('cartridge_type')
            ->map(function($group) {
                return $group->count();
            })
            ->sortDesc();

        // Статистика по филиалам
        $branchStats = $cartridges->groupBy('branch.name')
            ->map(function($group) {
                return $group->count();
            })
            ->sortDesc();

        // Динамика по дням
        $dailyStats = $cartridges->groupBy(function($item) {
            return $item->replacement_date->format('Y-m-d');
        })->map(function($group) {
            return $group->count();
        });

        $branches = Branch::where('is_active', true)->get();

        return view('reports.cartridges', compact(
            'cartridges', 'typeStats', 'branchStats', 'dailyStats',
            'branches', 'dateFrom', 'dateTo'
        ));
    }

    public function inventory(Request $request)
    {
        $inventory = RoomInventory::with('branch')->get();

        // Статистика по типам оборудования
        $typeStats = $inventory->groupBy('equipment_type')
            ->map(function($group) {
                return $group->count();
            })
            ->sortDesc();

        // Статистика по филиалам
        $branchStats = $inventory->groupBy('branch.name')
            ->map(function($group) {
                return [
                    'total' => $group->count(),
                    'types' => $group->groupBy('equipment_type')->count()
                ];
            });

        // Статистика по брендам
        $brandStats = $inventory->where('brand', '!=', '')
            ->groupBy('brand')
            ->map(function($group) {
                return $group->count();
            })
            ->sortDesc()
            ->take(10);

        return view('reports.inventory', compact(
            'inventory', 'typeStats', 'branchStats', 'brandStats'
        ));
    }

    public function export(Request $request)
    {
        $type = $request->get('type', 'repairs');
        
        switch ($type) {
            case 'repairs':
                return $this->exportRepairs($request);
            case 'cartridges':
                return $this->exportCartridges($request);
            case 'inventory':
                return $this->exportInventory($request);
            default:
                abort(404);
        }
    }

    private function exportRepairs(Request $request)
    {
        $dateFrom = $request->date_from ? Carbon::parse($request->date_from) : Carbon::now()->subMonth();
        $dateTo = $request->date_to ? Carbon::parse($request->date_to) : Carbon::now();

        $query = RepairRequest::with('branch')
            ->whereBetween('created_at', [$dateFrom->startOfDay(), $dateTo->endOfDay()]);

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        $repairs = $query->orderBy('created_at', 'desc')->get();

        $filename = 'repairs_report_' . date('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($repairs) {
            $file = fopen('php://output', 'w');
            
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($file, [
                'ID', 'Филиал', 'Кабинет', 'Описание', 'Пользователь', 
                'Телефон', 'Статус', 'Создано', 'Обновлено'
            ], ';');

            foreach ($repairs as $repair) {
                fputcsv($file, [
                    $repair->id,
                    $repair->branch->name,
                    $repair->room_number,
                    $repair->description,
                    $repair->username ?: 'ID: ' . $repair->user_telegram_id,
                    $repair->phone,
                    $repair->status,
                    $repair->created_at->format('d.m.Y H:i'),
                    $repair->updated_at->format('d.m.Y H:i')
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportCartridges(Request $request)
    {
        $dateFrom = $request->date_from ? Carbon::parse($request->date_from) : Carbon::now()->subMonth();
        $dateTo = $request->date_to ? Carbon::parse($request->date_to) : Carbon::now();

        $query = CartridgeReplacement::with('branch')
            ->whereBetween('replacement_date', [$dateFrom, $dateTo]);

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        $cartridges = $query->orderBy('replacement_date', 'desc')->get();

        $filename = 'cartridges_report_' . date('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($cartridges) {
            $file = fopen('php://output', 'w');
            
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($file, [
                'ID', 'Филиал', 'Кабинет', 'Принтер', 'Тип картриджа', 
                'Пользователь', 'Дата замены', 'Заметки'
            ], ';');

            foreach ($cartridges as $cartridge) {
                fputcsv($file, [
                    $cartridge->id,
                    $cartridge->branch->name,
                    $cartridge->room_number,
                    $cartridge->printer_info,
                    $cartridge->cartridge_type,
                    $cartridge->username ?: 'ID: ' . $cartridge->user_telegram_id,
                    $cartridge->replacement_date->format('d.m.Y'),
                    $cartridge->notes
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportInventory(Request $request)
    {
        $inventory = RoomInventory::with('branch')->orderBy('branch_id')->orderBy('room_number')->get();

        $filename = 'inventory_report_' . date('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($inventory) {
            $file = fopen('php://output', 'w');
            
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($file, [
                'ID', 'Филиал', 'Кабинет', 'Тип оборудования', 'Бренд', 
                'Модель', 'Серийный номер', 'Инвентарный номер', 'Заметки'
            ], ';');

            foreach ($inventory as $item) {
                fputcsv($file, [
                    $item->id,
                    $item->branch->name,
                    $item->room_number,
                    $item->equipment_type,
                    $item->brand,
                    $item->model,
                    $item->serial_number,
                    $item->inventory_number,
                    $item->notes
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}