
<x-app-layout>
<link rel="icon" type="image/png" href="{{ asset('assets/img/logo-kominfo.png') }}" />
<title>OTS Kominfo Jatim</title>
<div class="container mx-auto py-8">
    <div class="max-w-xl mx-auto bg-white dark:bg-slate-850 shadow-xl rounded-2xl p-8">
        <div class="flex justify-center">
            <h2 class="text-2xl font-bold mb-6 text-blue-500 text-center">One Time Secret (OTS)</h2>
        </div>
        {{-- Display success messages --}}
        @if(session('success'))
            <div class="mb-4 p-3 bg-green-100 text-green-700 rounded">{{ session('success') }}</div>
        @endif
        {{-- Display error messages --}}
        @if(session('error'))
            <div class="mb-4 p-3 bg-red-100 text-red-700 rounded">{{ session('error') }}</div>
        @endif
        {{-- Display error from controller --}}
        @if(isset($error))
            <div class="mb-4 p-3 bg-red-100 text-red-700 rounded">{{ $error }}</div>
        @endif
    @can('access ots')
        <form method="POST" action="{{ route('ots.store') }}" class="space-y-4" enctype="multipart/form-data">
            @csrf
            <div>
                <label for="secret" class="block text-sm font-medium text-gray-700 dark:text-white">Masukkan Pesan Rahasia (Teks)</label>
                <textarea name="secret" id="secret" rows="4" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-800 dark:text-white" placeholder="Tuliskan pesan rahasia anda disini...">{{ old('secret') }}</textarea>
                @error('secret')
                    <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
                @enderror
            </div>
            
            <div>
                <label for="file" class="block text-sm font-medium text-gray-700 dark:text-white">Atau Upload File (Max 10MB)</label>
                <input type="file" name="file" id="file" onchange="validateFileSize(this)" class="mt-1 block w-full text-sm text-gray-500
                    file:mr-4 file:py-2 file:px-4
                    file:rounded-full file:border-0
                    file:text-sm file:font-semibold
                    file:bg-blue-50 file:text-blue-700
                    hover:file:bg-blue-100
                ">
                <div id="fileError" class="text-red-500 text-xs mt-1 hidden">File terlalu besar! Maksimal ukuran file adalah 10MB.</div>
                @error('file')
                    <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
                @enderror
            </div>
<script>
function validateFileSize(input) {
    const maxSize = 10 * 1024 * 1024; // 10MB
    const errorDiv = document.getElementById('fileError');
    
    if (input.files && input.files[0]) {
        if (input.files[0].size > maxSize) {
            errorDiv.classList.remove('hidden');
            input.value = ''; // Clear the input
        } else {
            errorDiv.classList.add('hidden');
        }
    }
}
</script>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-white mb-1">Pilih Durasi</label>
                <div class="flex gap-4">
                    <label>
                        <input type="radio" name="one_time" value="1" {{ old('one_time', '1') == '1' ? 'checked' : '' }} onclick="toggleExpiry(this.value)">
                        Sekali Lihat
                    </label>
                    <label>
                        <input type="radio" name="one_time" value="0" {{ old('one_time') == '0' ? 'checked' : '' }} onclick="toggleExpiry(this.value)">
                        Dilihat Dengan Batasan Waktu                  </label>
                </div>
            </div>
            <div>
                <label for="expiry" class="block text-sm font-medium text-gray-700 dark:text-white">Pilih Batasan Waktu </label>
                <select name="expiry" id="expiry" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-slate-800 dark:text-white">
                    <option value="5" {{ old('expiry') == '5' ? 'selected' : '' }}>5 Menit</option>
                    <option value="60" {{ old('expiry') == '60' ? 'selected' : '' }}>1 Jam</option>
                    <option value="1440" {{ old('expiry') == '1440' ? 'selected' : '' }}>1 Hari</option>
                </select>
                @error('expiry')
                    <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
                @enderror
<script>
function toggleExpiry(val) {
    const expiry = document.getElementById('expiry');
    if (val == '1') {
        expiry.disabled = true;
        expiry.classList.add('bg-gray-200', 'cursor-not-allowed');
    } else {
        expiry.disabled = false;
        expiry.classList.remove('bg-gray-200', 'cursor-not-allowed');
    }
}
// Inisialisasi saat halaman load
document.addEventListener('DOMContentLoaded', function() {
    const checked = document.querySelector('input[name="one_time"]:checked');
    if (checked) toggleExpiry(checked.value);
});
</script>
            </div>
            
            {{-- WhatsApp Static Button --}}
            <div class="mt-4 mb-2">
                <a href="{{ route('whatsapp.connect') }}" class="flex items-center justify-between p-3 bg-white hover:bg-gray-50 text-gray-700 rounded-lg border border-gray-200 shadow-sm transition group">
                    <div class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-500" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.463 1.065 2.875 1.213 3.074.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                        </svg>
                        <span class="font-semibold">Kelola Koneksi WhatsApp</span>
                    </div>
                    <span class="text-blue-500 text-sm group-hover:underline">Buka &rarr;</span>
                </a>
            </div>
            <div class="mt-4 p-4 bg-green-50 rounded-lg border border-green-200">
                <div class="flex items-center mb-2">
                    <input type="checkbox" id="send_whatsapp" name="send_whatsapp" value="1" class="w-4 h-4 text-green-600 bg-gray-100 border-gray-300 rounded focus:ring-green-500 dark:focus:ring-green-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600" onclick="toggleWhatsAppInput()">
                    <label for="send_whatsapp" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Kirim Link via WhatsApp</label>
                </div>
                <div id="whatsapp_input_container" class="hidden">
                    <label for="whatsapp_number" class="block text-sm font-medium text-gray-700 dark:text-white">Nomor WhatsApp (contoh: 628123456789)</label>
                    <input type="text" name="whatsapp_number" id="whatsapp_number" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 dark:bg-slate-800 dark:text-white" placeholder="628xxxxxxxxxx">
                </div>
            </div>
            <script>
                function toggleWhatsAppInput() {
                    const checkbox = document.getElementById('send_whatsapp');
                    const container = document.getElementById('whatsapp_input_container');
                    if (checkbox.checked) {
                        container.classList.remove('hidden');
                    } else {
                        container.classList.add('hidden');
                    }
                }

                // Check WhatsApp Status
                const waStatusDiv = document.getElementById('whatsapp-conn-status');
                
                async function checkWaStatus() {
                    try {
                        const response = await fetch('http://localhost:3001/status');
                        const data = await response.json();
                        
                        if (data.status === 'AUTHENTICATED' || data.status === 'READY') {
                            waStatusDiv.innerHTML = `
                                <a href="{{ route('whatsapp.connect') }}" class="flex items-center justify-between py-2 px-3 bg-green-50 hover:bg-green-100 text-green-700 rounded-lg text-sm border border-green-200 transition group cursor-pointer">
                                    <div class="flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                        <span class="font-semibold">WhatsApp Terhubung</span>
                                    </div>
                                    <span class="text-green-600 group-hover:underline text-xs">Atur &rarr;</span>
                                </a>
                            `;
                        } else {
                            // Not connected
                            waStatusDiv.innerHTML = `
                                <a href="{{ route('whatsapp.connect') }}" class="flex items-center justify-between p-3 bg-amber-50 hover:bg-amber-100 text-amber-800 rounded-lg border border-amber-200 transition group mb-2 text-sm">
                                    <div class="flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                        <span>WhatsApp Belum Terhubung</span>
                                    </div>
                                    <span class="font-semibold text-blue-600 group-hover:underline">Hubungkan Sekarang &rarr;</span>
                                </a>
                            `;
                        }
                    } catch (error) {
                        console.error('WA Status Error:', error);
                        waStatusDiv.innerHTML = `
                             <div class="flex items-center gap-2 py-2 px-3 bg-gray-100 text-gray-500 rounded-lg text-sm border border-gray-200">
                                <span class="w-2 h-2 bg-gray-400 rounded-full"></span>
                                <span>Service WhatsApp Offline</span>
                            </div>
                        `;
                    }
                }

                // Check on load
                document.addEventListener('DOMContentLoaded', checkWaStatus);
            </script>
            <button type="submit" class="w-full py-2 px-4 bg-blue-500 hover:bg-blue-600 text-white font-semibold rounded-lg shadow-md transition">Buat Link Rahasia</button>
        </form>

        {{-- Display generated link --}}
        @if(session('signedUrl'))
        <div class="mt-8 flex flex-col items-center justify-center">
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-6 shadow-lg w-full max-w-lg">
                <h3 class="text-lg font-bold text-blue-600 mb-2">Link Rahasia Anda</h3>
                <div class="flex items-center mb-2">
                    <input type="text" readonly value="{{ session('signedUrl') }}" id="secretUrl" class="flex-1 rounded-lg border-blue-300 bg-white text-blue-700 px-2 py-2 mr-2 font-mono text-sm shadow focus:outline-none">
                    <button onclick="copyToClipboard()" class="py-2 px-4 bg-green-500 hover:bg-green-600 text-white rounded-lg font-semibold shadow">Salin Link</button>
                </div>
                
                {{-- WhatsApp Share Button --}}
                <div class="mt-4 flex justify-center">
                    <a href="https://wa.me/?text={{ urlencode('Halo, ini link rahasia untuk Anda: ' . session('signedUrl')) }}" target="_blank" class="flex items-center gap-2 py-2 px-6 bg-[#25D366] hover:bg-[#128C7E] text-white rounded-lg font-semibold shadow transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M13.601 2.326A7.854 7.854 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.933 7.933 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.898 7.898 0 0 0 13.6 2.326zM7.994 14.521a6.573 6.573 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.557 6.557 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592zm3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.729.729 0 0 0-.529.247c-.182.198-.691.677-.691 1.654 0 .977.71 1.916.81 2.049.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232z"/>
                        </svg>
                        Kirim via WhatsApp
                    </a>
                </div>

                <a href="{{ session('signedUrl') }}" target="_blank" class="block text-blue-500 hover:underline text-xs mt-4 text-center">Buka Link Rahasia</a>
                <div class="mt-2 text-xs text-gray-600">
                    <strong>Perhatian:</strong> Link ini hanya dapat dibuka sesuai jenis yang dipilih dan akan kedaluwarsa secara otomatis.
                </div>
            </div>
        </div>
        @endif
        @else
        <div class="p-4 bg-yellow-100 text-yellow-700 rounded">Hanya pegawai yang dapat membuat Link Rahasia.</div>
        @endrole
<script>
function copyToClipboard() {
    const urlInput = document.getElementById('secretUrl');
    urlInput.select();
    urlInput.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(urlInput.value).then(function() {
        const button = event.target;
        const originalText = button.textContent;
        button.textContent = 'Copied!';
        button.classList.remove('bg-green-500', 'hover:bg-green-600');
        button.classList.add('bg-green-600');
        setTimeout(() => {
            button.textContent = originalText;
            button.classList.add('bg-green-500', 'hover:bg-green-600');
            button.classList.remove('bg-green-600');
        }, 2000);
    });
}
</script>
    </div>
</div>
</x-app-layout>
