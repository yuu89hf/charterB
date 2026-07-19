<x-app-layout>
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
    <link rel="stylesheet" href="{{ asset('css/workspace-editor.css') }}">
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

                        <form id="generate-form" action="{{ route('workspace.generate') }}" method="POST" enctype="multipart/form-data">
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
                                                        <input type="radio" name="fit_mode" value="smaller" checked class="form-radio text-orange-500 focus:ring-orange-400">
                                                        <span class="ml-2 text-xs text-gray-600 font-medium">Custom</span>
                                                    </label>
                                                    <label class="inline-flex items-center cursor-pointer">
                                                        <input type="radio" name="fit_mode" value="full" class="form-radio text-orange-500 focus:ring-orange-400">
                                                        <span class="ml-2 text-xs text-gray-600 font-medium">Full Page</span>
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

    <script src="{{ asset('js/workspace-editor.js') }}"></script>
</x-app-layout>