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
