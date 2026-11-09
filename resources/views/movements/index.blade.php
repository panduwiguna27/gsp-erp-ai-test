@extends('layouts.app')

@section('title', 'Stock Movements')
@section('heading', 'Stock Movements')

@section('content')
    <form method="GET" action="{{ route('movements.index') }}" class="mb-4 flex flex-wrap items-end gap-2">
        <div>
            <label class="block text-xs font-medium text-slate-600 mb-1">Product</label>
            <select name="product_id" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm">
                <option value="">All products</option>
                @foreach ($products as $product)
                    <option value="{{ $product->id }}" @selected(request('product_id') == $product->id)>
                        {{ $product->sku }} — {{ $product->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-600 mb-1">Type</label>
            <select name="type" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm">
                <option value="">All types</option>
                @foreach (['in', 'out', 'adjustment'] as $type)
                    <option value="{{ $type }}" @selected(request('type') === $type)>{{ $type }}</option>
                @endforeach
            </select>
        </div>
        <button class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-sm hover:bg-slate-50">Filter</button>
        <a href="{{ route('movements.index') }}" class="rounded-md px-3 py-1.5 text-sm text-slate-500 hover:text-slate-800">Reset</a>
    </form>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">When</th>
                    <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Product</th>
                    <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Type</th>
                    <th class="px-4 py-2 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Qty</th>
                    <th class="px-4 py-2 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Before</th>
                    <th class="px-4 py-2 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">After</th>
                    <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Reference</th>
                    <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">By</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($movements as $movement)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-2.5 text-slate-500 whitespace-nowrap">{{ $movement->created_at->format('d M Y H:i') }}</td>
                        <td class="px-4 py-2.5 text-slate-800">{{ $movement->product->name ?? 'Deleted product' }}</td>
                        <td class="px-4 py-2.5">
                            <span class="inline-flex rounded px-2 py-0.5 text-xs font-medium ring-1 {{ $movement->type === 'in' ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : ($movement->type === 'out' ? 'bg-rose-50 text-rose-700 ring-rose-200' : 'bg-amber-50 text-amber-700 ring-amber-200') }}">
                                {{ $movement->type }}
                            </span>
                        </td>
                        <td class="px-4 py-2.5 text-right font-medium {{ $movement->qty >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                            {{ $movement->qty > 0 ? '+' : '' }}{{ $movement->qty }}
                        </td>
                        <td class="px-4 py-2.5 text-right text-slate-500">{{ $movement->stock_before }}</td>
                        <td class="px-4 py-2.5 text-right text-slate-700">{{ $movement->stock_after }}</td>
                        <td class="px-4 py-2.5 text-slate-500">
                            {{ $movement->reference ?: '—' }}
                            @if ($movement->note)<span class="block text-xs text-slate-400">{{ $movement->note }}</span>@endif
                        </td>
                        <td class="px-4 py-2.5 text-slate-500">{{ $movement->user->name ?? 'system' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-4 py-10 text-center text-slate-400">No stock movements recorded.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $movements->links() }}</div>
@endsection
