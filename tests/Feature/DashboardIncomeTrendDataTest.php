<?php

namespace Tests\Feature;

use App\Http\Controllers\DashboardController;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class DashboardIncomeTrendDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_income_trend_endpoint_returns_series_data(): void
    {
        Route::middleware('web')->get('/test-income-trend-data', [DashboardController::class, 'incomeTrendData']);

        $user = User::factory()->create();

        Sale::create([
            'invoice_number' => 'INV-001',
            'user_id' => $user->id,
            'subtotal' => 150000,
            'discount' => 0,
            'total' => 150000,
            'payment_method' => 'cash',
            'paid_amount' => 150000,
            'change_amount' => 0,
            'notes' => 'Test sale',
        ]);

        $response = $this->getJson('/test-income-trend-data');

        $response->assertOk();
        $response->assertJsonStructure([
            'daily' => ['series' => [['label', 'value']], 'currentValue', 'previousValue', 'delta', 'deltaPercent', 'maxValue'],
            'monthly' => ['series' => [['label', 'value']], 'currentValue', 'previousValue', 'delta', 'deltaPercent', 'maxValue'],
            'yearly' => ['series' => [['label', 'value']], 'currentValue', 'previousValue', 'delta', 'deltaPercent', 'maxValue'],
        ]);
        $this->assertEquals(150000.0, $response->json('daily.currentValue'));
    }
}
