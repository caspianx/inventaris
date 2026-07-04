<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\PurchaseOrder;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $totalItems = Item::count();
        $lowStockItems = Item::lowStock()->with('category')->orderBy('current_stock')->take(10)->get();
        $lowStockCount = Item::lowStock()->count();
        $totalStockValue = Item::selectRaw('SUM(current_stock * purchase_price) as total')->value('total') ?? 0;
        $totalSuppliers = DB::table('suppliers')->count();
        $pendingPOs = PurchaseOrder::whereIn('status', ['draft', 'ordered'])->count();

        $recentMovements = StockMovement::with(['item', 'user'])
            ->latest()
            ->take(8)
            ->get();

        $monthlyMovements = StockMovement::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, type, SUM(quantity) as total")
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month', 'type')
            ->orderBy('month')
            ->get()
            ->groupBy('month');

        return view('dashboard', compact(
            'totalItems',
            'lowStockItems',
            'lowStockCount',
            'totalStockValue',
            'totalSuppliers',
            'pendingPOs',
            'recentMovements',
            'monthlyMovements'
        ));
    }
}
