@extends('layouts.app')

@section('title', 'Products')
@section('heading', 'Products')

@section('actions')
    <a href="{{ route('products.create') }}" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
        + New product
    </a>
@endsection

@section('content')
    @php
        $money = fn ($v) => number_format((float) $v, 0, ',', '.');
        $th = 'px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-500';
        $td = 'px-4 py-2.5';
    @endphp

    <form method="GET" action="{{ route('products.index') }}" class="mb-4 flex flex-wrap items-center gap-2">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search SKU or name"
               class="rounded-md border border-slate-300 px-3 py-1.5 text-sm w-64">
        <button class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-sm hover:bg-slate-50">Search</button>
        @if (request('low'))
            <a href="{{ route('products.index') }}" class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-sm">Show all</a>
        @else
            <a href="{{ route('products.index', ['low' => 1]) }}" class="rounded-md border border-rose-200 bg-rose-50 px-3 py-1.5 text-sm text-rose-700">Low stock only</a>
        @endif
    </form>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="{{ $th }}">SKU</th>
                    <th class="{{ $th }}">Product</th>
                    <th class="{{ $th }}">Category</th>
                    <th class="{{ $th }} text-right">Stock</th>
                    <th class="{{ $th }} text-right">Cost</th>
                    <th class="{{ $th }} text-right">Price</th>
                    <th class="{{ $th }} text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($products as $product)
                    <tr class="hover:bg-slate-50">
                        <td class="{{ $td }} font-mono text-xs text-slate-500">{{ $product->sku }}</td>
                        <td class="{{ $td }}">
                            <span class="font-medium text-slate-800">{{ $product->name }}</span>
                            @if (! $product->is_active)
                                <span class="ml-1 rounded bg-slate-100 px-1.5 py-0.5 text-[10px] text-slate-500">inactive</span>
                            @endif
                        </td>
                        <td class="{{ $td }} text-slate-500">{{ $product->category ?: '—' }}</td>
                        <td class="{{ $td }} text-right whitespace-nowrap">
                            <span class="inline-flex rounded px-2 py-0.5 text-xs font-medium ring-1 {{ $product->isLowStock() ? 'bg-rose-50 text-rose-700 ring-rose-200' : 'bg-emerald-50 text-emerald-700 ring-emerald-200' }}">
                                {{ $product->stock_qty }} {{ $product->unit }}
                            </span>
                            <span class="block text-[11px] text-slate-400">min {{ $product->reorder_level }}</span>
                        </td>
                        <td class="{{ $td }} text-right text-slate-600">{{ $money($product->cost_price) }}</td>
                        <td class="{{ $td }} text-right text-slate-600">{{ $money($product->sale_price) }}</td>
                        <td class="{{ $td }} text-right whitespace-nowrap">
                            <a href="{{ route('products.edit', $product) }}" class="text-indigo-600 hover:underline">Edit</a>
                            <form method="POST" action="{{ route('products.destroy', $product) }}" class="inline"
                                  onsubmit="return confirm('Archive {{ $product->sku }}?')">
                                @csrf @method('DELETE')
                                <button class="ml-2 text-rose-600 hover:underline">Archive</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-10 text-center text-slate-400">No products yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $products->links() }}</div>

    <div class="mt-8 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="font-medium text-slate-900 mb-1">Manual stock adjustment</h2>
        <p class="text-sm text-slate-500 mb-4">Written to the ledger with before/after balances.</p>
        <form method="POST" action="{{ route('products.adjust', ['product' => 1]) }}" id="adjust-form"
              class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5 items-end">
            @csrf
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Product</label>
                <select onchange="document.getElementById('adjust-form').action = '{{ url('products') }}/' + this.value + '/adjust'"
                        class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                    @foreach ($products as $product)
                        <option value="{{ $product->id }}">{{ $product->sku }} — {{ $product->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Direction</label>
                <select name="type" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                    <option value="in">Stock in (+)</option>
                    <option value="out">Stock out (−)</option>
                    <option value="adjustment">Correction (signed)</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Quantity</label>
                <input type="number" name="qty" value="1" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Note</label>
                <input type="text" name="note" placeholder="e.g. physical count" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
            </div>
            <button class="rounded-md bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-900">Apply</button>
        </form>
    </div>
@endsection
