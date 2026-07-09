<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ReportImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_importing_exported_items_does_not_misread_selling_price_as_stock(): void
    {
        $user = User::factory()->create();

        $this->withoutMiddleware();

        $csv = "SKU,Nama,Kategori,Satuan,Harga Beli,Harga Jual,Stok Min,Deskripsi\n";
        $csv .= "BRG-00001,Test Item,Kategori,pcs,10000,20000,2,Test description\n";

        $file = UploadedFile::fake()->createWithContent('items.csv', $csv, 'text/csv');

        $response = $this->actingAs($user)->post(route('reports.import', ['type' => 'items']), [
            'file' => $file,
        ]);

        $response->assertRedirect(route('reports.index'));

        $this->assertDatabaseHas('items', [
            'sku' => 'BRG-00001',
            'name' => 'Test Item',
            'current_stock' => 0,
        ]);
    }
}
