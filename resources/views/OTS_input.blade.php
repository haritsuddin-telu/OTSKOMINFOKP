
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
