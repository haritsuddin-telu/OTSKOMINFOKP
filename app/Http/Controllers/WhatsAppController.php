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
}
