@extends('layouts.app')

@section('title', $order->code)
@section('heading', 'Sales Order ' . $order->code)

@section('actions')
    <a href="{{ route('sales.index') }}" class="text-sm text-slate-500 hover:text-slate-800">← Back to list</a>
@endsection

@section('content')
    @php
        $money = fn ($v) => number_format((float) $v, 0, ',', '.');
        $badge = [
            'draft' => 'bg-slate-100 text-slate-600 ring-slate-200',
            'confirmed' => 'bg-amber-50 text-amber-700 ring-amber-200',
            'shipped' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
            'cancelled' => 'bg-rose-50 text-rose-700 ring-rose-200',
        ];
    @endphp

    <div class="grid gap-4 sm:grid-cols-4 mb-6">
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs uppercase tracking-wide text-slate-500">Customer</p>
            <p class="mt-1 font-medium text-slate-800">{{ $order->customer->name ?? '—' }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs uppercase tracking-wide text-slate-500">Date</p>
            <p class="mt-1 font-medium text-slate-800">{{ $order->order_date?->format('d M Y') ?: '—' }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs uppercase tracking-wide text-slate-500">Status</p>
            <p class="mt-1">
                <span class="inline-flex rounded px-2 py-0.5 text-xs font-medium ring-1 {{ $badge[$order->status] ?? $badge['draft'] }}">
                    {{ $order->status }}
                </span>
            </p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs uppercase tracking-wide text-slate-500">Total</p>
            <p class="mt-1 font-semibold text-slate-900">{{ $money($order->total) }}</p>
        </div>
    </div>

    @if ($order->notes)
        <p class="mb-4 rounded-lg bg-amber-50 border border-amber-200 px-4 py-2 text-sm text-amber-900">{{ $order->notes }}</p>
    @endif

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Product</th>
                    <th class="px-4 py-2 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Qty</th>
                    <th class="px-4 py-2 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Price</th>
                    <th class="px-4 py-2 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Subtotal</th>
                    <th class="px-4 py-2 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Stock now</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($order->items as $item)
                    <tr>
                        <td class="px-4 py-2.5 text-slate-800">
                            {{ $item->product->name ?? 'Product removed' }}
                            <span class="text-xs text-slate-400">{{ $item->product?->sku }}</span>
                        </td>
                        <td class="px-4 py-2.5 text-right">{{ $item->qty }}</td>
                        <td class="px-4 py-2.5 text-right text-slate-600">{{ $money($item->unit_price) }}</td>
                        <td class="px-4 py-2.5 text-right text-slate-600">{{ $money($item->subtotal) }}</td>
                        <td class="px-4 py-2.5 text-right {{ ($item->product && $item->product->stock_qty < $item->qty) ? 'text-rose-600 font-medium' : 'text-slate-500' }}">
                            {{ $item->product->stock_qty ?? '—' }}
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-slate-400">This order has no lines.</td></tr>
                @endforelse
            </tbody>
            <tfoot class="bg-slate-50 border-t border-slate-200 font-semibold text-slate-800">
                <tr>
                    <td class="px-4 py-2.5 text-right" colspan="3">Total</td>
                    <td class="px-4 py-2.5 text-right">{{ $money($order->total) }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="mt-6 flex flex-wrap items-center gap-3">
        @if ($order->isShippable())
            <form method="POST" action="{{ route('sales.ship', $order) }}"
                  onsubmit="return confirm('Ship {{ $order->code }}? Stock will be reduced.')">
                @csrf
                <button class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                    Mark as shipped &amp; reduce stock
                </button>
            </form>
        @else
            <p class="text-sm text-slate-500">This order has already been {{ $order->status }}.</p>
        @endif

        <form method="POST" action="{{ route('sales.destroy', $order) }}"
              onsubmit="return confirm('Delete {{ $order->code }}?')">
            @csrf @method('DELETE')
            <button class="rounded-md border border-rose-300 bg-white px-4 py-2 text-sm text-rose-700 hover:bg-rose-50">Delete</button>
        </form>
    </div>
@endsection
