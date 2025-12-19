<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Secret;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Spatie\Permission\Models\Permission;

class MixedContentFeatureTest extends TestCase
{
    public function test_mixed_content_display()
    {
        Storage::fake('local');

        $user = User::factory()->create();
        if (!Permission::where('name', 'access ots')->exists()) {
            Permission::create(['name' => 'access ots']);
        }
        $user->givePermissionTo('access ots');

        $file = UploadedFile::fake()->create('mixed.txt', 100);
        $textSecret = 'This is a mixed secret';

        // Store mixed secret
        $response = $this->actingAs($user)->post(route('ots.store'), [
            'one_time' => '1',
            'secret' => $textSecret,
            'file' => $file,
        ]);

        $response->assertRedirect();
        $secret = Secret::latest()->first();
        
        // Visit the signed URL
        $signedUrl = session('signedUrl');
        $response = $this->get($signedUrl);
        
        $response->assertStatus(200);
        
        // Assert BOTH are present
        $response->assertSee($textSecret); // Note: View masks it, but raw HTML might contain it in JS variable or we check for masked version?
        // Actually the view passes $secret to JS: const secret = "This is a mixed secret";
        // So assertSee should find it.
        
        $response->assertSee('mixed.txt');
        $response->assertSee('/download');

        // Cleanup
        $secret->delete();
        $user->delete();
    }
}
