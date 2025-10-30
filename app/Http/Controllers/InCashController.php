<?php

namespace App\Http\Controllers;

use App\Models\InCash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class InCashController extends Controller
{
    public function index()
    {
        if (!Gate::allows('hasRole', ['Admin'])) {
            abort(403, 'Unauthorized');
        }

        $inCashRecords = InCash::orderBy('created_at', 'desc')->get();

        return response()->json([
            'data' => $inCashRecords,
            'total' => $inCashRecords->sum('amount')
        ]);
    }

    public function store(Request $request)
    {
        if (!Gate::allows('hasRole', ['Admin'])) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'note' => 'nullable|string|max:500',
        ]);

        $inCash = InCash::create($validated);

        return response()->json([
            'message' => 'In Cash added successfully',
            'data' => $inCash
        ], 201);
    }

    public function destroy($id)
    {
        if (!Gate::allows('hasRole', ['Admin'])) {
            abort(403, 'Unauthorized');
        }

        $inCash = InCash::findOrFail($id);
        $inCash->delete();

        return response()->json([
            'message' => 'In Cash record deleted successfully'
        ]);
    }
}