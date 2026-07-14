<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Certificate Workspace') }}
        </h2>
    </x-slot>

    <div class="py-6 h-[calc(100vh-64px)] overflow-hidden">
        <div class="max-w-[1600px] mx-auto sm:px-6 lg:px-8 h-full flex gap-4">
            
            <div class="flex-grow bg-gray-200 rounded-xl relative overflow-hidden flex items-center justify-center p-10 border-2 border-dashed border-gray-300">
                
                <div id="v-guide" class="absolute left-1/2 top-0 bottom-0 w-0.5 bg-red-500 hidden z-10"></div>
                <div id="h-guide" class="absolute top-1/2 left-0 right-0 h-0.5 bg-red-500 hidden z-10"></div>

                <div id="canvas-container" class="relative shadow-2xl bg-white hidden">
                    <img id="preview-img" src="#" alt="Preview" class="max-w-full max-h-[70vh]">
                    
                    <div id="draggable-name" class="absolute cursor-move px-4 py-2 bg-blue-500/20 border-2 border-blue-500 text-blue-700 font-bold whitespace-nowrap select-none" style="top: 50%; left: 50%; transform: translate(-50%, -50%);">
                        Nama Penerima (X: <span id="coord-x">0</span>, Y: <span id="coord-y">0</span>)
                    </div>
                </div>

                <div id="placeholder-text" class="text-gray-500 text-center">
                    <i class="fa-solid fa-image text-5xl mb-3"></i>
                    <p>Silahkan upload template di sidebar kanan</p>
                </div>
            </div>

            <div class="relative flex h-full">
                
                <button onclick="toggleSidebar()" class="absolute left-0 top-1/2 -translate-y-1/2 -translate-x-1/2 bg-blue-600 text-white shadow-lg rounded-full w-8 h-8 flex items-center justify-center hover:bg-blue-700 transition-all duration-300 z-50 focus:outline-none">
                    <span id="toggle-btn-text" class="font-bold text-sm">&gt;</span>
                </button>

                <div id="sidebar" class="w-80 bg-white shadow-xl rounded-xl h-full transition-all duration-300 ease-in-out border-l border-gray-100 overflow-hidden">
                    <div id="sidebar-content" class="p-6 h-full overflow-y-auto w-80">
                        <h3 class="font-bold text-lg mb-4 text-gray-800">Pengaturan File</h3>
                        
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">1. Upload Template</label>
                            <input type="file" id="image-upload" accept="image/*" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">2. Data Nama (CSV)</label>
                            <input type="file" accept=".csv" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100" />
                        </div>

                        <hr class="my-6 border-gray-200">

                        <form action="{{ route('certificate.generate') }}" method="POST">
                            @csrf
                            <input type="hidden" name="x_pos" id="input-x">
                            <input type="hidden" name="y_pos" id="input-y">
                            
                            <button type="submit" class="w-full bg-blue-600 text-white font-bold py-3 rounded-lg hover:bg-blue-700 transition">
                                Generate Sertifikat
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const sidebar = document.getElementById('sidebar');
        const toggleIcon = document.getElementById('toggle-icon');
        const dragItem = document.getElementById('draggable-name');
        const container = document.getElementById('canvas-container');
        const imgUpload = document.getElementById('image-upload');
        const previewImg = document.getElementById('preview-img');
        const vGuide = document.getElementById('v-guide');
        const hGuide = document.getElementById('h-guide');

        // 1. FITUR TOGGLE SIDEBAR
        let sidebarOpen = true;

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const toggleBtnText = document.getElementById('toggle-btn-text');

            if (sidebarOpen) {
                // Sembunyikan sidebar (ciutkan lebarnya jadi 0)
                sidebar.style.width = '0px';
                sidebar.style.borderLeftWidth = '0px';
                toggleBtnText.innerText = '<'; // Ubah arah panah penunjuk saat ditutup
                sidebarOpen = false;
            } else {
                // Tampilkan kembali sidebar
                sidebar.style.width = '320px';
                sidebar.style.borderLeftWidth = '1px';
                toggleBtnText.innerText = '>'; // Ubah arah panah penunjuk saat dibuka
                sidebarOpen = true;
            }
        }

        // 2. PREVIEW GAMBAR SETELAH UPLOAD
        imgUpload.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    previewImg.src = event.target.result;
                    document.getElementById('canvas-container').classList.remove('hidden');
                    document.getElementById('placeholder-text').classList.add('hidden');
                }
                reader.readAsDataURL(file);
            }
        });

        // 3. FITUR DRAG & SMART GUIDES
        let isDragging = false;
        let startX, startY;

        dragItem.addEventListener('mousedown', (e) => {
            isDragging = true;
            dragItem.style.zIndex = 100;
        });

        document.addEventListener('mousemove', (e) => {
            if (!isDragging) return;

            const rect = container.getBoundingClientRect();
            let x = e.clientX - rect.left;
            let y = e.clientY - rect.top;

            // Constrain inside container
            x = Math.max(0, Math.min(x, rect.width));
            y = Math.max(0, Math.min(y, rect.height));

            // SMART GUIDES (SNAPPING TO CENTER)
            const centerX = rect.width / 2;
            const centerY = rect.height / 2;
            const threshold = 15; // Jarak magnet snapping

            if (Math.abs(x - centerX) < threshold) {
                x = centerX;
                vGuide.classList.remove('hidden');
            } else {
                vGuide.classList.add('hidden');
            }

            if (Math.abs(y - centerY) < threshold) {
                y = centerY;
                hGuide.classList.remove('hidden');
            } else {
                hGuide.classList.add('hidden');
            }

            // Update posisi (tengah elemen ke kursor)
            dragItem.style.left = x + 'px';
            dragItem.style.top = y + 'px';

            // Hitung persentase untuk backend agar responsif terhadap resolusi gambar asli
            const percentX = ((x / rect.width) * 100).toFixed(2);
            const percentY = ((y / rect.height) * 100).toFixed(2);

            document.getElementById('coord-x').innerText = percentX + '%';
            document.getElementById('coord-y').innerText = percentY + '%';
            document.getElementById('input-x').value = percentX;
            document.getElementById('input-y').value = percentY;
        });

        document.addEventListener('mouseup', () => {
            isDragging = false;
            vGuide.classList.add('hidden');
            hGuide.classList.add('hidden');
        });
    </script>
</x-app-layout>