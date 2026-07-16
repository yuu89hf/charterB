<x-app-layout>
    <style>
        @font-face {
            font-family: 'Roboto-Bold';
            src: url('/fonts/Roboto-Bold.ttf') format('truetype');
            font-weight: bold;
        }
        @font-face {
            font-family: 'Montserrat-Bold';
            src: url('/fonts/Montserrat-Bold.ttf') format('truetype');
            font-weight: bold;
        }
        @font-face {
            font-family: 'PlayfairDisplay-Bold';
            src: url('/fonts/PlayfairDisplay-Bold.ttf') format('truetype');
            font-weight: bold;
        }
        @font-face {
            font-family: 'AlexBrush-Regular';
            src: url('/fonts/AlexBrush-Regular.ttf') format('truetype');
            font-weight: normal;
        }
        @font-face {
            font-family: 'Cinzel-Bold';
            src: url('/fonts/Cinzel-Bold.ttf') format('truetype');
            font-weight: bold;
        }
        @font-face {
            font-family: 'ComicSans';
            src: url('/fonts/ComicSans.ttf') format('truetype');
            font-weight: normal;
        }
        @font-face {
            font-family: 'TimesNewRoman';
            src: url('/fonts/TimesNewRoman.ttf') format('truetype');
            font-weight: normal;
        }
        @font-face {
            font-family: 'Arial';
            src: url('/fonts/Arial.ttf') format('truetype');
            font-weight: normal;
        }
    </style>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Workspace') }}
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
                <p class="font-bold text-gray-800 text-lg">Processing... <span id="progress-percent">0%</span></p>
                
                {{-- Progress Bar Container --}}
                <div class="w-full bg-gray-200 rounded-full h-3 mt-4 overflow-hidden">
                    <div id="progress-bar-fill" class="bg-blue-600 h-3 rounded-full transition-all duration-300" style="width: 0%"></div>
                </div>

                <p class="text-gray-500 text-sm mt-4">All certificates are being generated and packaged into a ZIP.<br>Please wait, do not close this page.</p>
            </div>
            <div id="loading-count" class="text-blue-600 font-semibold text-sm"></div>
        </div>
    </div>

    <div class="py-6 h-[calc(100vh-64px)] overflow-hidden">
        <div class="max-w-[1600px] mx-auto sm:px-6 lg:px-8 h-full flex gap-4">
            
            <div class="flex-grow bg-gray-200 rounded-xl relative overflow-hidden flex items-center justify-center p-10 border-2 border-dashed border-gray-300">
                
                <div id="canvas-container" class="relative shadow-2xl bg-white hidden">
                    <img id="preview-img" src="#" alt="Preview" class="max-w-full max-h-[70vh]">
                    
                    <div id="v-guide" class="absolute top-0 bottom-0 w-0.5 bg-red-500 hidden z-10"></div>
                    <div id="h-guide" class="absolute left-0 right-0 h-0.5 bg-red-500 hidden z-10"></div>

                    <div id="draggable-name" class="absolute cursor-move select-none text-center" style="top: 50%; left: 50%; transform: translate(-50%, -50%);">
                        <div id="preview-name-text" class="px-3 py-1 bg-blue-500/20 border-2 border-blue-500 text-blue-700 font-bold whitespace-nowrap rounded">Recipient Name</div>
                        <div class="absolute left-1/2 -translate-x-1/2 top-full mt-1 bg-gray-900 text-white px-1.5 py-0.5 rounded shadow-md whitespace-nowrap pointer-events-none" style="font-size: 10px !important; font-family: sans-serif !important; font-weight: normal !important; line-height: 1 !important;">
                            X: <span id="coord-x">50%</span>, Y: <span id="coord-y">50%</span>
                        </div>
                    </div>
                </div>

                <div id="placeholder-text" class="text-gray-500 text-center">
                    <p class="text-lg font-semibold">Please upload template in the right sidebar</p>
                </div>
            </div>

            <div class="relative flex h-full">
                
                <button onclick="toggleSidebar()" class="absolute left-0 top-1/2 -translate-y-1/2 -translate-x-1/2 bg-blue-600 text-white shadow-lg rounded-full w-8 h-8 flex items-center justify-center hover:bg-blue-700 transition-all duration-300 z-50 focus:outline-none">
                    <span id="toggle-btn-text" class="font-bold text-sm">&gt;</span>
                </button>

                <div id="sidebar" class="w-80 bg-white shadow-xl rounded-xl h-full transition-all duration-300 ease-in-out border-l border-gray-100 overflow-hidden">
                    
                    <div id="sidebar-content" class="p-6 h-full overflow-y-auto w-80">
                        <h3 class="font-bold text-lg mb-4 text-gray-800">File Settings</h3>

                        {{-- Tampilkan error validasi dari server --}}
                        @if ($errors->any())
                            <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
                                <p class="font-bold mb-1">⚠️ An error occurred:</p>
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

                            <!-- SECTION 1: FILE CONFIGURATION -->
                            <div class="mb-6 border-b border-gray-100 pb-5">
                                <span class="text-[10px] font-bold tracking-wider text-blue-600 uppercase block mb-3 bg-blue-50 px-2.5 py-1 rounded w-fit">1. File Configuration</span>
                                
                                <div class="mb-4">
                                    <label class="block text-xs font-semibold text-gray-600 mb-2">1.1 Template Upload</label>
                                    <input type="file" name="template" id="image-upload" accept="image/*" required
                                        class="block w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3.5 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer" />
                                </div>

                                <div class="mb-4">
                                    <label class="block text-xs font-semibold text-gray-600 mb-2">1.2 Names Data (CSV / Excel)</label>
                                    <input type="file" name="csv_file" id="csv-upload" accept=".csv,.txt,.xlsx,.xls" required
                                        class="block w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3.5 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100 cursor-pointer" />
                                    {{-- Preview info jumlah nama terdeteksi --}}
                                    <div id="csv-info" class="mt-2 text-xs text-gray-500 hidden">
                                        <span id="csv-count"></span>
                                    </div>
                                </div>

                                <!-- Row Filtering Features -->
                                <div class="bg-gray-50 p-3 rounded-lg border border-gray-200 mt-3">
                                    <h4 class="text-[11px] font-bold text-gray-600 uppercase mb-2 flex items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" /></svg>
                                        CSV Row Filtering (Optional)
                                    </h4>
                                    
                                    <div class="grid grid-cols-2 gap-2 mb-2.5">
                                        <div>
                                            <label class="block text-[10px] font-semibold text-gray-500 mb-1">Start Row</label>
                                            <input type="number" name="row_start" min="1" placeholder="e.g. 11" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-xs py-1 px-2">
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-semibold text-gray-500 mb-1">End Row</label>
                                            <input type="number" name="row_end" min="1" placeholder="e.g. 80" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-xs py-1 px-2">
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-[10px] font-semibold text-gray-500 mb-1">Exclude Rows</label>
                                        <input type="text" name="row_exclude" placeholder="e.g. 22;33;44" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-xs py-1 px-2">
                                        <p class="text-[9px] text-gray-400 mt-1">Separate row numbers with commas or semicolons.</p>
                                    </div>
                                </div>
                            </div>

                            <!-- SECTION 2: TEXT & STYLE SETTINGS -->
                            <div class="mb-6 border-b border-gray-100 pb-5">
                                <span class="text-[10px] font-bold tracking-wider text-purple-600 uppercase block mb-3 bg-purple-50 px-2.5 py-1 rounded w-fit">2. Text & Font Styles</span>

                                <div class="mb-4">
                                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">2.1 Select Default Font</label>
                                    <select name="font_family" id="font-family" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-xs py-1.5 px-2.5">
                                        <option value="Roboto-Bold" selected>Roboto (Modern Sans-Serif - Default)</option>
                                        <option value="Montserrat-Bold">Montserrat (Sleek Sans-Serif)</option>
                                        <option value="PlayfairDisplay-Bold">Playfair Display (Elegant Serif)</option>
                                        <option value="AlexBrush-Regular">Alex Brush (Signature Script)</option>
                                        <option value="Cinzel-Bold">Cinzel (Classic Serif)</option>
                                        <option value="ComicSans">Comic Sans MS</option>
                                        <option value="TimesNewRoman">Times New Roman</option>
                                        <option value="Arial">Arial</option>
                                    </select>
                                </div>

                                <div class="mb-4">
                                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                                        2.2 Font Size
                                        <span id="font-scale-label" class="text-purple-600 font-bold ml-1">100%</span>
                                    </label>
                                    <input type="range" name="font_scale" id="font-scale" min="25" max="300" value="100" step="5"
                                        class="w-full h-1.5 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-purple-600">
                                    <div class="flex justify-between text-[10px] text-gray-400 mt-1">
                                        <span>25%</span>
                                        <span>100%</span>
                                        <span>300%</span>
                                    </div>
                                    <p class="text-[10px] text-gray-400 mt-1.5 leading-relaxed">
                                        Base size scales with template resolution. Text auto-shrinks if it risks clipping.
                                    </p>
                                    <p id="font-size-info" class="text-[10px] text-purple-600 font-semibold mt-1 hidden"></p>
                                </div>

                                <div class="mb-2">
                                    <label class="inline-flex items-center cursor-pointer select-none">
                                        <input type="checkbox" id="enable-snap" checked class="rounded border-gray-300 text-purple-600 shadow-sm focus:border-purple-500 focus:ring-purple-500 w-4 h-4">
                                        <span class="ml-2 text-xs text-gray-600 font-semibold">Enable Snap Guide (Every 5%)</span>
                                    </label>
                                </div>
                            </div>

                            <!-- SECTION 3: EXPORT CONFIGURATION -->
                            <div class="mb-6">
                                <span class="text-[10px] font-bold tracking-wider text-orange-600 uppercase block mb-3 bg-orange-50 px-2.5 py-1 rounded w-fit">3. Export Configuration</span>

                                <div class="mb-4">
                                    <label class="block text-xs font-semibold text-gray-600 mb-2">3.1 Output Format</label>
                                    <div class="flex items-center space-x-4">
                                        <label class="inline-flex items-center cursor-pointer">
                                            <input type="radio" name="format" value="png" checked class="form-radio text-orange-500 focus:ring-orange-400">
                                            <span class="ml-2 text-xs text-gray-600 font-medium">PNG</span>
                                        </label>
                                        <label class="inline-flex items-center cursor-pointer">
                                            <input type="radio" name="format" value="jpg" class="form-radio text-orange-500 focus:ring-orange-400">
                                            <span class="ml-2 text-xs text-gray-600 font-medium">JPG</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="mb-6">
                                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                                        3.2 Output Resolution
                                        <span id="resolution-scale-label" class="text-orange-600 font-bold ml-1">100%</span>
                                    </label>
                                    <input type="range" name="resolution_scale" id="resolution-scale" min="25" max="300" value="100" step="5"
                                        class="w-full h-1.5 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-orange-500">
                                    <div class="flex justify-between text-[10px] text-gray-400 mt-1">
                                        <span>25%</span>
                                        <span>100%</span>
                                        <span>300%</span>
                                    </div>
                                    <p class="text-[10px] text-gray-400 mt-1.5 leading-relaxed">
                                        Reduce pixel resolution for lighter files (e.g. 2MB → ~500KB).
                                    </p>
                                    <p id="resolution-info" class="text-[10px] text-orange-600 font-semibold mt-1 hidden"></p>
                                </div>
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
        const fontFamilySelect = document.getElementById('font-family');

        let imageNaturalWidth = 0;
        let imageNaturalHeight = 0;
        let firstCsvName = 'Nama Penerima';

        function getSelectedFontFamily() {
            return fontFamilySelect.value;
        }

        function mapFontName(name) {
            if (!name) return 'Roboto-Bold';
            const clean = name.trim().toLowerCase();
            if (clean.includes('roboto')) return 'Roboto-Bold';
            if (clean.includes('montserrat')) return 'Montserrat-Bold';
            if (clean.includes('playfair')) return 'PlayfairDisplay-Bold';
            if (clean.includes('alex')) return 'AlexBrush-Regular';
            if (clean.includes('cinzel')) return 'Cinzel-Bold';
            if (clean.includes('comic') || clean.includes('sans')) return 'ComicSans';
            if (clean.includes('times') || clean.includes('tnr')) return 'TimesNewRoman';
            if (clean.includes('arial')) return 'Arial';
            return 'Roboto-Bold'; // fallback
        }

        function updateFontFamilyPreview() {
            const font = getSelectedFontFamily();
            dragItem.style.fontFamily = font;
        }

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
            resolutionInfo.textContent = 'Output resolution: ' + width + ' × ' + height + 'px (original ' + imageNaturalWidth + ' × ' + imageNaturalHeight + 'px)';
            resolutionInfo.classList.remove('hidden');
        }

        function updateFontPreview() {
            if (!imageNaturalWidth || !previewImg.clientWidth) return;

            const designSize = getDesignFontSize();
            const displayScale = previewImg.clientWidth / imageNaturalWidth;
            const previewSize = Math.max(8, Math.round(designSize * displayScale));

            dragItem.style.fontSize = previewSize + 'px';
            fontScaleLabel.textContent = fontScaleInput.value + '%';
            fontSizeInfo.textContent = 'Font size: ' + designSize + 'px (at native resolution)';
            fontSizeInfo.classList.remove('hidden');
        }

        function updatePreview() {
            updateFontPreview();
            updateResolutionInfo();
            updateFontFamilyPreview();
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

        let lastTemplateFile = null;
        imgUpload.addEventListener('change', function(e) {
            if (e.target.files.length > 0) {
                lastTemplateFile = e.target.files[0];
            } else if (lastTemplateFile) {
                // Kembalikan file sebelumnya jika dibatalkan (cancel)
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(lastTemplateFile);
                e.target.files = dataTransfer.files;
                return;
            } else {
                return;
            }

            const file = lastTemplateFile;
            if (file) {
                // Hapus URL objek lama jika ada untuk mencegah memory leak
                if (previewImg.src && previewImg.src.startsWith('blob:')) {
                    URL.revokeObjectURL(previewImg.src);
                }

                previewImg.onload = function() {
                    imageNaturalWidth = previewImg.naturalWidth;
                    imageNaturalHeight = previewImg.naturalHeight;
                    document.getElementById('canvas-container').classList.remove('hidden');
                    document.getElementById('placeholder-text').classList.add('hidden');
                    positionTextAtCenter();
                    updatePreview();
                };
                previewImg.src = URL.createObjectURL(file);
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

            const enableSnap = document.getElementById('enable-snap').checked;
            const threshold = 12; // toleransi piksel untuk snap

            vGuide.classList.add('hidden');
            hGuide.classList.add('hidden');

            if (enableSnap) {
                // Snap X ke kelipatan 5%
                for (let i = 0; i <= 20; i++) {
                    const snapPct = i * 5;
                    const snapPx = (snapPct / 100) * rect.width;
                    if (Math.abs(x - snapPx) < threshold) {
                        x = snapPx;
                        vGuide.style.left = snapPx + 'px';
                        vGuide.classList.remove('hidden');
                        break;
                    }
                }

                // Snap Y ke kelipatan 5%
                for (let i = 0; i <= 20; i++) {
                    const snapPct = i * 5;
                    const snapPx = (snapPct / 100) * rect.height;
                    if (Math.abs(y - snapPx) < threshold) {
                        y = snapPx;
                        hGuide.style.top = snapPx + 'px';
                        hGuide.classList.remove('hidden');
                        break;
                    }
                }
            }

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

        let isFocused = false;

        // Fokuskan elemen saat diklik atau didrag
        dragItem.addEventListener('mousedown', () => {
            isFocused = true;
            document.getElementById('preview-name-text').classList.add('ring-2', 'ring-blue-500', 'ring-offset-2');
        });

        // Hapus fokus jika mengklik di luar area teks
        document.addEventListener('click', (e) => {
            if (!dragItem.contains(e.target)) {
                isFocused = false;
                document.getElementById('preview-name-text').classList.remove('ring-2', 'ring-blue-500', 'ring-offset-2');
            }
        });

        // Kontrol menggunakan tombol Arrow (Panah) keyboard
        document.addEventListener('keydown', (e) => {
            if (!isFocused) return;

            if (['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight'].includes(e.key)) {
                e.preventDefault(); // Cegah halaman scroll saat memindahkan teks
            } else {
                return;
            }

            const rect = container.getBoundingClientRect();
            if (!rect.width || !rect.height) return;

            // Dapatkan posisi pixel saat ini
            let leftPx = parseFloat(dragItem.style.left) || (rect.width / 2);
            let topPx = parseFloat(dragItem.style.top) || (rect.height / 2);

            // Jarak perpindahan: 1px (tahan Shift untuk 10px agar lebih cepat)
            const step = e.shiftKey ? 10 : 1;

            if (e.key === 'ArrowLeft') {
                leftPx = Math.max(0, leftPx - step);
            } else if (e.key === 'ArrowRight') {
                leftPx = Math.min(rect.width, leftPx + step);
            } else if (e.key === 'ArrowUp') {
                topPx = Math.max(0, topPx - step);
            } else if (e.key === 'ArrowDown') {
                topPx = Math.min(rect.height, topPx + step);
            }

            // Update posisi elemen
            dragItem.style.left = leftPx + 'px';
            dragItem.style.top  = topPx + 'px';

            // Hitung ulang persentase koordinat
            const px = ((leftPx / rect.width)  * 100).toFixed(2);
            const py = ((topPx / rect.height) * 100).toFixed(2);

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

        let lastCsvFile = null;
        document.getElementById('csv-upload').addEventListener('change', function(e) {
            if (e.target.files.length > 0) {
                lastCsvFile = e.target.files[0];
            } else if (lastCsvFile) {
                // Kembalikan file sebelumnya jika dibatalkan (cancel)
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(lastCsvFile);
                e.target.files = dataTransfer.files;
                return;
            } else {
                return;
            }

            const file = lastCsvFile;
            if (!file) return;
            const reader = new FileReader();
            reader.onload = function(ev) {
                const lines  = ev.target.result.split(/\r?\n/);
                const skipWords = ['nama','name','no','no.','nomor','number'];
                
                // Deteksi delimiter
                let delimiter = ',';
                if (lines.length > 0) {
                    const firstLine = lines[0];
                    const commaCount = (firstLine.match(/,/g) || []).length;
                    const semicolonCount = (firstLine.match(/;/g) || []).length;
                    if (semicolonCount > commaCount) {
                        delimiter = ';';
                    }
                }

                let longestName = '';
                lines.forEach((line, idx) => {
                    const parts = line.split(delimiter);
                    const cell = parts[0] ? parts[0].trim() : '';
                    if (!cell) return;
                    if (idx === 0 && skipWords.includes(cell.toLowerCase())) return; // skip header
                    count++;
                    if (cell.length > longestName.length) {
                        longestName = cell;
                    }
                });
                const info = document.getElementById('csv-info');
                const countEl = document.getElementById('csv-count');
                if (count > 0) {
                    countEl.textContent = '✅ Detected ' + count + ' names in Column A';
                    info.classList.remove('hidden');
                    document.getElementById('loading-count').textContent = 'Total: ' + count + ' certificates will be generated';

                    if (longestName) {
                        firstCsvName = longestName;
                        previewNameText.textContent = firstCsvName;
                    }
                } else {
                    countEl.textContent = '⚠️ No names detected in column A';
                    info.classList.remove('hidden');
                }
                updateFontFamilyPreview();
            };
            reader.readAsText(file);
        });

        function parseExcel(file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                try {
                    const data = new Uint8Array(e.target.result);
                    const workbook = XLSX.read(data, {type: 'array'});
                    const firstSheet = workbook.Sheets[workbook.SheetNames[0]];
                    
                    // Convert sheet ke array of arrays untuk mengambil Kolom A
                    const rows = XLSX.utils.sheet_to_json(firstSheet, {header: 1});
                    const skipWords = ['nama','name','no','no.','nomor','number'];
                    let count = 0;
                    let longestName = '';

                    rows.forEach((row, idx) => {
                        const cell = row[0] ? String(row[0]).trim() : '';
                        if (!cell) return;
                        if (idx === 0 && skipWords.includes(cell.toLowerCase())) return;
                        count++;
                        if (cell.length > longestName.length) {
                            longestName = cell;
                        }
                    });

                    const info = document.getElementById('csv-info');
                    const countEl = document.getElementById('csv-count');
                    if (count > 0) {
                        countEl.textContent = '✅ Detected ' + count + ' names in Column A';
                        document.getElementById('loading-count').textContent = 'Total: ' + count + ' certificates will be generated';
                        if (longestName) {
                            firstCsvName = longestName;
                            previewNameText.textContent = firstCsvName;
                        }
                    } else {
                        countEl.textContent = '⚠️ No names detected in Column A';
                    }
                    updateFontFamilyPreview();
                } catch (err) {
                    console.error(err);
                    document.getElementById('csv-count').textContent = '⚠️ Error reading Excel file';
                }
            };
            reader.readAsArrayBuffer(file);
        }

        fontFamilySelect.addEventListener('change', updateFontFamilyPreview);


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
                    throw new Error(errData.message || 'Failed to process certificate generation.');
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