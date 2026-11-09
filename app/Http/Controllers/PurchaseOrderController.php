<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class PurchaseOrderController extends Controller
{
    public function index(Request $request): View
    {
        $orders = PurchaseOrder::query()
            ->with('supplier')
            ->when($request->input('status'), fn ($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('purchases.index', compact('orders'));
    }

    public function create(): View
    {
        return view('purchases.create', [
            'suppliers' => Supplier::orderBy('name')->get(),
            'products' => Product::where('is_active', true)->orderBy('name')->get(),
            'order' => new PurchaseOrder(['order_date' => now(), 'status' => 'draft']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $rows = collect($request->input('items', []))
            ->map(fn ($r) => [
                'product_id' => $r['product_id'] ?? null,
                'qty' => (int) ($r['qty'] ?? 0),
                'unit_cost' => (float) ($r['unit_cost'] ?? 0),
            ])
            ->filter(fn ($r) => ! empty($r['product_id']) && $r['qty'] > 0)
            ->values()
            ->all();

        $validator = Validator::make($request->all() + ['rows' => $rows], [
            'supplier_id' => ['required', 'integer', 'exists:suppliers,id'],
            'order_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'rows' => ['required', 'array', 'min:1'],
            'rows.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'rows.*.qty' => ['required', 'integer', 'min:1'],
            'rows.*.unit_cost' => ['required', 'numeric', 'min:0'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $order = DB::transaction(function () use ($request, $rows) {
            $order = PurchaseOrder::create([
                'code' => PurchaseOrder::nextCode(),
                'supplier_id' => $request->input('supplier_id'),
                'status' => 'draft',
                'order_date' => $request->input('order_date') ?? now()->toDateString(),
                'notes' => $request->input('notes'),
                'user_id' => $request->user()?->id,
            ]);

            foreach ($rows as $row) {
                $order->items()->create([
                    'product_id' => $row['product_id'],
                    'qty' => $row['qty'],
                    'unit_cost' => $row['unit_cost'],
                    'subtotal' => $row['qty'] * $row['unit_cost'],
                ]);
            }

            $order->recalculateTotal();

            return $order;
        });

        return redirect()->route('purchases.show', $order)
            ->with('success', "Purchase order {$order->code} created.");
    }

    public function show(PurchaseOrder $purchase): View
    {
        $purchase->load(['supplier', 'items.product']);

        return view('purchases.show', ['order' => $purchase]);
    }

    public function receive(Request $request, PurchaseOrder $purchase): RedirectResponse
    {
        if (! $purchase->isReceivable()) {
            return back()->with('error', "Order {$purchase->code} is already {$purchase->status}.");
        }

        DB::transaction(function () use ($request, $purchase) {
            $purchase->items->each(function ($item) use ($purchase, $request) {
                $item->product?->adjustStock(
                    (int) $item->qty,
                    'in',
                    $purchase->code,
                    'Purchase received',
                    $request->user()?->id,
                );
            });

            $purchase->update(['status' => 'received']);
        });

        return back()->with('success', "Order {$purchase->code} received; stock updated.");
    }

    public function destroy(PurchaseOrder $purchase): RedirectResponse
    {
        $code = $purchase->code;
        $purchase->items()->delete();
        $purchase->delete();

        return redirect()->route('purchases.index')->with('success', "Order {$code} deleted.");
    }
}
