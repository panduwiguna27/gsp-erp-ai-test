@extends('layouts.app')

@php $editing = $supplier->exists; @endphp

@section('title', $editing ? 'Edit supplier' : 'New supplier')
@section('heading', $editing ? 'Edit supplier' : 'New supplier')

@section('actions')
    <a href="{{ route('suppliers.index') }}" class="text-sm text-slate-500 hover:text-slate-800">← Back</a>
@endsection

@section('content')
    @php $field = 'mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500'; @endphp

    <form method="POST" action="{{ $editing ? route('suppliers.update', $supplier) : route('suppliers.store') }}"
          class="max-w-xl rounded-xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
        @csrf
        @if ($editing) @method('PUT') @endif

        <div>
            <label class="block text-sm font-medium text-slate-700">Name *</label>
            <input name="name" value="{{ old('name', $supplier->name) }}" required maxlength="180" class="{{ $field }}">
        </div>
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="block text-sm font-medium text-slate-700">Phone</label>
                <input name="phone" value="{{ old('phone', $supplier->phone) }}" maxlength="40" class="{{ $field }}">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Email</label>
                <input type="email" name="email" value="{{ old('email', $supplier->email) }}" maxlength="120" class="{{ $field }}">
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700">Address</label>
            <textarea name="address" rows="2" maxlength="255" class="{{ $field }}">{{ old('address', $supplier->address) }}</textarea>
        </div>
        <label class="flex items-center gap-2 text-sm text-slate-700">
            <input type="checkbox" name="is_active" value="1" class="rounded border-slate-300" @checked(old('is_active', $supplier->is_active ?? true))>
            Active
        </label>

        <div class="flex items-center gap-3 pt-2">
            <button class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                {{ $editing ? 'Save changes' : 'Create supplier' }}
            </button>
            <a href="{{ route('suppliers.index') }}" class="text-sm text-slate-500 hover:text-slate-800">Cancel</a>
        </div>
    </form>
@endsection
