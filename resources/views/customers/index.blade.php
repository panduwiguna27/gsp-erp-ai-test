@extends('layouts.app')

@section('title', 'Customers')
@section('heading', 'Customers')

@section('actions')
    <a href="{{ route('customers.create') }}" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
        + New customer
    </a>
@endsection

@section('content')
    <form method="GET" action="{{ route('customers.index') }}" class="mb-4 flex items-center gap-2">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search customer"
               class="rounded-md border border-slate-300 px-3 py-1.5 text-sm w-64">
        <button class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-sm hover:bg-slate-50">Search</button>
    </form>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Name</th>
                    <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Contact</th>
                    <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Address</th>
                    <th class="px-4 py-2 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Orders</th>
                    <th class="px-4 py-2 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($customers as $customer)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-2.5 font-medium text-slate-800">{{ $customer->name }}</td>
                        <td class="px-4 py-2.5 text-slate-500">
                            {{ $customer->phone ?: '—' }}
                            @if ($customer->email)<span class="block text-xs text-slate-400">{{ $customer->email }}</span>@endif
                        </td>
                        <td class="px-4 py-2.5 text-slate-500">{{ $customer->address ?: '—' }}</td>
                        <td class="px-4 py-2.5 text-right text-slate-500">{{ $customer->salesOrders()->count() }}</td>
                        <td class="px-4 py-2.5 text-right whitespace-nowrap">
                            <a href="{{ route('customers.edit', $customer) }}" class="text-indigo-600 hover:underline">Edit</a>
                            <form method="POST" action="{{ route('customers.destroy', $customer) }}" class="inline"
                                  onsubmit="return confirm('Delete this customer?')">
                                @csrf @method('DELETE')
                                <button class="ml-2 text-rose-600 hover:underline">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-10 text-center text-slate-400">No customers yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $customers->links() }}</div>
@endsection
