@extends('layouts.app')

@section('title', 'Dashboard')
@section('heading', 'Dashboard')

@section('content')
    @php $money = fn ($v) => number_format((float) $v, 0, ',', '.'); @endphp

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([
            ['label' => 'Products', 'value' => $stats['products'], 'tone' => 'text-slate-900'],
            ['label' => 'Low stock items', 'value' => $stats['low_stock'], 'tone' => $stats['low_stock'] > 0 ? 'text-rose-600' : 'text-emerald-600'],
            ['label' => 'Stock value (cost)', 'value' => $money($stats['stock_value']), 'tone' => 'text-slate-900'],
            ['label' => 'Open sales orders', 'value' => $stats['open_sales'], 'tone' => 'text-indigo-600'],
        ] as $card)
            <div class="rounded-xl bg-white p-5 shadow-sm border border-slate-200">
                <p class="text-xs uppercase tracking-wide text-slate-500">{{ $card['label'] }}</p>
                <p class="mt-2 text-2xl font-semibold {{ $card['tone'] }}">{{ $card['value'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="grid gap-6 mt-6 lg:grid-cols-2">
        <div class="rounded-xl bg-white shadow-sm border border-slate-200">
            <div class="flex items-center justify-between px-5 py-3 border-b border-slate-200">
                <h2 class="font-medium text-slate-900">Needs restocking</h2>
                <a href="{{ route('products.index', ['low' => 1]) }}" class="text-sm text-indigo-600 hover:underline">View all</a>
            </div>
            <table class="w-full text-sm">
                <tbody>
                @forelse ($lowStockProducts as $product)
                    <tr class="border-b border-slate-100 last:border-0">
                        <td class="px-5 py-2.5">
                            <span class="font-medium text-slate-800">{{ $product->name }}</span>
                            <span class="text-slate-400"> · {{ $product->sku }}</span>
                        </td>
                        <td class="px-5 py-2.5 text-right whitespace-nowrap">
                            <span class="inline-flex rounded bg-rose-50 px-2 py-0.5 text-rose-700 ring-1 ring-rose-200">
                                {{ $product->stock_qty }} {{ $product->unit }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr><td class="px-5 py-6 text-center text-slate-400">Everything is above its reorder level.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="rounded-xl bg-white shadow-sm border border-slate-200">
            <div class="flex items-center justify-between px-5 py-3 border-b border-slate-200">
                <h2 class="font-medium text-slate-900">Recent stock activity</h2>
                <a href="{{ route('movements.index') }}" class="text-sm text-indigo-600 hover:underline">Ledger</a>
            </div>
            <ul class="divide-y divide-slate-100 text-sm">
                @forelse ($recentMovements as $movement)
                    <li class="px-5 py-2.5 flex items-center justify-between gap-3">
                        <span class="truncate">
                            {{ $movement->product->name ?? 'Deleted product' }}
                            <span class="text-slate-400">· {{ $movement->reference ?? $movement->type }}</span>
                        </span>
                        <span class="shrink-0 font-medium {{ $movement->qty >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                            {{ $movement->qty > 0 ? '+' : '' }}{{ $movement->qty }}
                        </span>
                    </li>
                @empty
                    <li class="px-5 py-6 text-center text-slate-400">No movements recorded yet.</li>
                @endforelse
            </ul>
        </div>
    </div>

    <div class="grid gap-4 mt-6 sm:grid-cols-3">
        @foreach ([
            ['label' => 'Customers', 'value' => $stats['customers'], 'href' => route('customers.index')],
            ['label' => 'Suppliers', 'value' => $stats['suppliers'], 'href' => route('suppliers.index')],
            ['label' => 'Sales total (net)', 'value' => $money($stats['sales_total']), 'href' => route('sales.index')],
        ] as $tile)
            <a href="{{ $tile['href'] }}" class="block rounded-xl bg-white p-5 shadow-sm border border-slate-200 hover:border-indigo-300">
                <p class="text-xs uppercase tracking-wide text-slate-500">{{ $tile['label'] }}</p>
                <p class="mt-2 text-xl font-semibold text-slate-900">{{ $tile['value'] }}</p>
            </a>
        @endforeach
    </div>
@endsection
