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
