<?php 

namespace App\Http\Controllers;

use App\Models\RepairRequest;
use App\Models\Branch;
use Illuminate\Http\Request;

class RepairRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = RepairRequest::with('branch');

        // Фильтрация
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('room_number', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
            });
        }

        $repairs = $query->orderBy('created_at', 'desc')->paginate(20);
        $branches = Branch::where('is_active', true)->get();

        return view('repairs.index', compact('repairs', 'branches'));
    }

    public function show(RepairRequest $repair)
    {
        $repair->load('branch');
        return view('repairs.show', compact('repair'));
    }

    public function update(Request $request, RepairRequest $repair)
    {
        $request->validate([
            'status' => 'required|in:нова,в_роботі,виконана'
        ]);

        $repair->update([
            'status' => $request->status,
            'updated_at' => now()
        ]);

        return redirect()->back()->with('success', 'Статус заявки оновлено');
    }
}