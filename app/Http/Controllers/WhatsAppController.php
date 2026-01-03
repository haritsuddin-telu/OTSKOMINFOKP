<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class WhatsAppController extends Controller
{
    /**
     * Show the WhatsApp connection status and QR code scan page.
     */
    public function connect(): View
    {
        return view('whatsapp_scan');
    }
    /**
     * Proxy to get WhatsApp status from local Node.js service.
     */
    public function getStatus()
    {
        try {
            $response = \Illuminate\Support\Facades\Http::get('http://localhost:3001/status');
            return $response->json();
        } catch (\Exception $e) {
            return response()->json(['status' => 'DISCONNECTED', 'error' => 'Service unreachable'], 200);
        }
    }

    /**
     * Proxy to logout WhatsApp via local Node.js service.
     */
    public function logout()
    {
        try {
            $response = \Illuminate\Support\Facades\Http::post('http://localhost:3001/logout');
            return $response->json();
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Service unreachable'], 500);
        }
    }
}
