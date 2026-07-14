<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Certificate Workspace') }}
        </h2>
    </x-slot>

    {{-- Loading Overlay --}}
    <div id="loading-overlay" class="hidden fixed inset-0 z-[9999] bg-black/60 backdrop-blur-sm flex items-center justify-center">
        <div class="bg-white rounded-2xl shadow-2xl p-8 flex flex-col items-center gap-4 max-w-sm mx-4 text-center">
            <svg class="animate-spin w-12 h-12 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            <div>
                <p class="font-bold text-gray-800 text-lg">Sedang memproses...</p>
                <p class="text-gray-500 text-sm mt-1">Semua sertifikat sedang di-generate dan dipaket ke ZIP.<br>Mohon tunggu, jangan tutup halaman ini.</p>
            </div>
            <div id="loading-count" class="text-blue-600 font-semibold text-sm"></div>
        </div>
    </div>

    <div class="py-6 h-[calc(100vh-64px)] overflow-hidden">
        <div class="max-w-[1600px] mx-auto sm:px-6 lg:px-8 h-full flex gap-4">
            
            <div class="flex-grow bg-gray-200 rounded-xl relative overflow-hidden flex items-center justify-center p-10 border-2 border-dashed border-gray-300">
                
                <div id="v-guide" class="absolute left-1/2 top-0 bottom-0 w-0.5 bg-red-500 hidden z-10"></div>
                <div id="h-guide" class="absolute top-1/2 left-0 right-0 h-0.5 bg-red-500 hidden z-10"></div>

                <div id="canvas-container" class="relative shadow-2xl bg-white hidden">
                    <img id="preview-img" src="#" alt="Preview" class="max-w-full max-h-[70vh]">
                    
                    <div id="draggable-name" class="absolute cursor-move px-4 py-2 bg-blue-500/20 border-2 border-blue-500 text-blue-700 font-bold whitespace-nowrap select-none" style="top: 50%; left: 50%; transform: translate(-50%, -50%);">
                        Nama Penerima (X: <span id="coord-x">50%</span>, Y: <span id="coord-y">50%</span>)
                    </div>
                </div>

                <div id="placeholder-text" class="text-gray-500 text-center">
                    <p class="text-lg font-semibold">Silahkan upload template di sidebar kanan</p>
                </div>
            </div>

            <div class="relative flex h-full">
                
                <button onclick="toggleSidebar()" class="absolute left-0 top-1/2 -translate-y-1/2 -translate-x-1/2 bg-blue-600 text-white shadow-lg rounded-full w-8 h-8 flex items-center justify-center hover:bg-blue-700 transition-all duration-300 z-50 focus:outline-none">
                    <span id="toggle-btn-text" class="font-bold text-sm">&gt;</span>
                </button>

                <div id="sidebar" class="w-80 bg-white shadow-xl rounded-xl h-full transition-all duration-300 ease-in-out border-l border-gray-100 overflow-hidden">
                    
                    <div id="sidebar-content" class="p-6 h-full overflow-y-auto w-80">
                        <h3 class="font-bold text-lg mb-4 text-gray-800">Pengaturan File</h3>

                        {{-- Tampilkan error validasi dari server --}}
                        @if ($errors->any())
                            <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
                                <p class="font-bold mb-1">⚠️ Terjadi kesalahan:</p>
                                <ul class="list-disc list-inside space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if (session('success'))
                            <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">
                                ✅ {{ session('success') }}
                            </div>
                        @endif

                        <form id="generate-form" action="{{ route('certificate.generate') }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            <div class="mb-4">
                                <label class="block text-gray-700 text-sm font-bold mb-2">Format Output</label>
                                <div class="flex items-center space-x-4">
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="format" value="png" checked class="form-radio text-indigo-600">
                                        <span class="ml-2 text-gray-700">PNG</span>
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="format" value="jpg" class="form-radio text-indigo-600">
                                        <span class="ml-2 text-gray-700">JPG</span>
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="format" value="pdf" class="form-radio text-indigo-600">
                                        <span class="ml-2 text-gray-700">PDF</span>
                                    </label>
                                </div>
                            </div>

                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">1. Upload Template</label>
                                <input type="file" name="template" id="image-upload" accept="image/*" required
                                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">2. Data Nama (CSV)</label>
                                <input type="file" name="csv_file" id="csv-upload" accept=".csv,.txt" required
                                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100" />
                                {{-- Preview info jumlah nama terdeteksi --}}
                                <div id="csv-info" class="mt-2 text-xs text-gray-500 hidden">
                                    <span id="csv-count"></span>
                                </div>
                            </div>

                            <hr class="my-6 border-gray-200">

                            <input type="hidden" name="x_pos" id="input-x" value="50">
                            <input type="hidden" name="y_pos" id="input-y" value="50">

                            <button id="submit-btn" type="submit"
                                class="w-full bg-blue-600 text-white font-bold py-3 rounded-lg hover:bg-blue-700 transition shadow-md flex items-center justify-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                Generate &amp; Download ZIP
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let sidebarOpen = true;

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const toggleBtnText = document.getElementById('toggle-btn-text');
            if (sidebarOpen) {
                sidebar.style.width = '0px';
                sidebar.style.borderLeftWidth = '0px';
                toggleBtnText.innerText = '<';
                sidebarOpen = false;
            } else {
                sidebar.style.width = '320px';
                sidebar.style.borderLeftWidth = '1px';
                toggleBtnText.innerText = '>';
                sidebarOpen = true;
            }
        }

        // ── Drag & Drop preview ──────────────────────────────────────────────
        const dragItem  = document.getElementById('draggable-name');
        const container = document.getElementById('canvas-container');
        const imgUpload = document.getElementById('image-upload');
        const previewImg = document.getElementById('preview-img');
        const vGuide   = document.getElementById('v-guide');
        const hGuide   = document.getElementById('h-guide');

        imgUpload.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    previewImg.src = event.target.result;
                    document.getElementById('canvas-container').classList.remove('hidden');
                    document.getElementById('placeholder-text').classList.add('hidden');
                };
                reader.readAsDataURL(file);
            }
        });

        let isDragging = false;
        dragItem.addEventListener('mousedown', () => { isDragging = true; dragItem.style.zIndex = 100; });

        document.addEventListener('mousemove', (e) => {
            if (!isDragging) return;
            const rect = container.getBoundingClientRect();
            let x = Math.max(0, Math.min(e.clientX - rect.left, rect.width));
            let y = Math.max(0, Math.min(e.clientY - rect.top, rect.height));

            const cx = rect.width / 2, cy = rect.height / 2, th = 15;
            if (Math.abs(x - cx) < th) { x = cx; vGuide.classList.remove('hidden'); } else { vGuide.classList.add('hidden'); }
            if (Math.abs(y - cy) < th) { y = cy; hGuide.classList.remove('hidden'); } else { hGuide.classList.add('hidden'); }

            dragItem.style.left = x + 'px';
            dragItem.style.top  = y + 'px';

            const px = ((x / rect.width)  * 100).toFixed(2);
            const py = ((y / rect.height) * 100).toFixed(2);
            document.getElementById('coord-x').innerText = px + '%';
            document.getElementById('coord-y').innerText = py + '%';
            document.getElementById('input-x').value = px;
            document.getElementById('input-y').value = py;
        });

        document.addEventListener('mouseup', () => {
            isDragging = false;
            vGuide.classList.add('hidden');
            hGuide.classList.add('hidden');
        });

        // ── CSV preview: hitung jumlah nama di kolom A ───────────────────────
        document.getElementById('csv-upload').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = function(ev) {
                const lines  = ev.target.result.split(/\r?\n/);
                const skipWords = ['nama','name','no','no.','nomor','number'];
                let count = 0;
                lines.forEach((line, idx) => {
                    const cell = line.split(',')[0].trim();
                    if (!cell) return;
                    if (idx === 0 && skipWords.includes(cell.toLowerCase())) return; // skip header
                    count++;
                });
                const info = document.getElementById('csv-info');
                const countEl = document.getElementById('csv-count');
                if (count > 0) {
                    countEl.textContent = '✅ Terdeteksi ' + count + ' nama di kolom A';
                    info.classList.remove('hidden');
                    // Update loading overlay text
                    document.getElementById('loading-count').textContent = 'Total: ' + count + ' sertifikat akan di-generate';
                } else {
                    countEl.textContent = '⚠️ Tidak ada nama terdeteksi di kolom A';
                    info.classList.remove('hidden');
                }
            };
            reader.readAsText(file);
        });

        // ── Show loading overlay on form submit ──────────────────────────────
        document.getElementById('generate-form').addEventListener('submit', function() {
            document.getElementById('loading-overlay').classList.remove('hidden');
            document.getElementById('submit-btn').disabled = true;

            // Hapus cookie download_started jika ada dari download sebelumnya
            document.cookie = "download_started=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";

            // Polling untuk mendeteksi kapan download dimulai (ketika cookie dikirim oleh server)
            const checkCookieInterval = setInterval(function() {
                if (document.cookie.split(';').some((item) => item.trim().startsWith('download_started='))) {
                    // Sembunyikan loading overlay dan aktifkan kembali tombol submit
                    document.getElementById('loading-overlay').classList.add('hidden');
                    document.getElementById('submit-btn').disabled = false;
                    
                    // Hapus cookie setelah dideteksi
                    document.cookie = "download_started=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
                    clearInterval(checkCookieInterval);
                }
            }, 500);
        });
    </script>
</x-app-layout>