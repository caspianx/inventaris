<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaleReceiptPromptTest extends TestCase
{
    use RefreshDatabase;

    public function test_sale_can_request_receipt_preview_after_payment(): void
    {
        $user = User::factory()->create();

        $item = Item::create([
            'name' => 'Test Item',
            'sku' => 'TEST-001',
            'category_id' => null,
            'supplier_id' => null,
            'buying_price' => 5000,
            'selling_price' => 10000,
            'current_stock' => 10,
            'unit' => 'pcs',
        ]);

        $this->withoutMiddleware();

        $response = $this->actingAs($user)->post(route('sales.store'), [
            'payment_method' => 'cash',
            'paid_amount' => 10000,
            'discount' => 0,
            'notes' => null,
            'items' => [
                [
                    'item_id' => $item->id,
                    'quantity' => 1,
                ],
            ],
            'print_receipt' => true,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('pos_print_receipt', true);
        $response->assertSessionHas('pos_sale_id');
        $this->assertDatabaseHas('sales', [
            'id' => session('pos_sale_id'),
        ]);
        $this->assertDatabaseHas('sale_items', [
            'sale_id' => session('pos_sale_id'),
            'item_id' => $item->id,
        ]);
        $this->assertDatabaseHas('print_files', [
            'sale_id' => session('pos_sale_id'),
        ]);
    }
}
