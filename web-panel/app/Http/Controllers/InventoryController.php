<?php
namespace App\Http\Controllers;

use App\Models\RoomInventory;
use App\Models\Branch;
use App\Models\InventoryTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $query = RoomInventory::with('branch', 'template');

        // Фильтрация
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('equipment_type')) {
            $query->where('equipment_type', 'like', '%' . $request->equipment_type . '%');
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('inventory_number', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%")
                  ->orWhere('model', 'like', "%{$search}%")
                  ->orWhere('room_number', 'like', "%{$search}%");
            });
        }

        $inventory = $query->orderBy('created_at', 'desc')->paginate(20);
        $branches = Branch::where('is_active', true)->get();
        
        // Статистика по типам оборудования
        $equipmentStats = RoomInventory::select('equipment_type')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('equipment_type')
            ->orderBy('count', 'desc')
            ->get();

        return view('inventory.index', compact('inventory', 'branches', 'equipmentStats'));
    }

    public function show(RoomInventory $inventory)
    {
        $inventory->load('branch', 'template');
        
        // Связанные замены картриджей (если это принтер)
        $cartridgeReplacements = null;
        if (stripos($inventory->equipment_type, 'принтер') !== false) {
            $cartridgeReplacements = \App\Models\CartridgeReplacement::where('printer_inventory_id', $inventory->id)
                ->orderBy('replacement_date', 'desc')
                ->limit(10)
                ->get();
        }

        return view('inventory.show', compact('inventory', 'cartridgeReplacements'));
    }

    public function create()
    {
        $branches = Branch::where('is_active', true)->get();
        $templates = InventoryTemplate::orderBy('name')->get();
        return view('inventory.create', compact('branches', 'templates'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'room_number' => 'required|string|max:50',
            'equipment_type' => 'required|string|max:100',
            'brand' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'serial_number' => 'nullable|string|max:255',
            'inventory_number' => 'required|string|max:255|unique:room_inventory,inventory_number',
            'notes' => 'nullable|string|max:1000'
        ]);

        RoomInventory::create([
            'admin_telegram_id' => Auth::user()->telegram_id ?? 0,
            'branch_id' => $request->branch_id,
            'room_number' => $request->room_number,
            'equipment_type' => $request->equipment_type,
            'brand' => $request->brand,
            'model' => $request->model,
            'serial_number' => $request->serial_number,
            'inventory_number' => $request->inventory_number,
            'notes' => $request->notes
        ]);

        return redirect()->route('inventory.index')->with('success', 'Оборудование добавлено');
    }

    public function edit(RoomInventory $inventory)
    {
        $branches = Branch::where('is_active', true)->get();
        $templates = InventoryTemplate::orderBy('name')->get();
        return view('inventory.edit', compact('inventory', 'branches', 'templates'));
    }

    public function update(Request $request, RoomInventory $inventory)
    {
        $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'room_number' => 'required|string|max:50',
            'equipment_type' => 'required|string|max:100',
            'brand' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'serial_number' => 'nullable|string|max:255',
            'inventory_number' => 'required|string|max:255|unique:room_inventory,inventory_number,' . $inventory->id,
            'notes' => 'nullable|string|max:1000'
        ]);

        $inventory->update($request->only([
            'branch_id', 'room_number', 'equipment_type', 'brand', 
            'model', 'serial_number', 'inventory_number', 'notes'
        ]));

        return redirect()->route('inventory.index')->with('success', 'Оборудование обновлено');
    }

    public function destroy(RoomInventory $inventory)
    {
        // Проверяем, не связано ли оборудование с заменами картриджей
        $hasCartridges = \App\Models\CartridgeReplacement::where('printer_inventory_id', $inventory->id)->exists();
        
        if ($hasCartridges) {
            return redirect()->back()->withErrors(['Нельзя удалить оборудование, связанное с заменами картриджей']);
        }

        $inventory->delete();

        return redirect()->route('inventory.index')->with('success', 'Оборудование удалено');
    }

    public function export(Request $request)
    {
        $query = RoomInventory::with('branch');

        // Применяем те же фильтры что и в index
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('equipment_type')) {
            $query->where('equipment_type', 'like', '%' . $request->equipment_type . '%');
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('inventory_number', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%")
                  ->orWhere('model', 'like', "%{$search}%");
            });
        }

        $inventory = $query->orderBy('branch_id')->orderBy('room_number')->get();

        // Простой CSV экспорт
        $filename = 'inventory_' . date('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($inventory) {
            $file = fopen('php://output', 'w');
            
            // UTF-8 BOM для правильного отображения в Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Заголовки
            fputcsv($file, [
                'ID', 'Филиал', 'Кабинет', 'Тип оборудования', 
                'Бренд', 'Модель', 'Серийный номер', 'Инвентарный номер', 
                'Заметки', 'Дата добавления'
            ], ';');

            // Данные
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
                    $item->notes,
                    $item->created_at->format('d.m.Y H:i')
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
