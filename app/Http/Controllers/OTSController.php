<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Secret;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;
use Carbon\Carbon;

use Illuminate\Support\Facades\Http;

class OTSController extends Controller
{
    /**
     * Send message via local Node.js WhatsApp service
     */
    private function sendWhatsAppMessage($number, $message)
    {
        try {
            $response = Http::post('http://localhost:3001/send', [
                'number' => $number,
                'message' => $message,
            ]);
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Grafik jumlah pesan rahasia yang dikirim sesuai batasan waktu.
     */
    public function chart()
    {
        // Ambil data jumlah pesan rahasia berdasarkan batas waktu (expiry)
        $labels = ['Sekali lihat', '5 Menit', '1 Jam', '1 Hari'];
        $data = [
            Secret::where('one_time', true)->count(),
            Secret::where('one_time', false)->where('expires_at', '!=', null)->whereRaw('TIMESTAMPDIFF(MINUTE, created_at, expires_at) = 5')->count(),
            Secret::where('one_time', false)->where('expires_at', '!=', null)->whereRaw('TIMESTAMPDIFF(MINUTE, created_at, expires_at) = 60')->count(),
            Secret::where('one_time', false)->where('expires_at', '!=', null)->whereRaw('TIMESTAMPDIFF(MINUTE, created_at, expires_at) = 1440')->count(),
        ];
        return view('secret_chart', compact('labels', 'data'));
    }
    /**
     * Show the form for creating a new secret.
     */
    public function form(): View
    {
        return view('OTS_input');
    }

    /**
     * Store a newly created secret and generate signed URL.
     */
    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'secret' => 'nullable|string|max:10000',
            'file' => 'nullable|file|max:10240', // Max 10MB
            'one_time' => 'required|in:0,1',
            'expiry' => 'required_if:one_time,0|integer|in:5,60,1440',
            'whatsapp_number' => 'nullable|string|max:20',
        ]);

        if (!$request->input('secret') && !$request->hasFile('file')) {
            return redirect()->back()->withInput()->with('error', 'Please provide either text or a file.');
        }

        try {
            $isOneTime = $request->input('one_time') == 1;
            $expiresAt = $isOneTime ? null : now()->addMinutes($request->input('expiry'));

            $filePath = null;
            $originalName = null;
            $mimeType = null;
            $fileSize = null;

            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $filePath = $file->store('secrets'); // storage/app/private/secrets
                $originalName = $file->getClientOriginalName();
                $mimeType = $file->getMimeType();
                $fileSize = $file->getSize();
            }

            $secret = Secret::create([
                'text' => $request->input('secret'),
                'slug' => $this->generateUniqueSlug(),
                'expires_at' => $expiresAt,
                'user_id' => auth()->id(),
                'used' => false,
                'one_time' => $isOneTime,
                'file_path' => $filePath,
                'original_name' => $originalName,
                'mime_type' => $mimeType,
                'file_size' => $fileSize,
            ]);

            // Untuk sekali lihat, link tetap valid sangat lama (10 tahun), expired hanya jika sudah dibuka
            $signedUrl = URL::temporarySignedRoute(
                'ots.show',
                $isOneTime ? now()->addYears(10) : $expiresAt,
                ['slug' => $secret->slug]
            );

            // Send via WhatsApp if requested
            if ($request->has('send_whatsapp') && $request->input('whatsapp_number')) {
                $message = "Halo, ini link rahasia untuk Anda: " . $signedUrl;
                $sent = $this->sendWhatsAppMessage($request->input('whatsapp_number'), $message);
                if ($sent) {
                    session()->flash('success', 'Link pesan rahasia telah ter-generate dan dikirim via WhatsApp!');
                } else {
                    session()->flash('error', 'Link generated, but failed to send via WhatsApp. Please ensure the service is running.');
                }
            }
            
            return redirect()->route('ots.form')->with([
                'success' => session('success') ?? 'Link pesan rahasia telah ter-generate !',
                'signedUrl' => $signedUrl
            ]);
        } catch (\Exception $e) {
            return redirect()->route('ots.form')->withInput()->with('error', 'Failed to create secret. Please try again.');
        }
    }

    /**
     * Display the specified secret (one-time use, public, OTS_display view).
     */
    public function show(Request $request, string $slug): View
    {
        if (!$request->hasValidSignature()) {
            return view('OTS_display', [
                'expired' => true
            ]);
        }
        $secret = Secret::where('slug', $slug)->first();
        if (!$secret) {
            return view('OTS_display', [
                'expired' => true
            ]);
        }
        
        // Logic Expired/Used
        if ($secret->one_time) {
            if ($secret->used) {
                return view('OTS_display', [ 'expired' => true ]);
            }
            // Jika HANYA text, tandai used saat dibuka. Jika ada file, tandai used saat download.
            if (!$secret->file_path) {
                $secret->update([
                    'used' => true,
                    'viewed_at' => now()
                ]);
            }
        } else {
            if ($secret->expires_at && Carbon::parse($secret->expires_at)->isPast()) {
                return view('OTS_display', [ 'expired' => true ]);
            }
        }

        // Pastikan waktu yang dikirim ke view sudah diubah ke Asia/Jakarta
        $expires_at = $secret->expires_at ? Carbon::parse($secret->expires_at)->setTimezone('Asia/Jakarta') : null;
        
        // Generate Signed Download URL if file exists
        $downloadUrl = null;
        if ($secret->file_path) {
            // Use same expiration logic as the main link
            $expiration = $secret->one_time ? now()->addYears(10) : ($secret->expires_at ? Carbon::parse($secret->expires_at) : now()->addMinutes(30));
            
            $downloadUrl = URL::temporarySignedRoute(
                'ots.download',
                $expiration,
                ['slug' => $secret->slug]
            );
        }

        return view('OTS_display', [
            'secret' => $secret->text,
            'file_path' => $secret->file_path,
            'original_name' => $secret->original_name,
            'downloadUrl' => $downloadUrl,
            'expires_at' => $expires_at,
            'one_time' => $secret->one_time,
        ]);
    }

    public function download(Request $request, string $slug)
    {
        if (!$request->hasValidSignature()) {
            abort(403, 'Invalid signature.');
        }

        $secret = Secret::where('slug', $slug)->firstOrFail();

        // Check expiration
        if ($secret->one_time && $secret->used) {
            abort(410, 'Link expired.');
        }
        if (!$secret->one_time && $secret->expires_at && Carbon::parse($secret->expires_at)->isPast()) {
            abort(410, 'Link expired.');
        }

        if (!$secret->file_path || !\Illuminate\Support\Facades\Storage::exists($secret->file_path)) {
            abort(404, 'File not found.');
        }

        // Mark as used if one-time
        if ($secret->one_time) {
            $secret->update([
                'used' => true,
                'viewed_at' => now()
            ]);
        }

        if (ob_get_length()) {
            ob_end_clean();
        }

        return \Illuminate\Support\Facades\Storage::download($secret->file_path, $secret->original_name);
    }

    /**
     * Display user's secrets (for admin/management).
     */
    public function index(Request $request): View
    {
        $query = Secret::where('user_id', auth()->id());
        if ($request->has('status')) {
            switch ($request->input('status')) {
                case 'active':
                    $query->where('used', false)
                          ->where(function($q) {
                              $q->whereNull('expires_at')
                                ->orWhere('expires_at', '>', now());
                          });
                    break;
                case 'expired':
                    $query->where(function($q) {
                        $q->where('expires_at', '<=', now())
                          ->orWhere('used', true);
                    });
                    break;
                case 'used':
                    $query->where('used', true);
                    break;
            }
        }
        $secrets = $query->select(['id', 'slug', 'expires_at', 'used', 'viewed_at', 'created_at'])
                        ->orderBy('created_at', 'desc')
                        ->get()
                        ->map(function($secret) {
                            $secret->expires_at = $secret->expires_at ? Carbon::parse($secret->expires_at)->setTimezone('Asia/Jakarta') : null;
                            $secret->created_at = $secret->created_at ? Carbon::parse($secret->created_at)->setTimezone('Asia/Jakarta') : null;
                            $secret->viewed_at = $secret->viewed_at ? Carbon::parse($secret->viewed_at)->setTimezone('Asia/Jakarta') : null;
                            return $secret;
                        });
        return view('OTS', ['secrets' => $secrets]);
    }

    /**
     * Delete a specific secret.
     */
    public function destroy(Secret $secret): RedirectResponse
    {
        if ($secret->user_id !== auth()->id()) {
            return redirect()->route('ots.form')->with('error', 'Unauthorized access.');
        }
        $secret->delete();
        return redirect()->route('ots.form')->with('sukses', 'Pesan rahasia telah dihapus.');
    }

    /**
     * Clean up expired and used secrets.
     */
    public function cleanup(): RedirectResponse
    {
        $deleted = Secret::where('user_id', auth()->id())
                        ->where(function($query) {
                            $query->where('expires_at', '<=', now())
                                  ->orWhere('used', true);
                        })
                        ->delete();
        return redirect()->route('ots.form')->with('success', "Sukses Dihapus {$deleted} Pesan Rahasia Expired/Terpakai.");
    }

    /**
     * Show statistics for user's secrets.
     */
    public function stats(): View
    {
        $userId = auth()->id();
        $stats = [
            'total' => Secret::where('user_id', $userId)->count(),
            'active' => Secret::where('user_id', $userId)
                            ->where('used', false)
                            ->where(function($q) {
                                $q->whereNull('expires_at')
                                  ->orWhere('expires_at', '>', now());
                            })->count(),
            'used' => Secret::where('user_id', $userId)->where('used', true)->count(),
            'expired' => Secret::where('user_id', $userId)
                              ->where('expires_at', '<=', now())
                              ->where('used', false)
                              ->count(),
        ];
        return view('OTS', compact('stats'));
    }

    /**
     * Generate unique slug for secret.
     */
    private function generateUniqueSlug(): string
    {
        do {
            $slug = Str::random(32);
        } while (Secret::where('slug', $slug)->exists());

        return $slug;
    }

    /**
     * Check if secret exists and get basic info (without revealing content).
     */
    public function info(string $slug): View
    {
        $secret = Secret::where('slug', $slug)
                       ->select('slug', 'expires_at', 'used', 'viewed_at', 'created_at')
                       ->first();
        if (!$secret) {
            return view('OTS', [
                'error' => 'Pesan rahasia tidak ditemukan.'
            ]);
        }
        $status = 'active';
        if ($secret->used) {
            $status = 'used';
        } elseif ($secret->expires_at && Carbon::parse($secret->expires_at)->isPast()) {
            $status = 'expired';
        }
        return view('OTS', [
            'secret' => $secret,
            'status' => $status
        ]);
    }

    /**api */
     public function apiStore(Request $request)
    {
        $validated = $request->validate([
            'secret' => 'required|string|max:10000',
            'one_time' => 'required|boolean',
            'expiry' => 'required_if:one_time,false|integer|in:5,60,1440',
        ]);
        try {
            $isOneTime = (bool) $request->input('one_time');
            $expiresAt = $isOneTime ? null : now()->addMinutes($request->input('expiry'));
            $secret = Secret::create([
                'text' => $request->input('secret'),
                'slug' => $this->generateUniqueSlug(),
                'expires_at' => $expiresAt,
                'user_id' => $request->user() ? $request->user()->id : null,
                'used' => false,
                'one_time' => $isOneTime,
            ]);
            $signedUrl = URL::temporarySignedRoute(
                'ots.show',
                $isOneTime ? now()->addYears(10) : $expiresAt,
                ['slug' => $secret->slug]
            );
            return response()->json([
                'success' => true,
                'message' => 'Secret created successfully',
                'signed_url' => $signedUrl,
                'secret_id' => $secret->id,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create secret',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * REST API: Show a secret by slug (JSON response)
     */
    public function apiShow(Request $request, $slug)
    {
        $secret = Secret::where('slug', $slug)->first();
        if (!$secret) {
            return response()->json([
                'success' => false,
                'message' => 'Secret not found',
            ], 404);
        }
        if ($secret->one_time && $secret->used) {
            return response()->json([
                'success' => false,
                'message' => 'Secret already viewed',
            ], 410);
        }
        if (!$secret->one_time && $secret->expires_at && Carbon::parse($secret->expires_at)->isPast()) {
            return response()->json([
                'success' => false,
                'message' => 'Secret expired',
            ], 410);
        }
        // Mark as used if one_time
        if ($secret->one_time && !$secret->used) {
            $secret->update([
                'used' => true,
                'viewed_at' => now()
            ]);
        }
        return response()->json([
            'success' => true,
            'secret' => $secret->text,
            'expires_at' => $secret->expires_at,
            'one_time' => $secret->one_time,
        ]);
    }
}
