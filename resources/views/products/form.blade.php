@extends('layouts.app')

@php $editing = $product->exists; @endphp

@section('title', $editing ? 'Edit product' : 'New product')
@section('heading', $editing ? "Edit {$product->sku}" : 'New product')

@section('actions')
    <a href="{{ route('products.index') }}" class="text-sm text-slate-500 hover:text-slate-800">← Back to products</a>
@endsection

@section('content')
    @php $field = 'mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500'; @endphp

    <form method="POST" action="{{ $editing ? route('products.update', $product) : route('products.store') }}"
          class="max-w-2xl rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        @csrf
        @if ($editing)
            @method('PUT')
        @endif

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="block text-sm font-medium text-slate-700">SKU *</label>
                <input name="sku" value="{{ old('sku', $product->sku) }}" required maxlength="60" class="{{ $field }}">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Name *</label>
                <input name="name" value="{{ old('name', $product->name) }}" required maxlength="180" class="{{ $field }}">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Category</label>
                <input name="category" value="{{ old('category', $product->category) }}" maxlength="80" class="{{ $field }}">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Unit *</label>
                <input name="unit" value="{{ old('unit', $product->unit ?: 'pcs') }}" required maxlength="20" class="{{ $field }}">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Cost price *</label>
                <input type="number" step="0.01" min="0" name="cost_price" value="{{ old('cost_price', $product->cost_price) }}" required class="{{ $field }}">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Sale price *</label>
                <input type="number" step="0.01" min="0" name="sale_price" value="{{ old('sale_price', $product->sale_price) }}" required class="{{ $field }}">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Reorder level *</label>
                <input type="number" min="0" name="reorder_level" value="{{ old('reorder_level', $product->reorder_level ?? 5) }}" required class="{{ $field }}">
            </div>

            @unless ($editing)
                <div>
                    <label class="block text-sm font-medium text-slate-700">Opening stock *</label>
                    <input type="number" min="0" name="stock_qty" value="{{ old('stock_qty', $product->stock_qty ?? 0) }}" required class="{{ $field }}">
                </div>
            @else
                <div class="rounded-lg bg-slate-50 border border-slate-200 p-3 text-sm">
                    <p class="text-slate-500">Current stock</p>
                    <p class="font-semibold text-slate-800">{{ $product->stock_qty }} {{ $product->unit }}</p>
                    <p class="text-xs text-slate-400 mt-1">Edit stock via adjustments, purchases or sales so the ledger stays accurate.</p>
                </div>
            @endunless
        </div>

        <label class="mt-5 flex items-center gap-2 text-sm text-slate-700">
            <input type="checkbox" name="is_active" value="1" class="rounded border-slate-300"
                   @checked(old('is_active', $product->is_active ?? true))>
            Active (available for orders)
        </label>

        <div class="mt-6 flex items-center gap-3">
            <button class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                {{ $editing ? 'Save changes' : 'Create product' }}
            </button>
            <a href="{{ route('products.index') }}" class="text-sm text-slate-500 hover:text-slate-800">Cancel</a>
        </div>
    </form>
@endsection
