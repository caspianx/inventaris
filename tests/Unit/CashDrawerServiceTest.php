<?php

namespace Tests\Unit;

use App\Models\StoreSetting;
use App\Services\CashDrawerService;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CashDrawerServiceTest extends TestCase
{
    public function test_it_writes_a_log_file_when_cash_drawer_open_succeeds(): void
    {
        if (! Schema::hasTable('store_settings')) {
            Schema::create('store_settings', function ($table) {
                $table->id();
                $table->string('name')->nullable();
                $table->text('address')->nullable();
                $table->string('default_printer')->nullable();
                $table->boolean('auto_print_receipt')->default(false);
                $table->integer('receipt_copies')->default(1);
                $table->string('receipt_size')->nullable();
                $table->boolean('show_receipt_logo')->default(true);
                $table->string('cash_drawer_driver')->nullable();
                $table->string('cash_drawer_address')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('activity_logs')) {
            Schema::create('activity_logs', function ($table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('subject_type')->nullable();
                $table->unsignedBigInteger('subject_id')->nullable();
                $table->string('subject_name')->nullable();
                $table->string('action')->nullable();
                $table->json('changes')->nullable();
                $table->json('old_values')->nullable();
                $table->json('new_values')->nullable();
                $table->string('url')->nullable();
                $table->text('description')->nullable();
                $table->string('ip_address')->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamps();
            });
        }

        StoreSetting::query()->delete();

        $printsDir = storage_path('prints');
        if (is_dir($printsDir)) {
            File::deleteDirectory($printsDir);
        }
        File::makeDirectory($printsDir, 0755, true);

        $printerPath = tempnam(sys_get_temp_dir(), 'cashdrawer');
        $this->assertNotFalse($printerPath);

        StoreSetting::create([
            'name' => 'Test Store',
            'address' => 'Test Address',
            'default_printer' => 'POS-1',
            'auto_print_receipt' => false,
            'receipt_copies' => 1,
            'receipt_size' => '80mm',
            'show_receipt_logo' => false,
            'cash_drawer_driver' => 'printer',
            'cash_drawer_address' => $printerPath,
        ]);

        $sale = (object) [
            'id' => 123,
            'invoice_number' => 'TEST-001',
            'total' => 15000,
        ];

        $result = (new CashDrawerService())->open($sale);

        $this->assertTrue($result['success'] ?? false);

        $files = glob($printsDir.DIRECTORY_SEPARATOR.'cashdrawer_*.txt');
        $this->assertNotEmpty($files);

        $content = file_get_contents($files[0]);
        $this->assertStringContainsString('Cash Drawer', $content);
        $this->assertStringContainsString('TEST-001', $content);

        @unlink($printerPath);
    }
}
