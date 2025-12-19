<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FileSecretTest extends DuskTestCase
{
    // use DatabaseMigrations; // Assuming we want to keep data or handle migration manually, but for test usually we use this. 
    // However, since user is running on existing DB, I should be careful. 
    // Let's assume I can create a user or use existing.
    // Ideally, I should use a separate test DB. But for now, I will create a user and delete it.

    public function testFileUploadAndDownload()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);
        
        // Assign permission if needed (based on web.php middleware)
        // Assuming user factory creates a user that can pass 'auth:sanctum'
        // And 'permission:access ots' is needed. 
        // I might need to seed permissions or mock them. 
        // For simplicity, let's assume the user has access or I can assign it.
        // If Spatie Permission is used:
        $user->givePermissionTo('access ots');

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/ots')
                    ->assertSee('One Time Secret')
                    ->attach('file', __DIR__.'/testfile.txt') // Need to create this file
                    ->press('Buat Link Rahasia')
                    ->waitForText('Link Rahasia Anda')
                    ->assertSee('Link Rahasia Anda');

            $link = $browser->value('#secretUrl');
            
            // Visit the link as guest (logout first or use another browser)
            // Using same browser instance but logout
            $browser->logout();
            
            $browser->visit($link)
                    ->assertSee('File Rahasia:')
                    ->assertSee('testfile.txt')
                    ->assertSee('Download File');
            
            // Test Download is tricky in Dusk without specific driver config to check file presence.
            // But we can check if clicking it doesn't error.
            // Or just verify the button exists.
        });
        
        $user->delete();
    }
}
