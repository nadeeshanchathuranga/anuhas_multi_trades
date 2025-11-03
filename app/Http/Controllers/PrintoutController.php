<?php

namespace App\Http\Controllers;

use App\Models\Printout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class PrintoutController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    /**
 * Display a listing of the resource.
 */
public function index(Request $request)
{
    $query = Printout::query();

    // Add search functionality to the main index method
    if ($request->filled('search')) {
        $query->where('name', 'like', '%' . $request->search . '%');
    }

    $printouts = $query->latest()->paginate(12);
    
    return Inertia::render('Printouts/Index', [
        'printouts' => $printouts,
        'totalPrintouts' => Printout::count(),
        'search' => $request->search, // Pass search term back to frontend
    ]);
}

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Printouts/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
        ]);

        Printout::create($request->only(['name', 'price']));

        return redirect()->route('printouts.index')->with('success', 'Printout created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Printout $printout)
    {
        return Inertia::render('Printouts/Show', [
            'printout' => $printout,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Printout $printout)
    {
        return Inertia::render('Printouts/Edit', [
            'printout' => $printout,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Printout $printout)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
        ]);

        $printout->update($request->only(['name', 'price']));

        return redirect()->route('printouts.index')->with('success', 'Printout updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Printout $printout)
    {
        $printout->delete();

        return redirect()->route('printouts.index')->with('success', 'Printout deleted successfully.');
    }

    /**
     * Update stock quantity
     */
    public function updateStock(Request $request, Printout $printout)
    {
        $request->validate([
            'stock_quantity' => 'required|integer|min:0',
        ]);

        $printout->update([
            'stock_quantity' => $request->stock_quantity,
        ]);

        return back()->with('success', 'Stock quantity updated successfully.');
    }
    /**
     * API endpoint for fetching printouts (for POS modal)
     */
    /**
 * API endpoint for fetching printouts (for POS modal)
 */
public function apiIndex(Request $request)
{
    $query = Printout::query();

    // Search filter - UPDATED: search by name only
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where('name', 'like', "%{$search}%");
    }

    // Stock filter
    if ($request->filled('stock') && $request->stock !== 'all') {
        if ($request->stock === 'in') {
            $query->where('stock_quantity', '>', 0);
        } elseif ($request->stock === 'out') {
            $query->where('stock_quantity', '<=', 0);
        }
    }

    // Sort by price
    if ($request->filled('sort')) {
        $query->orderBy('price', $request->sort);
    } else {
        $query->latest();
    }

    $printouts = $query->get();

    return response()->json([
        'data' => $printouts,
        'count' => $printouts->count()
    ]);
}

}