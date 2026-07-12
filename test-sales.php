<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Sale;
use Carbon\Carbon;

// Test today's sales
$today = Carbon::today();
$salesFromToday = Sale::whereDate('created_at', $today->toDateString())->get();
echo "Sales from today (" . $today->toDateString() . "): " . $salesFromToday->count() . " records\n";
echo "Total sales today: Rp " . number_format($salesFromToday->sum('total'), 0) . "\n\n";

// Show recent sales
echo "Recent sales:\n";
$recent = Sale::latest()->take(5)->get();
foreach ($recent as $sale) {
    echo "- " . $sale->created_at . " | Rp " . number_format($sale->total, 0) . "\n";
}

// Test the controller's buildIncomeSeries logic
echo "\n\nTesting buildIncomeSeries (daily):\n";
$now = Carbon::now();
$series = [];
for ($i = 6; $i >= 0; $i--) {
    $date = $now->copy()->subDays($i);
    $value = (float) Sale::whereDate('created_at', $date->toDateString())->sum('total');
    echo "- " . $date->format('d M Y') . ": Rp " . number_format($value, 0) . "\n";
    $series[] = ['label' => $date->translatedFormat('d M'), 'value' => $value];
}

$maxValue = !empty($series) ? max(array_column($series, 'value')) : 0;
$currentValue = $series[count($series) - 1]['value'] ?? 0;
$previousValue = $series[count($series) - 2]['value'] ?? 0;
$delta = $currentValue - $previousValue;
$deltaPercent = $previousValue > 0 ? ($delta / $previousValue) * 100 : ($currentValue > 0 ? 100 : 0);

echo "\nDaily Summary:\n";
echo "- Current (today): Rp " . number_format($currentValue, 0) . "\n";
echo "- Previous (yesterday): Rp " . number_format($previousValue, 0) . "\n";
echo "- Delta: " . number_format($delta, 0) . " (" . number_format($deltaPercent, 1) . "%)\n";
echo "- Max Value: Rp " . number_format($maxValue, 0) . "\n";
