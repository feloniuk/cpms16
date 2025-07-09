<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function index()
    {
        $branches = Branch::withCount(['repairRequests', 'cartridgeReplacements', 'inventory'])
            ->orderBy('name')
            ->get();

        return view('branches.index', compact('branches'));
    }

    public function show(Branch $branch)
    {
        $branch->load(['repairRequests' => function($query) {
            $query->orderBy('created_at', 'desc')->limit(10);
        }]);

        return view('branches.show', compact('branch'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:branches,name'
        ]);

        Branch::create([
            'name' => $request->name,
            'is_active' => true
        ]);

        return redirect()->route('branches.index')->with('success', 'Філію створено');
    }

    public function update(Request $request, Branch $branch)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:branches,name,' . $branch->id,
            'is_active' => 'boolean'
        ]);

        $branch->update($request->only(['name', 'is_active']));

        return redirect()->route('branches.index')->with('success', 'Філію оновлено');
    }
}