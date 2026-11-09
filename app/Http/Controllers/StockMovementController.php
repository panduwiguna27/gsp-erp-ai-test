<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StockMovementController extends Controller
{
    public function index(Request $request): View
    {
        $request->validate([
            'type' => ['nullable', Rule::in(['in', 'out', 'adjustment'])],
        ]);

        $movements = StockMovement::query()
            ->with(['product', 'user'])
            ->when($request->input('product_id'), fn ($q, $id) => $q->where('product_id', $id))
            ->when($request->input('type'), fn ($q, $t) => $q->where('type', $t))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('movements.index', [
            'movements' => $movements,
            'products' => Product::orderBy('name')->get(),
        ]);
    }
}
