<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Item;
use App\Models\PurchaseOrder;
use App\Models\Sale;
use App\Models\StockMovement;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $totalItems = Item::count();
        $lowStockItems = Item::lowStock()->with('category')->orderBy('current_stock')->take(10)->get();
        $lowStockCount = Item::lowStock()->count();
        $totalStockValue = (float) Item::query()
            ->selectRaw('SUM(COALESCE(current_stock, 0) * COALESCE(purchase_price, 0)) as total')
            ->value('total') ?? 0;
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

        $stockCategoryChart = Item::with('category')
            ->select('id', 'category_id', 'current_stock')
            ->get()
            ->groupBy(fn ($item) => $item->category?->name ?? 'Tanpa Kategori')
            ->map(function ($items, $name) {
                $totalStock = $items->sum('current_stock');
                return [
                    'label' => $name,
                    'value' => $items->count(),
                    'stock' => (int) $totalStock,
                ];
            })
            ->sortByDesc('value')
            ->take(6)
            ->values();

        $now = Carbon::now();
        $stockMovementChart = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = $now->copy()->subMonths($i);
            $monthKey = $month->format('Y-m');
            $monthData = $monthlyMovements->get($monthKey, collect());
            $in = (float) ($monthData->firstWhere('type', 'in')?->total ?? 0);
            $out = (float) ($monthData->firstWhere('type', 'out')?->total ?? 0);

            $stockMovementChart[] = [
                'label' => $month->translatedFormat('M Y'),
                'in' => $in,
                'out' => $out,
            ];
        }

        $poStatusChart = PurchaseOrder::select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->orderByDesc('total')
            ->get()
            ->map(function ($row) {
                $label = match ($row->status) {
                    'draft' => 'Draft',
                    'ordered' => 'Dipesan',
                    'received' => 'Diterima',
                    'cancelled' => 'Dibatalkan',
                    default => ucfirst($row->status),
                };

                return [
                    'label' => $label,
                    'value' => (int) $row->total,
                ];
            });

        $dailyIncome = $this->buildIncomeSeries('daily');
        $monthlyIncome = $this->buildIncomeSeries('monthly');
        $yearlyIncome = $this->buildIncomeSeries('yearly');

        $topItemsSold = $this->getTopItemsSold(10);
        $profitLossSummary = $this->getProfitLossSummary();
        $marginByCategory = $this->getMarginByCategory();

        return view('dashboard', compact(
            'totalItems',
            'lowStockItems',
            'lowStockCount',
            'totalStockValue',
            'totalSuppliers',
            'pendingPOs',
            'recentMovements',
            'monthlyMovements',
            'stockCategoryChart',
            'stockMovementChart',
            'poStatusChart',
            'dailyIncome',
            'monthlyIncome',
            'yearlyIncome',
            'topItemsSold',
            'profitLossSummary',
            'marginByCategory'
        ));
    }

    public function incomeTrendData()
    {
        return response()->json([
            'daily' => $this->buildIncomeSeries('daily'),
            'monthly' => $this->buildIncomeSeries('monthly'),
            'yearly' => $this->buildIncomeSeries('yearly'),
        ]);
    }

    private function buildIncomeSeries(string $type): array
    {
        $now = Carbon::now();
        $series = [];

        if ($type === 'daily') {
            for ($i = 6; $i >= 0; $i--) {
                $date = $now->copy()->subDays($i);
                $series[] = [
                    'label' => $date->translatedFormat('d M'),
                    'value' => (float) Sale::whereDate('created_at', $date->toDateString())->sum('total'),
                ];
            }
        } elseif ($type === 'monthly') {
            for ($i = 5; $i >= 0; $i--) {
                $month = $now->copy()->subMonths($i);
                $series[] = [
                    'label' => $month->translatedFormat('M Y'),
                    'value' => (float) Sale::whereYear('created_at', $month->year)
                        ->whereMonth('created_at', $month->month)
                        ->sum('total'),
                ];
            }
        } else {
            for ($i = 4; $i >= 0; $i--) {
                $year = $now->copy()->subYears($i);
                $series[] = [
                    'label' => (string) $year->year,
                    'value' => (float) Sale::whereYear('created_at', $year->year)->sum('total'),
                ];
            }
        }

        $maxValue = ! empty($series) ? max(array_column($series, 'value')) : 0;
        $currentValue = $series[count($series) - 1]['value'] ?? 0;
        $previousValue = $series[count($series) - 2]['value'] ?? 0;

        $delta = $currentValue - $previousValue;
        $deltaPercent = $previousValue > 0 ? ($delta / $previousValue) * 100 : ($currentValue > 0 ? 100 : 0);

        return [
            'series' => $series,
            'currentValue' => $currentValue,
            'previousValue' => $previousValue,
            'delta' => $delta,
            'deltaPercent' => $deltaPercent,
            'maxValue' => $maxValue,
        ];
    }

    private function getTopItemsSold(int $limit = 10): array
    {
        return \App\Models\SaleItem::select('item_id', 'item_name', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(subtotal) as total_sales'))
            ->groupBy('item_id', 'item_name')
            ->orderByDesc('total_qty')
            ->take($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->item_name,
                    'quantity' => (int) $item->total_qty,
                    'sales' => (float) $item->total_sales,
                ];
            })
            ->toArray();
    }

    private function getProfitLossSummary(): array
    {
        $sales = Sale::with(['items.item'])->get();

        $totalRevenue = $sales->sum('total');
        $totalCost = $sales->sum(function ($sale) {
            return $sale->items->sum(function ($item) {
                return ($item->item?->purchase_price ?? 0) * $item->quantity;
            });
        });
        $totalProfit = $totalRevenue - $totalCost;
        $profitMargin = $totalRevenue > 0 ? ($totalProfit / $totalRevenue) * 100 : 0;

        return [
            'totalRevenue' => (float) $totalRevenue,
            'totalCost' => (float) $totalCost,
            'totalProfit' => (float) $totalProfit,
            'profitMargin' => (float) $profitMargin,
        ];
    }

    private function getMarginByCategory(): array
    {
        $categories = Category::all();
        $result = [];

        foreach ($categories as $category) {
            $saleItems = \App\Models\SaleItem::whereHas('item', function ($query) use ($category) {
                $query->where('category_id', $category->id);
            })
                ->with('item')
                ->get();

            if ($saleItems->isEmpty()) {
                continue;
            }

            $totalSales = $saleItems->sum('subtotal');
            $totalCost = $saleItems->sum(function ($item) {
                return ($item->item?->purchase_price ?? 0) * $item->quantity;
            });
            $profit = $totalSales - $totalCost;
            $margin = $totalSales > 0 ? ($profit / $totalSales) * 100 : 0;

            $result[] = [
                'category' => $category->name,
                'sales' => (float) $totalSales,
                'cost' => (float) $totalCost,
                'profit' => (float) $profit,
                'margin' => (float) $margin,
                'items_count' => $saleItems->groupBy('item_id')->count(),
            ];
        }

        usort($result, fn ($a, $b) => $b['sales'] <=> $a['sales']);

        return $result;
    }
}
