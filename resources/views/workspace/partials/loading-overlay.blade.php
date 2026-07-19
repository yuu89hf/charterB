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
