<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\SalesOrder;
use App\Models\StockMovement;
use App\Models\Supplier;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'products' => Product::count(),
            'low_stock' => Product::lowStock()->count(),
            'stock_value' => (float) Product::all()->sum(fn (Product $p) => $p->stockValue()),
            'customers' => Customer::count(),
            'suppliers' => Supplier::count(),
            'sales_total' => (float) SalesOrder::whereNotIn('status', ['cancelled'])->sum('total'),
            'purchases_total' => (float) PurchaseOrder::whereNotIn('status', ['cancelled'])->sum('total'),
            'open_sales' => SalesOrder::whereNotIn('status', ['shipped', 'cancelled'])->count(),
        ];

        $lowStockProducts = Product::lowStock()->orderBy('stock_qty')->limit(8)->get();
        $recentMovements = StockMovement::with('product')->latest()->limit(10)->get();
        $recentOrders = SalesOrder::with('customer')->latest()->limit(5)->get();

        return view('dashboard', compact('stats', 'lowStockProducts', 'recentMovements', 'recentOrders'));
    }
}
