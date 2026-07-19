<div id="sidebar-container" class="lg:relative fixed inset-y-0 right-0 z-40 flex h-full transition-transform duration-300 ease-in-out translate-x-full lg:translate-x-0">
    <button onclick="toggleSidebar()" class="absolute left-0 top-1/2 -translate-y-1/2 -translate-x-1/2 bg-blue-600 text-white shadow-lg rounded-full w-8 h-8 flex items-center justify-center hover:bg-blue-700 transition-all duration-300 z-50 focus:outline-none">
        <span id="toggle-btn-text" class="font-bold text-sm">&lt;</span>
    </button>

    <div id="sidebar" class="w-80 bg-white shadow-xl rounded-l-xl lg:rounded-xl h-full border-l border-gray-100 overflow-hidden">
        <div id="sidebar-content" class="p-6 h-full overflow-y-auto w-80">
            <h3 class="font-bold text-lg mb-4 text-gray-800">File Settings</h3>

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

                @include('workspace.partials.config-file')
                @include('workspace.partials.config-style')
                @include('workspace.partials.config-export')

            </form>
        </div>
    </div>
</div>
