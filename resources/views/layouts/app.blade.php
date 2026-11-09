<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') &middot; {{ config('app.name', 'ERP') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>📦</text></svg>">
</head>
<body class="bg-slate-100 text-slate-800 antialiased">
@php
    $nav = 'block px-3 py-2 rounded-md text-sm hover:bg-slate-800 hover:text-white transition';
    $active = 'bg-slate-700 text-white font-medium';
@endphp
<div class="min-h-screen flex">
    <aside class="w-60 shrink-0 bg-slate-900 text-slate-300 flex flex-col">
        <div class="px-4 py-5 text-base font-semibold text-white tracking-tight">
            📦 {{ config('app.name', 'ERP') }}
        </div>
        <nav class="px-3 space-y-1 flex-1">
            <a href="{{ route('dashboard') }}" class="{{ $nav }} {{ request()->routeIs('dashboard') ? $active : '' }}">Dashboard</a>
            <p class="px-3 pt-4 pb-1 text-[11px] uppercase tracking-wider text-slate-500">Inventory</p>
            <a href="{{ route('products.index') }}" class="{{ $nav }} {{ request()->routeIs('products.*') ? $active : '' }}">Products</a>
            <a href="{{ route('movements.index') }}" class="{{ $nav }} {{ request()->routeIs('movements.*') ? $active : '' }}">Stock Movements</a>
            <p class="px-3 pt-4 pb-1 text-[11px] uppercase tracking-wider text-slate-500">Trading</p>
            <a href="{{ route('sales.index') }}" class="{{ $nav }} {{ request()->routeIs('sales.*') ? $active : '' }}">Sales Orders</a>
            <a href="{{ route('purchases.index') }}" class="{{ $nav }} {{ request()->routeIs('purchases.*') ? $active : '' }}">Purchases</a>
            <p class="px-3 pt-4 pb-1 text-[11px] uppercase tracking-wider text-slate-500">Contacts</p>
            <a href="{{ route('customers.index') }}" class="{{ $nav }} {{ request()->routeIs('customers.*') ? $active : '' }}">Customers</a>
            <a href="{{ route('suppliers.index') }}" class="{{ $nav }} {{ request()->routeIs('suppliers.*') ? $active : '' }}">Suppliers</a>
        </nav>
        <div class="px-4 py-4 text-xs text-slate-400 border-t border-slate-800">
            <div class="truncate">{{ auth()->user()->name ?? 'Guest' }}</div>
            <form method="POST" action="{{ route('logout') }}" class="mt-2">
                @csrf
                <button type="submit" class="text-slate-200 hover:text-white underline">Sign out</button>
            </form>
        </div>
    </aside>

    <main class="flex-1 p-6 lg:p-8 overflow-x-auto">
        @if (session('success'))
            <div class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="mb-5 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                {{ session('error') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="mb-5 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                <p class="font-medium mb-1">Please fix the following:</p>
                <ul class="list-disc pl-5 space-y-0.5">
                    @foreach ($errors->all() as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <header class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-2xl font-semibold text-slate-900">@yield('heading', 'Dashboard')</h1>
            <div class="flex items-center gap-2">@yield('actions')</div>
        </header>

        @yield('content')
    </main>
</div>
@stack('scripts')
</body>
</html>
