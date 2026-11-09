@extends('layouts.app')

@section('title', 'Purchases')
@section('heading', 'Purchase Orders')

@section('actions')
    <a href="{{ route('purchases.create') }}" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
        + New purchase order
    </a>
@endsection

@section('content')
    @php
        $money = fn ($v) => number_format((float) $v, 0, ',', '.');
        $badge = [
            'draft' => 'bg-slate-100 text-slate-600 ring-slate-200',
            'received' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
            'cancelled' => 'bg-rose-50 text-rose-700 ring-rose-200',
        ];
    @endphp

    <div class="mb-4 flex gap-2 text-sm">
        <a href="{{ route('purchases.index') }}" class="rounded-md border px-3 py-1.5 {{ request('status') ? 'border-slate-300 bg-white' : 'border-indigo-300 bg-indigo-50 text-indigo-700' }}">All</a>
        @foreach (['draft', 'received'] as $status)
            <a href="{{ route('purchases.index', ['status' => $status]) }}"
               class="rounded-md border px-3 py-1.5 {{ request('status') === $status ? 'border-indigo-300 bg-indigo-50 text-indigo-700' : 'border-slate-300 bg-white' }}">
                {{ ucfirst($status) }}
            </a>
        @endforeach
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Code</th>
                    <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Supplier</th>
                    <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Date</th>
                    <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Status</th>
                    <th class="px-4 py-2 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Total</th>
                    <th class="px-4 py-2 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($orders as $order)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-2.5 font-mono text-xs">
                            <a href="{{ route('purchases.show', $order) }}" class="text-indigo-600 hover:underline">{{ $order->code }}</a>
                        </td>
                        <td class="px-4 py-2.5 text-slate-700">{{ $order->supplier->name ?? '—' }}</td>
                        <td class="px-4 py-2.5 text-slate-500">{{ $order->order_date?->format('d M Y') ?: '—' }}</td>
                        <td class="px-4 py-2.5">
                            <span class="inline-flex rounded px-2 py-0.5 text-xs font-medium ring-1 {{ $badge[$order->status] ?? $badge['draft'] }}">
                                {{ $order->status }}
                            </span>
                        </td>
                        <td class="px-4 py-2.5 text-right text-slate-700">{{ $money($order->total) }}</td>
                        <td class="px-4 py-2.5 text-right whitespace-nowrap">
                            <a href="{{ route('purchases.show', $order) }}" class="text-indigo-600 hover:underline">View</a>
                            <form method="POST" action="{{ route('purchases.destroy', $order) }}" class="inline"
                                  onsubmit="return confirm('Delete {{ $order->code }}?')">
                                @csrf @method('DELETE')
                                <button class="ml-2 text-rose-600 hover:underline">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-10 text-center text-slate-400">No purchase orders yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $orders->links() }}</div>
@endsection
