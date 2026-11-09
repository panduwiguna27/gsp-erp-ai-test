<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $products = Product::query()
            ->search($request->input('q'))
            ->when($request->filled('low'), fn ($q) => $q->lowStock())
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('products.index', compact('products'));
    }

    public function create(): View
    {
        $product = new Product([
            'unit' => 'pcs', 'reorder_level' => 5, 'stock_qty' => 0,
            'cost_price' => 0, 'sale_price' => 0, 'is_active' => true,
        ]);

        return view('products.form', compact('product'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $opening = (int) ($data['stock_qty'] ?? 0);
        unset($data['stock_qty']);

        // Open against zero so the opening balance is recorded as a real
        // movement instead of a silent edit to the stock column.
        $product = Product::create($data + ['stock_qty' => 0]);

        if ($opening > 0) {
            $product->adjustStock($opening, 'in', 'OPENING', 'Opening stock', $request->user()?->id);
        }

        return redirect()->route('products.index')->with('success', 'Product created.');
    }

    public function edit(Product $product): View
    {
        return view('products.form', compact('product'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $product->update($this->validated($request, $product));

        return redirect()->route('products.index')->with('success', 'Product updated.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()->route('products.index')->with('success', "Product {$product->sku} archived.");
    }

    public function adjust(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(['in', 'out', 'adjustment'])],
            'qty' => ['required', 'integer', 'not_in:0'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $delta = match ($data['type']) {
            'in' => abs((int) $data['qty']),
            'out' => -abs((int) $data['qty']),
            default => (int) $data['qty'],
        };

        if ($product->stock_qty + $delta < 0) {
            throw ValidationException::withMessages([
                'qty' => "Cannot go below zero. Current stock is {$product->stock_qty}.",
            ]);
        }

        $product->adjustStock($delta, $data['type'], 'manual', $data['note'] ?? null, $request->user()?->id);

        return back()->with('success', "Stock for {$product->sku} is now {$product->stock_qty}.");
    }

    private function validated(Request $request, ?Product $product = null): array
    {
        $data = $request->validate([
            'sku' => ['required', 'string', 'max:60', Rule::unique('products', 'sku')->ignore($product?->id)->withoutTrashed()],
            'name' => ['required', 'string', 'max:180'],
            'category' => ['nullable', 'string', 'max:80'],
            'unit' => ['required', 'string', 'max:20'],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['required', 'numeric', 'min:0'],
            'stock_qty' => [$product ? 'nullable' : 'required', 'integer', 'min:0'],
            'reorder_level' => ['required', 'integer', 'min:0'],
        ]);

        // Stock is edited through adjustments/orders once the product exists,
        // so an absent value must not blank the stored quantity.
        if (array_key_exists('stock_qty', $data) && $data['stock_qty'] === null) {
            unset($data['stock_qty']);
        }

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
