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
