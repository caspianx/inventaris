<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemMasterBulkSelectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_items_index_shows_bulk_selection_controls_for_printing(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin-bulk@example.com',
            'role' => 'admin',
        ]);

        $response = $this->actingAs($user)->get(route('items.index'));

        $response->assertOk();
        $response->assertSee('id="select-all-items"', false);
        $response->assertSee('Pilih Semua', false);
        $response->assertSee('id="print-selected-btn"', false);
        $response->assertSee('item-checkbox-visible', false);
    }

    public function test_create_page_shows_sku_mode_selection_for_existing_or_generated_sku(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'name' => 'Admin SKU User',
            'email' => 'admin-sku@example.com',
            'role' => 'admin',
        ]);

        $response = $this->actingAs($user)->get(route('items.create'));

        $response->assertOk();
        $response->assertSee('Apakah SKU sudah ada?', false);
        $response->assertSee('name="sku_mode"', false);
        $response->assertSee('value="existing"', false);
        $response->assertSee('value="generated"', false);
    }

    public function test_store_fails_when_manual_sku_conflicts_with_existing_item(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'name' => 'Admin SKU User',
            'email' => 'admin-sku-conflict@example.com',
            'role' => 'admin',
        ]);

        Item::create([
            'sku' => 'BRG-00001',
            'name' => 'Sample Item',
            'unit' => 'pcs',
            'purchase_price' => 10000,
            'selling_price' => 12000,
            'min_stock' => 0,
            'current_stock' => 10,
        ]);

        $response = $this->actingAs($user)->post(route('items.store'), [
            'sku_mode' => 'existing',
            'sku' => 'BRG-00001',
            'name' => 'Barang Baru',
            'unit' => 'pcs',
            'purchase_price' => 5000,
            'selling_price' => 7000,
            'min_stock' => 0,
            'current_stock' => 5,
        ]);

        $response->assertSessionHasErrors('sku');
    }

    public function test_store_generates_sku_when_selected_generated_mode(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'name' => 'Admin SKU User',
            'email' => 'admin-sku-generate@example.com',
            'role' => 'admin',
        ]);

        $response = $this->actingAs($user)->post(route('items.store'), [
            'sku_mode' => 'generated',
            'sku' => '',
            'name' => 'Barang Baru Generate',
            'unit' => 'pcs',
            'purchase_price' => 5000,
            'selling_price' => 7000,
            'min_stock' => 0,
            'current_stock' => 5,
        ]);

        $response->assertRedirect(route('items.index'));
        $this->assertDatabaseHas('items', ['name' => 'Barang Baru Generate']);
    }
}
