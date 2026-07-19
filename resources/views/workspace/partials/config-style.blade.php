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
