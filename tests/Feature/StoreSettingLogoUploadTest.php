<?php

namespace Tests\Feature;

use App\Http\Controllers\StoreSettingController;
use App\Models\StoreSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class StoreSettingLogoUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_uploading_new_logo_replaces_existing_logo_file(): void
    {
        $logoDirectory = public_path('store-logos');
        File::deleteDirectory($logoDirectory);
        File::makeDirectory($logoDirectory, 0755, true);

        $storeSetting = StoreSetting::create([
            'name' => 'Toko Test',
            'address' => 'Alamat Test',
            'show_receipt_logo' => true,
        ]);

        $firstRequest = new Request();
        $firstRequest->files->set('logo', UploadedFile::fake()->image('first-logo.png', 200, 200));
        $firstRequest->setMethod('PUT');
        $firstRequest->request->add(['name' => 'Toko Test']);

        $controller = new StoreSettingController();
        $controller->update($firstRequest);

        $firstLogoPath = $storeSetting->fresh()->logo_path;
        $this->assertNotNull($firstLogoPath);
        $this->assertFileExists(public_path($firstLogoPath));

        $secondRequest = new Request();
        $secondRequest->files->set('logo', UploadedFile::fake()->image('second-logo.png', 200, 200));
        $secondRequest->setMethod('PUT');
        $secondRequest->request->add(['name' => 'Toko Test']);

        $controller->update($secondRequest);

        $secondLogoPath = $storeSetting->fresh()->logo_path;
        $this->assertNotNull($secondLogoPath);
        $this->assertFileExists(public_path($secondLogoPath));
        $this->assertSame($secondLogoPath, $firstLogoPath);

        $logoFiles = File::files($logoDirectory);
        $this->assertCount(1, $logoFiles);
        $this->assertSame('store-logo.' . pathinfo($logoFiles[0]->getFilename(), PATHINFO_EXTENSION), $logoFiles[0]->getFilename());
    }
}
