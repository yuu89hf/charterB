<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Certificate Workspace') }}
        </h2>
    </x-slot>

    {{-- Loading Overlay --}}
    <div id="loading-overlay" class="hidden fixed inset-0 z-[9999] bg-black/60 backdrop-blur-sm flex items-center justify-center">
        <div class="bg-white rounded-2xl shadow-2xl p-8 flex flex-col items-center gap-4 w-full max-w-md mx-4 text-center">
            <svg id="loading-spinner" class="animate-spin w-12 h-12 text-blue-600 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            <div>
                <p class="font-bold text-gray-800 text-lg">Sedang memproses... <span id="progress-percent">0%</span></p>
                
                {{-- Progress Bar Container --}}
                <div class="w-full bg-gray-200 rounded-full h-3 mt-4 overflow-hidden">
                    <div id="progress-bar-fill" class="bg-blue-600 h-3 rounded-full transition-all duration-300" style="width: 0%"></div>
                </div>

                <p class="text-gray-500 text-sm mt-4">Semua sertifikat sedang di-generate dan dipaket ke ZIP.<br>Mohon tunggu, jangan tutup halaman ini.</p>
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
                    
                    <div id="draggable-name" class="absolute cursor-move px-2 py-1 bg-blue-500/20 border-2 border-blue-500 text-blue-700 font-bold whitespace-nowrap select-none" style="top: 50%; left: 50%; transform: translate(-50%, -50%);">
                        <span id="preview-name-text">Nama Penerima</span>
                        <span class="block text-xs font-normal opacity-75">X: <span id="coord-x">50%</span>, Y: <span id="coord-y">50%</span></span>
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

                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    3. Ukuran Font
                                    <span id="font-scale-label" class="text-blue-600 font-bold">100%</span>
                                </label>
                                <input type="range" name="font_scale" id="font-scale" min="25" max="300" value="100" step="5"
                                    class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-blue-600">
                                <div class="flex justify-between text-xs text-gray-400 mt-1">
                                    <span>25%</span>
                                    <span>100%</span>
                                    <span>300%</span>
                                </div>
                                <p class="text-xs text-gray-500 mt-2">
                                    Ukuran dasar menyesuaikan resolusi template. Geser untuk memperkecil/memperbesar. Teks otomatis mengecil jika berisiko terpotong.
                                </p>
                                <p id="font-size-info" class="text-xs text-gray-400 mt-1 hidden"></p>
                            </div>

                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    4. Resolusi Output
                                    <span id="resolution-scale-label" class="text-blue-600 font-bold">100%</span>
                                </label>
                                <input type="range" name="resolution_scale" id="resolution-scale" min="25" max="300" value="100" step="5"
                                    class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-blue-600">
                                <div class="flex justify-between text-xs text-gray-400 mt-1">
                                    <span>25%</span>
                                    <span>100%</span>
                                    <span>300%</span>
                                </div>
                                <p class="text-xs text-gray-500 mt-2">
                                    Kecilkan resolusi pixel agar file lebih ringan (mis. 2MB → ~500KB). Tidak mengubah posisi atau ukuran font di preview.
                                </p>
                                <p id="resolution-info" class="text-xs text-gray-400 mt-1 hidden"></p>
                            </div>

                            <input type="hidden" name="x_pos" id="input-x" value="50">
                            <input type="hidden" name="y_pos" id="input-y" value="50">
                            <input type="hidden" name="progress_id" id="input-progress-id" value="">

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
        const fontScaleInput = document.getElementById('font-scale');
        const fontScaleLabel = document.getElementById('font-scale-label');
        const fontSizeInfo = document.getElementById('font-size-info');
        const resolutionScaleInput = document.getElementById('resolution-scale');
        const resolutionScaleLabel = document.getElementById('resolution-scale-label');
        const resolutionInfo = document.getElementById('resolution-info');
        const previewNameText = document.getElementById('preview-name-text');

        let imageNaturalWidth = 0;
        let imageNaturalHeight = 0;
        let firstCsvName = 'Nama Penerima';

        function getResolutionScale() {
            return parseInt(resolutionScaleInput.value, 10) / 100;
        }

        function getOutputDimensions() {
            if (!imageNaturalWidth) return { width: 0, height: 0 };
            const scale = getResolutionScale();
            return {
                width: Math.max(100, Math.round(imageNaturalWidth * scale)),
                height: Math.max(100, Math.round(imageNaturalHeight * scale)),
            };
        }

        function calculateBaseFontSize(width) {
            return Math.max(12, Math.round(width * 0.04));
        }

        function getDesignFontSize() {
            if (!imageNaturalWidth) return 16;
            const scale = parseInt(fontScaleInput.value, 10) / 100;
            return Math.round(calculateBaseFontSize(imageNaturalWidth) * scale);
        }

        function updateResolutionInfo() {
            if (!imageNaturalWidth) return;

            const { width, height } = getOutputDimensions();
            resolutionScaleLabel.textContent = resolutionScaleInput.value + '%';
            resolutionInfo.textContent = 'Resolusi output: ' + width + ' × ' + height + 'px (asli ' + imageNaturalWidth + ' × ' + imageNaturalHeight + 'px)';
            resolutionInfo.classList.remove('hidden');
        }

        function updateFontPreview() {
            if (!imageNaturalWidth || !previewImg.clientWidth) return;

            const designSize = getDesignFontSize();
            const displayScale = previewImg.clientWidth / imageNaturalWidth;
            const previewSize = Math.max(8, Math.round(designSize * displayScale));

            dragItem.style.fontSize = previewSize + 'px';
            fontScaleLabel.textContent = fontScaleInput.value + '%';
            fontSizeInfo.textContent = 'Ukuran font: ' + designSize + 'px (pada resolusi asli)';
            fontSizeInfo.classList.remove('hidden');
        }

        function updatePreview() {
            updateFontPreview();
            updateResolutionInfo();
        }

        function positionTextAtCenter() {
            const rect = container.getBoundingClientRect();
            const x = rect.width / 2;
            const y = rect.height / 2;

            dragItem.style.left = x + 'px';
            dragItem.style.top = y + 'px';
            dragItem.style.transform = 'translate(-50%, -50%)';

            document.getElementById('coord-x').innerText = '50%';
            document.getElementById('coord-y').innerText = '50%';
            document.getElementById('input-x').value = '50';
            document.getElementById('input-y').value = '50';
        }

        imgUpload.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    previewImg.onload = function() {
                        imageNaturalWidth = previewImg.naturalWidth;
                        imageNaturalHeight = previewImg.naturalHeight;
                        document.getElementById('canvas-container').classList.remove('hidden');
                        document.getElementById('placeholder-text').classList.add('hidden');
                        positionTextAtCenter();
                        updatePreview();
                    };
                    previewImg.src = event.target.result;
                };
                reader.readAsDataURL(file);
            }
        });

        fontScaleInput.addEventListener('input', updateFontPreview);
        resolutionScaleInput.addEventListener('input', updateResolutionInfo);

        window.addEventListener('resize', updateFontPreview);
        previewImg.addEventListener('load', updatePreview);

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
            dragItem.style.transform = 'translate(-50%, -50%)';

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

        // ── CSV preview: hitung jumlah nama di kolom A & deteksi duplikat ───────────────────────
        document.getElementById('csv-upload').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;
            
            const reader = new FileReader();
            reader.onload = function(ev) {
                const lines = ev.target.result.split(/\r?\n/);
                const skipWords = ['nama','name','no','no.','nomor','number'];
                
                // 1. Deteksi otomatis delimiter (koma atau titik koma)
                let delimiter = ',';
                if (lines.length > 0) {
                    const firstLine = lines[0];
                    if ((firstLine.match(/;/g) || []).length > (firstLine.match(/,/g) || []).length) {
                        delimiter = ';';
                    }
                }

                let nameList = [];
                let duplicateList = [];

                lines.forEach((line, idx) => {
                    let cell = line.split(delimiter)[0];
                    if (!cell) return;
                    
                    // Bersihkan spasi dan karakter aneh / BOM tersembunyi khas Excel
                    cell = cell.trim().replace(/[\x00-\x1F\x7F-\xFF]/g, '');
                    if (cell === '') return;

                    // Skip baris pertama jika berupa header
                    if (idx === 0 && skipWords.includes(cell.toLowerCase())) return;

                    // Cek Duplikat
                    if (nameList.includes(cell)) {
                        if (!duplicateList.includes(cell)) {
                            duplicateList.push(cell);
                        }
                    } else {
                        nameList.push(cell);
                    }
                });

                const info = document.getElementById('csv-info');
                const countEl = document.getElementById('csv-count');
                
                if (nameList.length > 0) {
                    let statusText = '✅ Terdeteksi ' + nameList.length + ' nama unik di kolom A.';
                    
                    // Jika ada duplikat, tambahkan peringatan teks di bawahnya
                    if (duplicateList.length > 0) {
                        statusText += '<br><span style="color: #ef4444; font-weight: bold;">⚠️ Perhatian! Ada ' + duplicateList.length + ' nama duplikat yang diabaikan: [' + duplicateList.join(', ') + ']</span>';
                    }

                    countEl.innerHTML = statusText;
                    info.classList.remove('hidden');
                    document.getElementById('loading-count').textContent = 'Total: ' + nameList.length + ' sertifikat akan di-generate';

                    // Set preview nama pertama
                    if (nameList.length > 0) {
                        previewNameText.textContent = nameList[0];
                    }
                } else {
                    countEl.textContent = '⚠️ Tidak ada nama terdeteksi di kolom A';
                    info.classList.remove('hidden');
                }
            };
            reader.readAsText(file);
        });

        // ── Show loading overlay on form submit (AJAX) ───────────────────────
        document.getElementById('generate-form').addEventListener('submit', function(e) {
            e.preventDefault(); // Hentikan submit standar

            const form = e.target;
            const submitBtn = document.getElementById('submit-btn');
            const progressFill = document.getElementById('progress-bar-fill');
            const progressPercentText = document.getElementById('progress-percent');
            const overlay = document.getElementById('loading-overlay');

            // Reset progress bar
            progressFill.style.width = '0%';
            progressPercentText.innerText = '0%';

            // Generate progress ID unik
            const progressId = 'prog_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            document.getElementById('input-progress-id').value = progressId;

            // Tampilkan loading overlay dan disable tombol submit
            overlay.classList.remove('hidden');
            submitBtn.disabled = true;

            // Hapus cookie lama jika ada
            document.cookie = "download_started=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";

            // Persiapkan data form
            const formData = new FormData(form);

            // Jalankan polling progress bar
            let pollInterval;
            const startPolling = () => {
                pollInterval = setInterval(async () => {
                    try {
                        const response = await fetch(`/certificate/progress/${progressId}`);
                        if (response.ok) {
                            const data = await response.json();
                            const progress = data.progress || 0;
                            
                            progressFill.style.width = progress + '%';
                            progressPercentText.innerText = progress + '%';

                            if (progress >= 100) {
                                clearInterval(pollInterval);
                            }
                        }
                    } catch (err) {
                        console.error('Gagal mengambil progress:', err);
                    }
                }, 800);
            };

            // Kirim request generate via AJAX
            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(async response => {
                if (!response.ok) {
                    const errData = await response.json();
                    throw new Error(errData.message || 'Gagal memproses pembuatan sertifikat.');
                }
                return response.json();
            })
            .then(data => {
                if (data.success && data.download_url) {
                    // Pemicu download file ZIP
                    window.location.href = data.download_url;

                    // Tunggu pendeteksian download dimulai via Cookie
                    const checkDownloadCookie = setInterval(() => {
                        if (document.cookie.split(';').some((item) => item.trim().startsWith('download_started='))) {
                            // Tutup overlay, bersihkan cookie & interval
                            overlay.classList.add('hidden');
                            submitBtn.disabled = false;
                            document.cookie = "download_started=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
                            clearInterval(checkDownloadCookie);
                            clearInterval(pollInterval);
                        }
                    }, 500);
                } else {
                    throw new Error('Respons tidak valid dari server.');
                }
            })
            .catch(error => {
                clearInterval(pollInterval);
                overlay.classList.add('hidden');
                submitBtn.disabled = false;
                alert('⚠️ Error: ' + error.message);
            });

            // Mulai polling setelah submit terkirim
            startPolling();
        });
    </script>
</x-app-layout>