<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Secret;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class FileSecretFeatureTest extends TestCase
{
    // use RefreshDatabase; // Avoid wiping existing DB if not configured

    public function test_user_can_upload_file_secret()
    {
        Storage::fake('local');

        $user = User::factory()->create();
        
        // Mock permission if needed
        // Assuming 'access ots' permission exists or we need to create it
        // If the DB has it, we can assign. If not, we might fail.
        // Let's try to create permission if not exists
        if (!Permission::where('name', 'access ots')->exists()) {
            Permission::create(['name' => 'access ots']);
        }
        $user->givePermissionTo('access ots');

        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->actingAs($user)->post(route('ots.store'), [
            'one_time' => '1',
            'file' => $file,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('signedUrl');

        // Assert file stored
        $secret = Secret::latest()->first();
        $this->assertNotNull($secret->file_path);
        Storage::disk('local')->assertExists($secret->file_path);

        // Verify Show Page has Download Link
        $signedUrl = session('signedUrl');
        $response = $this->get($signedUrl);
        $response->assertStatus(200);
        $response->assertSee('Download File');
        
        // Extract download URL from response content (simple regex or just check if it exists)
        // Since we can't easily crawl with Feature test, we can manually generate the expected signed URL to verify logic
        // But better is to just check if the view has a link that looks like a signed URL
        $response->assertSee('/download');
        $response->assertSee('signature=');

        // Clean up
        $secret->delete();
        $user->delete();
    }
}
