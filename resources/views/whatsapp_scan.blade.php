<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WhatsApp Connection</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-4">

    <div class="bg-white rounded-2xl shadow-xl p-8 max-w-md w-full text-center">
        <div class="mb-6">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.463 1.065 2.875 1.213 3.074.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-800">WhatsApp Connect</h1>
            <p class="text-gray-500 mt-2" id="status-text">Hubungkan akun WhatsApp Anda untuk mengirim pesan rahasia.</p>
        </div>

        <!-- Status Indicator -->
        <div id="status-badge" class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-gray-100 text-gray-600 mb-8 transition-all duration-300">
            <span class="w-2 h-2 rounded-full bg-gray-400 mr-2 animate-pulse"></span>
            Checking Status...
        </div>

        <!-- QR Code Container -->
        <div id="qr-container" class="hidden">
            <div class="bg-white p-4 rounded-xl border-2 border-gray-100 shadow-inner inline-block relative group">
                <div id="qrcode"></div>
                <!-- Scan Overlay Hint -->
                <div class="absolute inset-0 bg-black/5 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center rounded-xl pointer-events-none">
                    <span class="bg-black/70 text-white text-xs px-2 py-1 rounded">Scan me</span>
                </div>
            </div>
            <p class="text-xs text-gray-400 mt-4">Buka WhatsApp > Titik Tiga > Perangkat Tertaut > Tautkan Perangkat</p>
        </div>

        <!-- Success Container -->
        <div id="success-container" class="hidden space-y-4">
             <div class="bg-green-50 p-6 rounded-xl border border-green-100">
                <p class="text-green-700 font-medium">WhatsApp Terhubung!</p>
                <p class="text-green-600 text-sm mt-1">Anda siap mengirim pesan rahasia.</p>
             </div>
             <a href="{{ route('ots.form') }}" class="inline-block w-full bg-gray-900 text-white font-medium py-3 rounded-xl hover:bg-gray-800 transition-colors shadow-lg shadow-gray-200">
                Kembali ke Form
            </a>
            
            <button onclick="logoutWhatsApp()" class="block w-full text-red-500 hover:text-red-700 font-medium py-2 text-sm transition mt-2">
                Putuskan Sambungan
            </button>
        </div>

         <!-- Error/Loading Container -->
         <div id="loading-container" class="hidden">
             <p class="text-gray-400 text-sm">Menunggu layanan WhatsApp...</p>
         </div>

    </div>

    <script>
        const statusBadge = document.getElementById('status-badge');
        const statusText = document.getElementById('status-text');
        const qrContainer = document.getElementById('qr-container');
        const successContainer = document.getElementById('success-container');
        const loadingContainer = document.getElementById('loading-container');
        const qrcodeElement = document.getElementById('qrcode');
        
        let currentQr = null;
        let diffQr = new QRCode(qrcodeElement, {
            width: 256,
            height: 256,
            colorDark : "#000000",
            colorLight : "#ffffff",
            correctLevel : QRCode.CorrectLevel.L
        });

        function updateUI(status, qr) {
            // Reset classes
            statusBadge.className = 'inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold mb-8 transition-all duration-300';
            
            if (status === 'AUTHENTICATED' || status === 'READY') {
                statusBadge.classList.add('bg-green-100', 'text-green-700');
                statusBadge.innerHTML = '<span class="w-2 h-2 rounded-full bg-green-500 mr-2"></span>Connected';
                
                qrContainer.classList.add('hidden');
                loadingContainer.classList.add('hidden');
                successContainer.classList.remove('hidden');
                statusText.innerText = 'Akun WhatsApp Anda aktif.';
            } else if (status === 'WAITING_FOR_QR' || (status === 'INITIALIZING' && qr)) {
                statusBadge.classList.add('bg-amber-100', 'text-amber-700');
                statusBadge.innerHTML = '<span class="w-2 h-2 rounded-full bg-amber-500 mr-2 animate-ping"></span>Scan QR Code';
                
                qrContainer.classList.remove('hidden');
                successContainer.classList.add('hidden');
                loadingContainer.classList.add('hidden');
                statusText.innerText = 'Scan QR code dibawah ini untuk login.';

                if (qr && qr !== currentQr) {
                    currentQr = qr;
                    diffQr.clear();
                    diffQr.makeCode(qr);
                }
            } else {
                // Initializing or Disconnected without QR yet
                statusBadge.classList.add('bg-gray-100', 'text-gray-600');
                
                let message = 'Loading Service...';
                if (status === 'INITIALIZING') {
                    message = 'Starting WhatsApp Engine... (5-15s)';
                } else if (status === 'DISCONNECTED') {
                    message = 'Reconnecting...';
                }
                
                statusBadge.innerHTML = `<span class="w-2 h-2 rounded-full bg-gray-400 mr-2 animate-pulse"></span>${message}`;
                
                qrContainer.classList.add('hidden');
                successContainer.classList.add('hidden');
                loadingContainer.classList.remove('hidden');
            }
        }

        async function checkStatus() {
            try {
                const response = await fetch('http://localhost:3001/status');
                const data = await response.json();
                console.log('Status:', data);
                updateUI(data.status, data.qr);
            } catch (error) {
                console.error('Error fetching status:', error);
                statusBadge.classList.add('bg-red-100', 'text-red-700');
                statusBadge.innerHTML = '<span class="w-2 h-2 rounded-full bg-red-500 mr-2"></span>Service Offline';
                loadingContainer.classList.remove('hidden');
                qrContainer.classList.add('hidden');
            }
        }

        // Poll every 2 seconds
        setInterval(checkStatus, 2000);
        checkStatus(); // Initial call

        async function logoutWhatsApp() {
            if (!confirm('Apakah Anda yakin ingin memutuskan sambungan WhatsApp?')) return;
            
            // Show loading state immediately to give feedback
            successContainer.classList.add('hidden');
            loadingContainer.classList.remove('hidden');
            statusText.innerText = 'Disconnecting...';

            try {
                const response = await fetch('http://localhost:3001/logout', { method: 'POST' });
                const data = await response.json();
                if (data.status === 'success') {
                    // UI will update automatically via polling
                    console.log('Logged out');
                } else {
                    alert('Gagal logout: ' + (data.message || 'Unknown error'));
                    // Restore UI if failed
                    checkStatus();
                }
            } catch (error) {
                console.error('Logout error:', error);
                alert('Gagal menghubungi service WhatsApp');
                checkStatus();
            }
        }
    </script>
</body>
</html>
