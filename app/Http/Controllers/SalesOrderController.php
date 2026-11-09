<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\SalesOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SalesOrderController extends Controller
{
    public function index(Request $request): View
    {
        $orders = SalesOrder::query()
            ->with('customer')
            ->when($request->input('status'), fn ($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('sales.index', compact('orders'));
    }

    public function create(): View
    {
        return view('sales.create', [
            'customers' => Customer::orderBy('name')->get(),
            'products' => Product::where('is_active', true)->orderBy('name')->get(),
            'order' => new SalesOrder(['order_date' => now(), 'status' => 'draft']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $rows = collect($request->input('items', []))
            ->map(fn ($r) => [
                'product_id' => $r['product_id'] ?? null,
                'qty' => (int) ($r['qty'] ?? 0),
                'unit_price' => (float) ($r['unit_price'] ?? 0),
            ])
            ->filter(fn ($r) => ! empty($r['product_id']) && $r['qty'] > 0)
            ->values()
            ->all();

        $validator = Validator::make($request->all() + ['rows' => $rows], [
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'order_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'rows' => ['required', 'array', 'min:1'],
            'rows.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'rows.*.qty' => ['required', 'integer', 'min:1'],
            'rows.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $order = DB::transaction(function () use ($request, $rows) {
            $order = SalesOrder::create([
                'code' => SalesOrder::nextCode(),
                'customer_id' => $request->input('customer_id'),
                'status' => 'draft',
                'order_date' => $request->input('order_date') ?? now()->toDateString(),
                'notes' => $request->input('notes'),
                'user_id' => $request->user()?->id,
            ]);

            foreach ($rows as $row) {
                $order->items()->create([
                    'product_id' => $row['product_id'],
                    'qty' => $row['qty'],
                    'unit_price' => $row['unit_price'],
                    'subtotal' => $row['qty'] * $row['unit_price'],
                ]);
            }

            $order->recalculateTotal();

            return $order;
        });

        return redirect()->route('sales.show', $order)
            ->with('success', "Sales order {$order->code} created.");
    }

    public function show(SalesOrder $sale): View
    {
        $sale->load(['customer', 'items.product']);

        return view('sales.show', ['order' => $sale]);
    }

    public function ship(Request $request, SalesOrder $sale): RedirectResponse
    {
        if (! $sale->isShippable()) {
            return back()->with('error', "Order {$sale->code} is already {$sale->status}.");
        }

        $sale->load('items.product');

        $shortage = $sale->items->first(function ($item) {
            return ! $item->product || $item->product->stock_qty < $item->qty;
        });

        if ($shortage) {
            throw ValidationException::withMessages([
                'items' => "Insufficient stock for ".($shortage->product->name ?? 'a product').
                    ' (need '.($shortage->qty ?? 0).', have '.($shortage->product->stock_qty ?? 0).').',
            ]);
        }

        DB::transaction(function () use ($request, $sale) {
            $sale->items->each(function ($item) use ($sale, $request) {
                $item->product->adjustStock(
                    -(int) $item->qty,
                    'out',
                    $sale->code,
                    'Order shipped',
                    $request->user()?->id,
                );
            });

            $sale->update(['status' => 'shipped']);
        });

        return back()->with('success', "Order {$sale->code} shipped; stock reduced.");
    }

    public function destroy(SalesOrder $sale): RedirectResponse
    {
        $code = $sale->code;
        $sale->items()->delete();
        $sale->delete();

        return redirect()->route('sales.index')->with('success', "Order {$code} deleted.");
    }
}
