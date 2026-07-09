<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterDataBulkSelectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_categories_index_shows_bulk_selection_controls(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin-categories@example.com',
            'role' => 'admin',
        ]);

        Category::create(['name' => 'Elektronik']);

        $response = $this->actingAs($user)->get(route('categories.index'));

        $response->assertOk();
        $response->assertSee('id="select-all-categories"', false);
        $response->assertSee('Pilih Semua', false);
        $response->assertSee('id="bulk-delete-btn"', false);
        $response->assertSee('name="selected_items[]"', false);
        $response->assertSee('Total 1 kategori', false);
    }

    public function test_categories_bulk_delete_removes_selected_categories(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'name' => 'Admin User Two',
            'email' => 'admin-categories-delete@example.com',
            'role' => 'admin',
        ]);

        $first = Category::create(['name' => 'Pakaian']);
        $second = Category::create(['name' => 'Makanan']);

        $response = $this->actingAs($user)->post(route('categories.bulk-delete'), [
            'selected_items' => [$first->id, $second->id],
        ]);

        $response->assertRedirect(route('categories.index'));
        $this->assertDatabaseMissing('categories', ['id' => $first->id]);
        $this->assertDatabaseMissing('categories', ['id' => $second->id]);
    }

    public function test_suppliers_index_shows_bulk_selection_controls(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'name' => 'Admin Supplier',
            'email' => 'admin-suppliers@example.com',
            'role' => 'admin',
        ]);

        Supplier::create(['name' => 'PT Demo']);

        $response = $this->actingAs($user)->get(route('suppliers.index'));

        $response->assertOk();
        $response->assertSee('id="select-all-suppliers"', false);
        $response->assertSee('Pilih Semua', false);
        $response->assertSee('id="bulk-delete-btn"', false);
        $response->assertSee('name="selected_items[]"', false);
        $response->assertSee('Total 1 supplier', false);
    }

    public function test_suppliers_bulk_delete_removes_selected_suppliers(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'name' => 'Admin Supplier Two',
            'email' => 'admin-suppliers-delete@example.com',
            'role' => 'admin',
        ]);

        $first = Supplier::create(['name' => 'PT Satu']);
        $second = Supplier::create(['name' => 'PT Dua']);

        $response = $this->actingAs($user)->post(route('suppliers.bulk-delete'), [
            'selected_items' => [$first->id, $second->id],
        ]);

        $response->assertRedirect(route('suppliers.index'));
        $this->assertDatabaseMissing('suppliers', ['id' => $first->id]);
        $this->assertDatabaseMissing('suppliers', ['id' => $second->id]);
    }
}
