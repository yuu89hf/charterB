<x-app-layout>
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
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
        <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 h-full flex flex-col lg:flex-row gap-4 relative overflow-hidden">
            
            <div class="flex-grow bg-gray-200 rounded-xl relative overflow-hidden flex items-center justify-center p-4 sm:p-10 border-2 border-dashed border-gray-300 h-[60vh] lg:h-full">
                
                <div id="canvas-container" class="relative shadow-2xl bg-white hidden overflow-hidden select-none">
                    <!-- Draggable Image Wrapper -->
                    <div id="draggable-image-wrapper" class="absolute cursor-move select-none" style="left: 0px; top: 0px; width: 100%; height: 100%;">
                        <img id="preview-img" src="#" alt="Preview" class="w-full h-full object-fill pointer-events-none">
                        
                        <!-- Canva-style Corner Resize Handles -->
                        <div class="resize-handle absolute w-3 h-3 bg-white border border-blue-600 rounded-full cursor-nwse-resize z-20 -top-1.5 -left-1.5 hover:scale-125 transition-transform" data-handle="tl"></div>
                        <div class="resize-handle absolute w-3 h-3 bg-white border border-blue-600 rounded-full cursor-nesw-resize z-20 -top-1.5 -right-1.5 hover:scale-125 transition-transform" data-handle="tr"></div>
                        <div class="resize-handle absolute w-3 h-3 bg-white border border-blue-600 rounded-full cursor-nesw-resize z-20 -bottom-1.5 -left-1.5 hover:scale-125 transition-transform" data-handle="bl"></div>
                        <div class="resize-handle absolute w-3 h-3 bg-white border border-blue-600 rounded-full cursor-nwse-resize z-20 -bottom-1.5 -right-1.5 hover:scale-125 transition-transform" data-handle="br"></div>

                        <div id="draggable-name" class="absolute cursor-move select-none text-center" style="top: 50%; left: 50%; transform: translate(-50%, -50%);">
                            <div id="preview-name-text" class="px-3 py-1 bg-blue-500/20 border-2 border-blue-500 text-blue-700 font-bold whitespace-nowrap rounded">Recipient Name</div>
                            <div class="absolute left-1/2 -translate-x-1/2 top-full mt-1 bg-gray-900 text-white px-1.5 py-0.5 rounded shadow-md whitespace-nowrap pointer-events-none" style="font-size: 10px !important; font-family: sans-serif !important; font-weight: normal !important; line-height: 1 !important;">
                                X: <span id="coord-x">50%</span>, Y: <span id="coord-y">50%</span>
                            </div>
                        </div>
                    </div>
                    
                    <div id="v-guide" class="absolute top-0 bottom-0 w-0.5 bg-red-500 hidden z-10 pointer-events-none"></div>
                    <div id="h-guide" class="absolute left-0 right-0 h-0.5 bg-red-500 hidden z-10 pointer-events-none"></div>
                </div>

                <div id="placeholder-text" class="text-gray-500 text-center">
                    <p class="text-lg font-semibold">Please upload template in the right sidebar</p>
                </div>
            </div>

            <div id="sidebar-container" class="lg:relative fixed inset-y-0 right-0 z-40 flex h-full transition-transform duration-300 ease-in-out translate-x-full lg:translate-x-0">
                
                <button onclick="toggleSidebar()" class="absolute left-0 top-1/2 -translate-y-1/2 -translate-x-1/2 bg-blue-600 text-white shadow-lg rounded-full w-8 h-8 flex items-center justify-center hover:bg-blue-700 transition-all duration-300 z-50 focus:outline-none">
                    <span id="toggle-btn-text" class="font-bold text-sm">&lt;</span>
                </button>

                <div id="sidebar" class="w-80 bg-white shadow-xl rounded-l-xl lg:rounded-xl h-full border-l border-gray-100 overflow-hidden">
                    
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
                                        <label class="inline-flex items-center cursor-pointer">
                                            <input type="radio" name="format" value="pdf" class="form-radio text-orange-500 focus:ring-orange-400">
                                            <span class="ml-2 text-xs text-gray-600 font-medium">PDF</span>
                                        </label>
                                    </div>

                                    <!-- PDF Paper Options Container -->
                                    <div id="pdf-paper-options" class="hidden mt-3 p-3 bg-orange-50/50 rounded-lg border border-orange-100">
                                        <div class="mb-3">
                                            <label class="block text-xs font-semibold text-gray-600 mb-1.5">Use Paper Size</label>
                                            <div class="flex items-center space-x-4">
                                                <label class="inline-flex items-center cursor-pointer">
                                                    <input type="radio" name="use_paper" value="n" checked class="form-radio text-orange-500 focus:ring-orange-400">
                                                    <span class="ml-2 text-xs text-gray-600 font-medium">No</span>
                                                </label>
                                                <label class="inline-flex items-center cursor-pointer">
                                                    <input type="radio" name="use_paper" value="y" class="form-radio text-orange-500 focus:ring-orange-400">
                                                    <span class="ml-2 text-xs text-gray-600 font-medium">Yes</span>
                                                </label>
                                            </div>
                                        </div>

                                        <div id="paper-size-container" class="hidden mb-1">
                                            <div class="mb-3">
                                                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Paper Size</label>
                                                <select name="paper_size" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-xs py-1.5 px-2.5">
                                                    <option value="A4" selected>A4: 21 x 29.7 cm</option>
                                                    <option value="F4">F4/Folio: 21 x 33 cm</option>
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Paper Orientation</label>
                                                <div class="flex items-center space-x-4">
                                                    <label class="inline-flex items-center cursor-pointer">
                                                        <input type="radio" name="paper_orientation" value="auto" checked class="form-radio text-orange-500 focus:ring-orange-400">
                                                        <span class="ml-2 text-xs text-gray-600 font-medium">Auto</span>
                                                    </label>
                                                    <label class="inline-flex items-center cursor-pointer">
                                                        <input type="radio" name="paper_orientation" value="L" class="form-radio text-orange-500 focus:ring-orange-400">
                                                        <span class="ml-2 text-xs text-gray-600 font-medium">Landscape</span>
                                                    </label>
                                                    <label class="inline-flex items-center cursor-pointer">
                                                        <input type="radio" name="paper_orientation" value="P" class="form-radio text-orange-500 focus:ring-orange-400">
                                                        <span class="ml-2 text-xs text-gray-600 font-medium">Portrait</span>
                                                    </label>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Image Layout Mode</label>
                                                <div class="flex items-center space-x-4">
                                                    <label class="inline-flex items-center cursor-pointer">
                                                        <input type="radio" name="fit_mode" value="full" class="form-radio text-orange-500 focus:ring-orange-400">
                                                        <span class="ml-2 text-xs text-gray-600 font-medium">Full Page</span>
                                                    </label>
                                                    <label class="inline-flex items-center cursor-pointer">
                                                        <input type="radio" name="fit_mode" value="smaller" checked class="form-radio text-orange-500 focus:ring-orange-400">
                                                        <span class="ml-2 text-xs text-gray-600 font-medium">Custom</span>
                                                    </label>
                                                </div>
                                            </div>

                                            <div id="image-scale-container" class="mb-1 bg-white p-2.5 rounded border border-orange-100">
                                                <label class="block text-[11px] font-semibold text-gray-500 mb-1">
                                                    Image Scale: <span id="img-scale-val" class="text-orange-500 font-bold">100%</span>
                                                </label>
                                                <input type="range" id="img-scale-slider" min="10" max="250" value="100" step="1" class="w-full h-1.5 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-orange-500">
                                                <p class="text-[9px] text-gray-400 mt-1">Scale template image relative to the paper canvas.</p>
                                            </div>
                                        </div>
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
                            
                            <!-- Draggable Image wrapper position metrics (percentages relative to canvas-container) -->
                            <input type="hidden" name="img_x" id="input-img-x" value="0">
                            <input type="hidden" name="img_y" id="input-img-y" value="0">
                            <input type="hidden" name="img_w" id="input-img-w" value="100">
                            <input type="hidden" name="img_h" id="input-img-h" value="100">

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
        function toggleSidebar() {
            const containerEl = document.getElementById('sidebar-container');
            const sidebar = document.getElementById('sidebar');
            const btnText = document.getElementById('toggle-btn-text');

            const isMobile = window.innerWidth < 1024;

            if (isMobile) {
                if (containerEl.classList.contains('translate-x-full')) {
                    containerEl.classList.remove('translate-x-full');
                    containerEl.classList.add('translate-x-0');
                    btnText.textContent = '>';
                } else {
                    containerEl.classList.remove('translate-x-0');
                    containerEl.classList.add('translate-x-full');
                    btnText.textContent = '<';
                }
            } else {
                if (sidebar.style.width === '0px') {
                    sidebar.style.width = '320px';
                    sidebar.style.borderLeftWidth = '1px';
                    btnText.innerText = '>';
                } else {
                    sidebar.style.width = '0px';
                    sidebar.style.borderLeftWidth = '0px';
                    btnText.innerText = '<';
                }
            }
            
            setTimeout(() => {
                updateCanvasContainerSize();
                updateImageLayerPositionAndSize();
                updateFontPreview();
            }, 305);
        }

        window.addEventListener('resize', () => {
            const containerEl = document.getElementById('sidebar-container');
            const sidebar = document.getElementById('sidebar');
            const btnText = document.getElementById('toggle-btn-text');
            const isMobile = window.innerWidth < 1024;

            if (isMobile) {
                sidebar.style.width = '';
                sidebar.style.borderLeftWidth = '';
                if (!containerEl.classList.contains('translate-x-0') && !containerEl.classList.contains('translate-x-full')) {
                    containerEl.classList.add('translate-x-full');
                }
                btnText.textContent = containerEl.classList.contains('translate-x-0') ? '>' : '<';
            } else {
                containerEl.classList.remove('translate-x-full', 'translate-x-0');
                sidebar.style.width = sidebar.style.width || '320px';
                btnText.textContent = sidebar.style.width === '0px' ? '<' : '>';
            }

            updateCanvasContainerSize();
            updateImageLayerPositionAndSize();
            updateFontPreview();
        });

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

        const imgWrapper = document.getElementById('draggable-image-wrapper');
        const imgScaleSlider = document.getElementById('img-scale-slider');
        const imgScaleVal = document.getElementById('img-scale-val');

        let imgScalePercent = 100;
        let imgXPercent = 0;
        let imgYPercent = 0;
        let imgWPercent = 100;
        let imgHPercent = 100;

        function isUsePaper() {
            const el = document.querySelector('input[name="use_paper"]:checked');
            return el && el.value === 'y';
        }

        function isFitModeCustom() {
            const el = document.querySelector('input[name="fit_mode"]:checked');
            return el && el.value === 'smaller';
        }

        function updateCanvasContainerSize() {
            if (!imageNaturalWidth) return;

            if (isUsePaper()) {
                const paperSize = document.querySelector('select[name="paper_size"]').value;
                let orientation = document.querySelector('input[name="paper_orientation"]:checked')?.value || 'auto';
                if (orientation === 'auto') {
                    orientation = imageNaturalWidth > imageNaturalHeight ? 'L' : 'P';
                }

                let ratio = 1.4142; 
                if (paperSize === 'A4') {
                    ratio = orientation === 'L' ? (29.7 / 21) : (21 / 29.7);
                } else if (paperSize === 'F4') {
                    ratio = orientation === 'L' ? (33 / 21) : (21 / 33);
                }

                const maxCanvasHeight = 450;
                const maxCanvasWidth = 650;

                let canvasHeight = maxCanvasHeight;
                let canvasWidth = canvasHeight * ratio;

                if (canvasWidth > maxCanvasWidth) {
                    canvasWidth = maxCanvasWidth;
                    canvasHeight = canvasWidth / ratio;
                }

                container.style.width = Math.round(canvasWidth) + 'px';
                container.style.height = Math.round(canvasHeight) + 'px';
                container.style.backgroundColor = '#ffffff'; 
                container.style.display = 'block'; 
            } else {
                const maxCanvasHeight = 450;
                const maxCanvasWidth = 650;
                const ratio = imageNaturalWidth / imageNaturalHeight;

                let canvasHeight = maxCanvasHeight;
                let canvasWidth = canvasHeight * ratio;

                if (canvasWidth > maxCanvasWidth) {
                    canvasWidth = maxCanvasWidth;
                    canvasHeight = canvasWidth / ratio;
                }

                container.style.width = Math.round(canvasWidth) + 'px';
                container.style.height = Math.round(canvasHeight) + 'px';
                container.style.backgroundColor = 'transparent';
                container.style.display = 'block';
            }
        }

        function centerImageLayer() {
            if (!imageNaturalWidth) return;
            const canvasW = container.clientWidth;
            const canvasH = container.clientHeight;
            if (!canvasW || !canvasH) return;

            const imgRatio = imageNaturalWidth / imageNaturalHeight;
            const canvasRatio = canvasW / canvasH;

            let baseW = canvasW;
            let baseH = canvasH;

            if (imgRatio > canvasRatio) {
                baseW = canvasW;
                baseH = canvasW / imgRatio;
            } else {
                baseH = canvasH;
                baseW = canvasH * imgRatio;
            }

            const scale = parseFloat(imgScaleSlider.value) / 100;
            const finalW = baseW * scale;
            const finalH = baseH * scale;

            const leftPx = (canvasW - finalW) / 2;
            const topPx = (canvasH - finalH) / 2;

            imgXPercent = (leftPx / canvasW) * 100;
            imgYPercent = (topPx / canvasH) * 100;
        }

        function updateImageLayerPositionAndSize() {
            if (!imageNaturalWidth) return;

            const canvasW = container.clientWidth;
            const canvasH = container.clientHeight;
            if (!canvasW || !canvasH) return;

            if (!isUsePaper() || !isFitModeCustom()) {
                imgWrapper.style.left = '0px';
                imgWrapper.style.top = '0px';
                imgWrapper.style.width = '100%';
                imgWrapper.style.height = '100%';
                
                document.getElementById('input-img-x').value = '0';
                document.getElementById('input-img-y').value = '0';
                document.getElementById('input-img-w').value = '100';
                document.getElementById('input-img-h').value = '100';
                return;
            }

            const imgRatio = imageNaturalWidth / imageNaturalHeight;
            const canvasRatio = canvasW / canvasH;

            let baseW = canvasW;
            let baseH = canvasH;

            if (imgRatio > canvasRatio) {
                baseW = canvasW;
                baseH = canvasW / imgRatio;
            } else {
                baseH = canvasH;
                baseW = canvasH * imgRatio;
            }

            const scale = parseFloat(imgScaleSlider.value) / 100;
            const finalW = baseW * scale;
            const finalH = baseH * scale;

            let leftPx = (imgXPercent / 100) * canvasW;
            let topPx = (imgYPercent / 100) * canvasH;

            imgWrapper.style.width = Math.round(finalW) + 'px';
            imgWrapper.style.height = Math.round(finalH) + 'px';
            imgWrapper.style.left = Math.round(leftPx) + 'px';
            imgWrapper.style.top = Math.round(topPx) + 'px';

            imgWPercent = (finalW / canvasW) * 100;
            imgHPercent = (finalH / canvasH) * 100;

            document.getElementById('input-img-x').value = imgXPercent.toFixed(4);
            document.getElementById('input-img-y').value = imgYPercent.toFixed(4);
            document.getElementById('input-img-w').value = imgWPercent.toFixed(4);
            document.getElementById('input-img-h').value = imgHPercent.toFixed(4);
        }

        function positionTextAtCenter() {
            const w = imgWrapper.clientWidth || 300;
            const h = imgWrapper.clientHeight || 200;
            const x = w / 2;
            const y = h / 2;

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
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(lastTemplateFile);
                e.target.files = dataTransfer.files;
                return;
            } else {
                return;
            }

            const file = lastTemplateFile;
            if (file) {
                if (previewImg.src && previewImg.src.startsWith('blob:')) {
                    URL.revokeObjectURL(previewImg.src);
                }

                previewImg.onload = function() {
                    imageNaturalWidth = previewImg.naturalWidth;
                    imageNaturalHeight = previewImg.naturalHeight;
                    document.getElementById('canvas-container').classList.remove('hidden');
                    document.getElementById('placeholder-text').classList.add('hidden');
                    
                    updateCanvasContainerSize();
                    centerImageLayer();
                    updateImageLayerPositionAndSize();
                    positionTextAtCenter();
                    updatePreview();
                };
                previewImg.src = URL.createObjectURL(file);
            }
        });

        fontScaleInput.addEventListener('input', updateFontPreview);
        resolutionScaleInput.addEventListener('input', updateResolutionInfo);

        window.addEventListener('resize', () => {
            updateCanvasContainerSize();
            updateImageLayerPositionAndSize();
            updateFontPreview();
        });
        previewImg.addEventListener('load', updatePreview);

        let isDragging = false;
        let isResizing = false;
        let activeDragElement = null;
        let activeHandle = null;
        let dragStartX = 0;
        let dragStartY = 0;
        let elementStartX = 0;
        let elementStartY = 0;
        let resizeStartW = 0;
        let resizeStartH = 0;
        let resizeStartLeft = 0;
        let resizeStartTop = 0;

        // Register handle mousedowns
        document.querySelectorAll('.resize-handle').forEach(handle => {
            handle.addEventListener('mousedown', (e) => {
                e.stopPropagation();
                e.preventDefault();
                isResizing = true;
                activeHandle = handle.dataset.handle;
                
                dragStartX = e.clientX;
                dragStartY = e.clientY;
                resizeStartW = imgWrapper.clientWidth;
                resizeStartH = imgWrapper.clientHeight;
                resizeStartLeft = imgWrapper.offsetLeft;
                resizeStartTop = imgWrapper.offsetTop;
            });
        });

        dragItem.addEventListener('mousedown', (e) => {
            e.stopPropagation();
            activeDragElement = dragItem;
            isDragging = true;
            dragItem.style.zIndex = 100;
            
            dragStartX = e.clientX;
            dragStartY = e.clientY;
            elementStartX = dragItem.offsetLeft;
            elementStartY = dragItem.offsetTop;

            isFocused = true;
            document.getElementById('preview-name-text').classList.add('ring-2', 'ring-blue-500', 'ring-offset-2');
        });

        imgWrapper.addEventListener('mousedown', (e) => {
            if (!isUsePaper() || !isFitModeCustom()) return;
            activeDragElement = imgWrapper;
            isDragging = true;

            dragStartX = e.clientX;
            dragStartY = e.clientY;
            elementStartX = imgWrapper.offsetLeft;
            elementStartY = imgWrapper.offsetTop;
        });

        function getTouchPos(e) {
            return {
                x: e.touches[0].clientX,
                y: e.touches[0].clientY
            };
        }

        // Register handle touchstart
        document.querySelectorAll('.resize-handle').forEach(handle => {
            handle.addEventListener('touchstart', (e) => {
                e.stopPropagation();
                isResizing = true;
                activeHandle = handle.dataset.handle;
                
                const pos = getTouchPos(e);
                dragStartX = pos.x;
                dragStartY = pos.y;
                resizeStartW = imgWrapper.clientWidth;
                resizeStartH = imgWrapper.clientHeight;
                resizeStartLeft = imgWrapper.offsetLeft;
                resizeStartTop = imgWrapper.offsetTop;
            });
        });

        dragItem.addEventListener('touchstart', (e) => {
            e.stopPropagation();
            activeDragElement = dragItem;
            isDragging = true;
            dragItem.style.zIndex = 100;
            
            const pos = getTouchPos(e);
            dragStartX = pos.x;
            dragStartY = pos.y;
            elementStartX = dragItem.offsetLeft;
            elementStartY = dragItem.offsetTop;

            isFocused = true;
            document.getElementById('preview-name-text').classList.add('ring-2', 'ring-blue-500', 'ring-offset-2');
        });

        imgWrapper.addEventListener('touchstart', (e) => {
            if (!isUsePaper() || !isFitModeCustom()) return;
            if (e.target.classList.contains('resize-handle')) return;

            activeDragElement = imgWrapper;
            isDragging = true;

            const pos = getTouchPos(e);
            dragStartX = pos.x;
            dragStartY = pos.y;
            elementStartX = imgWrapper.offsetLeft;
            elementStartY = imgWrapper.offsetTop;
        });

        function handleMove(clientX, clientY) {
            if (isResizing) {
                const deltaX = clientX - dragStartX;
                const deltaY = clientY - dragStartY;
                
                const canvasW = container.clientWidth;
                const canvasH = container.clientHeight;
                const imgRatio = imageNaturalWidth / imageNaturalHeight;

                let newW = resizeStartW;
                let newH = resizeStartH;
                let newLeft = resizeStartLeft;
                let newTop = resizeStartTop;

                const threshold = 12; // Snap distance in pixels
                let snapped = false;

                vGuide.classList.add('hidden');
                hGuide.classList.add('hidden');

                if (activeHandle === 'br') {
                    newW = Math.max(50, resizeStartW + deltaX);
                    newH = newW / imgRatio;

                    // Snap to right edge of canvas
                    if (Math.abs((newLeft + newW) - canvasW) < threshold) {
                        newW = canvasW - newLeft;
                        newH = newW / imgRatio;
                        vGuide.style.left = canvasW + 'px';
                        vGuide.classList.remove('hidden');
                        snapped = true;
                    }
                    // Snap to bottom edge of canvas
                    if (!snapped && Math.abs((newTop + newH) - canvasH) < threshold) {
                        newH = canvasH - newTop;
                        newW = newH * imgRatio;
                        hGuide.style.top = canvasH + 'px';
                        hGuide.classList.remove('hidden');
                    }
                } else if (activeHandle === 'bl') {
                    newW = Math.max(50, resizeStartW - deltaX);
                    newH = newW / imgRatio;
                    newLeft = resizeStartLeft + (resizeStartW - newW);

                    // Snap left edge to canvas left border
                    if (Math.abs(newLeft) < threshold) {
                        newLeft = 0;
                        newW = resizeStartLeft + resizeStartW;
                        newH = newW / imgRatio;
                        vGuide.style.left = '0px';
                        vGuide.classList.remove('hidden');
                        snapped = true;
                    }
                    // Snap bottom edge to canvas bottom border
                    if (!snapped && Math.abs((newTop + newH) - canvasH) < threshold) {
                        newH = canvasH - newTop;
                        newW = newH * imgRatio;
                        newLeft = resizeStartLeft + (resizeStartW - newW);
                        hGuide.style.top = canvasH + 'px';
                        hGuide.classList.remove('hidden');
                    }
                } else if (activeHandle === 'tr') {
                    newW = Math.max(50, resizeStartW + deltaX);
                    newH = newW / imgRatio;
                    newTop = resizeStartTop + (resizeStartH - newH);

                    // Snap right edge to canvas right border
                    if (Math.abs((newLeft + newW) - canvasW) < threshold) {
                        newW = canvasW - newLeft;
                        newH = newW / imgRatio;
                        newTop = resizeStartTop + (resizeStartH - newH);
                        vGuide.style.left = canvasW + 'px';
                        vGuide.classList.remove('hidden');
                        snapped = true;
                    }
                    // Snap top edge to canvas top border
                    if (!snapped && Math.abs(newTop) < threshold) {
                        newTop = 0;
                        newH = resizeStartTop + resizeStartH;
                        newW = newH * imgRatio;
                        hGuide.style.top = '0px';
                        hGuide.classList.remove('hidden');
                    }
                } else if (activeHandle === 'tl') {
                    newW = Math.max(50, resizeStartW - deltaX);
                    newH = newW / imgRatio;
                    newLeft = resizeStartLeft + (resizeStartW - newW);
                    newTop = resizeStartTop + (resizeStartH - newH);

                    // Snap left edge to canvas left border
                    if (Math.abs(newLeft) < threshold) {
                        newLeft = 0;
                        newW = resizeStartLeft + resizeStartW;
                        newH = newW / imgRatio;
                        newTop = resizeStartTop + (resizeStartH - newH);
                        vGuide.style.left = '0px';
                        vGuide.classList.remove('hidden');
                        snapped = true;
                    }
                    // Snap top edge to canvas top border
                    if (!snapped && Math.abs(newTop) < threshold) {
                        newTop = 0;
                        newH = resizeStartTop + resizeStartH;
                        newW = newH * imgRatio;
                        newLeft = resizeStartLeft + (resizeStartW - newW);
                        hGuide.style.top = '0px';
                        hGuide.classList.remove('hidden');
                    }
                }

                if (newW > 0 && newH > 0) {
                    imgWrapper.style.width = Math.round(newW) + 'px';
                    imgWrapper.style.height = Math.round(newH) + 'px';
                    imgWrapper.style.left = Math.round(newLeft) + 'px';
                    imgWrapper.style.top = Math.round(newTop) + 'px';

                    imgXPercent = (newLeft / canvasW) * 100;
                    imgYPercent = (newTop / canvasH) * 100;
                    imgWPercent = (newW / canvasW) * 100;
                    imgHPercent = (newH / canvasH) * 100;

                    const canvasRatio = canvasW / canvasH;
                    let fitBaseW = canvasW;
                    if (imgRatio > canvasRatio) {
                        fitBaseW = canvasW;
                    } else {
                        fitBaseW = canvasH * imgRatio;
                    }
                    const computedScale = Math.round((newW / fitBaseW) * 100);
                    imgScaleSlider.value = Math.max(10, Math.min(250, computedScale));
                    imgScaleVal.innerText = imgScaleSlider.value + '%';

                    document.getElementById('input-img-x').value = imgXPercent.toFixed(4);
                    document.getElementById('input-img-y').value = imgYPercent.toFixed(4);
                    document.getElementById('input-img-w').value = imgWPercent.toFixed(4);
                    document.getElementById('input-img-h').value = imgHPercent.toFixed(4);

                    updateFontPreview();
                }
                return;
            }

            if (!isDragging || !activeDragElement) return;

            const deltaX = clientX - dragStartX;
            const deltaY = clientY - dragStartY;

            if (activeDragElement === dragItem) {
                const rect = imgWrapper.getBoundingClientRect();
                let x = Math.max(0, Math.min(elementStartX + deltaX, rect.width));
                let y = Math.max(0, Math.min(elementStartY + deltaY, rect.height));

                const enableSnap = document.getElementById('enable-snap').checked;
                const threshold = 12;

                vGuide.classList.add('hidden');
                hGuide.classList.add('hidden');

                if (enableSnap) {
                    for (let i = 0; i <= 20; i++) {
                        const snapPct = i * 5;
                        const snapPx = (snapPct / 100) * rect.width;
                        if (Math.abs(x - snapPx) < threshold) {
                            x = snapPx;
                            const canvasRect = container.getBoundingClientRect();
                            vGuide.style.left = (rect.left - canvasRect.left + snapPx) + 'px';
                            vGuide.classList.remove('hidden');
                            break;
                        }
                    }

                    for (let i = 0; i <= 20; i++) {
                        const snapPct = i * 5;
                        const snapPx = (snapPct / 100) * rect.height;
                        if (Math.abs(y - snapPx) < threshold) {
                            y = snapPx;
                            const canvasRect = container.getBoundingClientRect();
                            hGuide.style.top = (rect.top - canvasRect.top + snapPx) + 'px';
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

            } else if (activeDragElement === imgWrapper) {
                const canvasW = container.clientWidth;
                const canvasH = container.clientHeight;
                
                let x = elementStartX + deltaX;
                let y = elementStartY + deltaY;

                const wrapperW = imgWrapper.clientWidth;
                const wrapperH = imgWrapper.clientHeight;

                const canvasCenterX = canvasW / 2;
                const canvasCenterY = canvasH / 2;
                const wrapperCenterX = x + wrapperW / 2;
                const wrapperCenterY = y + wrapperH / 2;

                const threshold = 12; 
                let snappedX = false;
                let snappedY = false;

                vGuide.classList.add('hidden');
                hGuide.classList.add('hidden');

                if (Math.abs(wrapperCenterX - canvasCenterX) < threshold) {
                    x = canvasCenterX - wrapperW / 2;
                    vGuide.style.left = canvasCenterX + 'px';
                    vGuide.classList.remove('hidden');
                    snappedX = true;
                }
                if (!snappedX) {
                    if (Math.abs(x) < threshold) {
                        x = 0;
                        vGuide.style.left = '0px';
                        vGuide.classList.remove('hidden');
                    } else if (Math.abs(x + wrapperW - canvasW) < threshold) {
                        x = canvasW - wrapperW;
                        vGuide.style.left = canvasW + 'px';
                        vGuide.classList.remove('hidden');
                    }
                }

                if (Math.abs(wrapperCenterY - canvasCenterY) < threshold) {
                    y = canvasCenterY - wrapperH / 2;
                    hGuide.style.top = canvasCenterY + 'px';
                    hGuide.classList.remove('hidden');
                    snappedY = true;
                }
                if (!snappedY) {
                    if (Math.abs(y) < threshold) {
                        y = 0;
                        hGuide.style.top = '0px';
                        hGuide.classList.remove('hidden');
                    } else if (Math.abs(y + wrapperH - canvasH) < threshold) {
                        y = canvasH - wrapperH;
                        hGuide.style.top = canvasH + 'px';
                        hGuide.classList.remove('hidden');
                    }
                }

                x = Math.max(-wrapperW / 2, Math.min(x, canvasW - wrapperW / 2));
                y = Math.max(-wrapperH / 2, Math.min(y, canvasH - wrapperH / 2));

                imgWrapper.style.left = x + 'px';
                imgWrapper.style.top  = y + 'px';

                imgXPercent = (x / canvasW) * 100;
                imgYPercent = (y / canvasH) * 100;

                document.getElementById('input-img-x').value = imgXPercent.toFixed(4);
                document.getElementById('input-img-y').value = imgYPercent.toFixed(4);
            }
        }

        document.addEventListener('mousemove', (e) => {
            handleMove(e.clientX, e.clientY);
        });

        document.addEventListener('touchmove', (e) => {
            if (isDragging || isResizing) {
                if (e.cancelable) e.preventDefault();
                const pos = getTouchPos(e);
                handleMove(pos.x, pos.y);
            }
        }, { passive: false });

        function handleEnd() {
            isDragging = false;
            isResizing = false;
            activeDragElement = null;
            activeHandle = null;
            vGuide.classList.add('hidden');
            hGuide.classList.add('hidden');
        }

        document.addEventListener('mouseup', handleEnd);
        document.addEventListener('touchend', handleEnd);
        document.addEventListener('touchcancel', handleEnd);
        let isFocused = false;

        document.addEventListener('click', (e) => {
            if (!dragItem.contains(e.target)) {
                isFocused = false;
                document.getElementById('preview-name-text').classList.remove('ring-2', 'ring-blue-500', 'ring-offset-2');
            }
        });

        document.addEventListener('keydown', (e) => {
            if (!isFocused) return;

            if (['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight'].includes(e.key)) {
                e.preventDefault();
            } else {
                return;
            }

            const w = imgWrapper.clientWidth;
            const h = imgWrapper.clientHeight;
            if (!w || !h) return;

            let leftPx = parseFloat(dragItem.style.left) || (w / 2);
            let topPx = parseFloat(dragItem.style.top) || (h / 2);

            const step = e.shiftKey ? 10 : 1;

            if (e.key === 'ArrowLeft') {
                leftPx = Math.max(0, leftPx - step);
            } else if (e.key === 'ArrowRight') {
                leftPx = Math.min(w, leftPx + step);
            } else if (e.key === 'ArrowUp') {
                topPx = Math.max(0, topPx - step);
            } else if (e.key === 'ArrowDown') {
                topPx = Math.min(h, topPx + step);
            }

            dragItem.style.left = leftPx + 'px';
            dragItem.style.top  = topPx + 'px';

            const px = ((leftPx / w)  * 100).toFixed(2);
            const py = ((topPx / h) * 100).toFixed(2);

            document.getElementById('coord-x').innerText = px + '%';
            document.getElementById('coord-y').innerText = py + '%';
            document.getElementById('input-x').value = px;
            document.getElementById('input-y').value = py;
        });

        let lastCsvFile = null;
        document.getElementById('csv-upload').addEventListener('change', function(e) {
            if (e.target.files.length > 0) {
                lastCsvFile = e.target.files[0];
            } else if (lastCsvFile) {
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(lastCsvFile);
                e.target.files = dataTransfer.files;
                return;
            } else {
                return;
            }

            const file = lastCsvFile;
            if (!file) return;

            const fileName = file.name.toLowerCase();
            if (fileName.endsWith('.xlsx') || fileName.endsWith('.xls')) {
                parseExcel(file);
                return;
            }

            const reader = new FileReader();
            reader.onload = function(ev) {
                const lines  = ev.target.result.split(/\r?\n/);
                const skipWords = ['nama','name','no','no.','nomor','number'];
                
                let delimiter = ',';
                if (lines.length > 0) {
                    const firstLine = lines[0];
                    const commaCount = (firstLine.match(/,/g) || []).length;
                    const semicolonCount = (firstLine.match(/;/g) || []).length;
                    if (semicolonCount > commaCount) {
                        delimiter = ';';
                    }
                }

                let count = 0;
                let longestName = '';
                lines.forEach((line, idx) => {
                    const parts = line.split(delimiter);
                    const cell = parts[0] ? parts[0].trim() : '';
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

        document.getElementById('generate-form').addEventListener('submit', function(e) {
            e.preventDefault(); 

            const form = e.target;
            const submitBtn = document.getElementById('submit-btn');
            const progressFill = document.getElementById('progress-bar-fill');
            const progressPercentText = document.getElementById('progress-percent');
            const overlay = document.getElementById('loading-overlay');

            progressFill.style.width = '0%';
            progressPercentText.innerText = '0%';

            const progressId = 'prog_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            document.getElementById('input-progress-id').value = progressId;

            overlay.classList.remove('hidden');
            submitBtn.disabled = true;

            document.cookie = "download_started=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";

            const formData = new FormData(form);

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
                    window.location.href = data.download_url;

                    const checkDownloadCookie = setInterval(() => {
                        if (document.cookie.split(';').some((item) => item.trim().startsWith('download_started='))) {
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

            startPolling();
        });

        // Toggle PDF Paper options visibility
        const formatRadios = document.querySelectorAll('input[name="format"]');
        const pdfPaperOptions = document.getElementById('pdf-paper-options');
        const usePaperRadios = document.querySelectorAll('input[name="use_paper"]');
        const paperSizeContainer = document.getElementById('paper-size-container');
        const fitModeRadios = document.querySelectorAll('input[name="fit_mode"]');
        const imageScaleContainer = document.getElementById('image-scale-container');

        function updatePdfOptionsVisibility() {
            // Paper options are now available for all formats (PNG, JPG, PDF)
            pdfPaperOptions.classList.remove('hidden');
        }

        function updatePaperSizeVisibility() {
            let usePaperValue = 'n';
            usePaperRadios.forEach(radio => {
                if (radio.checked) usePaperValue = radio.value;
            });

            if (usePaperValue === 'y') {
                paperSizeContainer.classList.remove('hidden');
            } else {
                paperSizeContainer.classList.add('hidden');
            }
        }

        function updateMarginVisibility() {
            let fitModeValue = 'full';
            fitModeRadios.forEach(radio => {
                if (radio.checked) fitModeValue = radio.value;
            });

            if (fitModeValue === 'smaller') {
                imageScaleContainer.classList.remove('hidden');
            } else {
                imageScaleContainer.classList.add('hidden');
            }
        }

        const paperSizeSelect = document.querySelector('select[name="paper_size"]');
        const paperOrientationRadios = document.querySelectorAll('input[name="paper_orientation"]');
        
        function handlePaperSettingsChange() {
            updateCanvasContainerSize();
            updateImageLayerPositionAndSize();
            updatePreview();
        }

        paperSizeSelect.addEventListener('change', handlePaperSettingsChange);
        paperOrientationRadios.forEach(radio => {
            radio.addEventListener('change', handlePaperSettingsChange);
        });

        formatRadios.forEach(radio => {
            radio.addEventListener('change', () => {
                updatePdfOptionsVisibility();
                handlePaperSettingsChange();
            });
        });

        usePaperRadios.forEach(radio => {
            radio.addEventListener('change', () => {
                updatePaperSizeVisibility();
                handlePaperSettingsChange();
            });
        });

        fitModeRadios.forEach(radio => {
            radio.addEventListener('change', () => {
                updateMarginVisibility();
                if (radio.value === 'smaller') {
                    imgScaleSlider.value = 80;
                    imgScaleVal.innerText = '80%';
                    centerImageLayer();
                }
                handlePaperSettingsChange();
            });
        });

        imgScaleSlider.addEventListener('input', () => {
            imgScaleVal.innerText = imgScaleSlider.value + '%';
            
            const canvasW = container.clientWidth;
            const canvasH = container.clientHeight;
            if (canvasW && canvasH) {
                const oldLeft = parseFloat(imgWrapper.style.left) || 0;
                const oldTop = parseFloat(imgWrapper.style.top) || 0;
                const oldWidth = parseFloat(imgWrapper.style.width) || 0;
                const oldHeight = parseFloat(imgWrapper.style.height) || 0;
                const centerX = oldLeft + oldWidth / 2;
                const centerY = oldTop + oldHeight / 2;

                updateImageLayerPositionAndSize();

                const newWidth = parseFloat(imgWrapper.style.width) || 0;
                const newHeight = parseFloat(imgWrapper.style.height) || 0;
                const newLeft = centerX - newWidth / 2;
                const newTop = centerY - newHeight / 2;

                imgXPercent = (newLeft / canvasW) * 100;
                imgYPercent = (newTop / canvasH) * 100;

                updateImageLayerPositionAndSize();
            } else {
                updateImageLayerPositionAndSize();
            }
            updateFontPreview();
        });

        // Run once on load
        updatePdfOptionsVisibility();
        updatePaperSizeVisibility();
        updateMarginVisibility();
    </script>
</x-app-layout>