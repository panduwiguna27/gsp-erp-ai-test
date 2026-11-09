@extends('layouts.app')

@section('title', 'New Sales Order')
@section('heading', 'New sales order')

@section('actions')
    <a href="{{ route('sales.index') }}" class="text-sm text-slate-500 hover:text-slate-800">← Back</a>
@endsection

@section('content')
    @php $field = 'mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500'; @endphp

    <form method="POST" action="{{ route('sales.store') }}" class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        @csrf
        <div class="grid gap-4 sm:grid-cols-3">
            <div>
                <label class="block text-sm font-medium text-slate-700">Customer *</label>
                <select name="customer_id" required class="{{ $field }}">
                    <option value="">— select —</option>
                    @foreach ($customers as $partner)
                        <option value="{{ $partner->id }}" @selected(old('customer_id') == $partner->id)>{{ $partner->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Order date</label>
                <input type="date" name="order_date" value="{{ old('order_date', now()->toDateString()) }}" class="{{ $field }}">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Notes</label>
                <input type="text" name="notes" value="{{ old('notes') }}" maxlength="1000" class="{{ $field }}">
            </div>
        </div>

        <h2 class="mt-6 mb-2 font-medium text-slate-900">Line items</h2>
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-y border-slate-200">
                <tr>
                    <th class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Product</th>
                    <th class="w-24 px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Qty</th>
                    <th class="w-40 px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Unit price</th>
                    <th class="w-32 px-3 py-2 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Subtotal</th>
                    <th class="w-10"></th>
                </tr>
            </thead>
            <tbody id="lines" class="divide-y divide-slate-100"></tbody>
            <tfoot>
                <tr class="border-t border-slate-200 font-semibold text-slate-800">
                    <td colspan="3" class="px-3 py-2 text-right">Total</td>
                    <td class="px-3 py-2 text-right" id="grand-total">0</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>

        <button type="button" id="add-line" class="mt-3 rounded-md border border-slate-300 bg-white px-3 py-1.5 text-sm hover:bg-slate-50">
            + Add line
        </button>

        <template id="line-template">
            <tr class="line-row">
                <td class="px-3 py-2">
                    <select name="items[__I__][product_id]" data-price-source
                            class="w-full rounded-md border border-slate-300 px-2 py-1.5 text-sm">
                        <option value="">— product —</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}" data-price="{{ (float) $product->sale_price }}">
                                {{ $product->sku }} — {{ $product->name }} (stock: {{ $product->stock_qty }})
                            </option>
                        @endforeach
                    </select>
                </td>
                <td class="px-3 py-2">
                    <input type="number" name="items[__I__][qty]" value="1" min="1" data-qty
                           class="w-full rounded-md border border-slate-300 px-2 py-1.5 text-sm">
                </td>
                <td class="px-3 py-2">
                    <input type="number" name="items[__I__][unit_price]" value="0" min="0" step="0.01" data-price
                           class="w-full rounded-md border border-slate-300 px-2 py-1.5 text-sm">
                </td>
                <td class="px-3 py-2 text-right text-slate-600" data-subtotal>0</td>
                <td class="px-3 py-2 text-right">
                    <button type="button" data-remove class="text-rose-600 hover:underline">×</button>
                </td>
            </tr>
        </template>

        <div class="mt-6 flex items-center gap-3">
            <button class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                Create sales order
            </button>
            <a href="{{ route('sales.index') }}" class="text-sm text-slate-500 hover:text-slate-800">Cancel</a>
        </div>
    </form>
@endsection

@include('partials.lines-script')
