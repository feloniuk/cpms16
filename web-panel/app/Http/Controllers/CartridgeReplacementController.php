<?php 

namespace App\Http\Controllers;

use App\Models\CartridgeReplacement;
use App\Models\Branch;
use Illuminate\Http\Request;

class CartridgeReplacementController extends Controller
{
    public function index(Request $request)
    {
        $query = CartridgeReplacement::with('branch');

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('cartridge_type', 'like', "%{$search}%")
                  ->orWhere('printer_info', 'like', "%{$search}%")
                  ->orWhere('room_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->where('replacement_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('replacement_date', '<=', $request->date_to);
        }

        $cartridges = $query->orderBy('replacement_date', 'desc')->paginate(20);
        $branches = Branch::where('is_active', true)->get();

        return view('cartridges.index', compact('cartridges', 'branches'));
    }

    public function show(CartridgeReplacement $cartridge)
    {
        $cartridge->load('branch', 'printer');
        return view('cartridges.show', compact('cartridge'));
    }
}