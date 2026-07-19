<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            
            <!-- Welcome Header -->
            <div class="bg-white/80 backdrop-blur-xl overflow-hidden shadow-lg rounded-2xl border border-white/40 p-8 relative">
                <div class="absolute top-0 right-0 -mt-16 -mr-16 w-64 h-64 bg-blue-400 rounded-full mix-blend-multiply filter blur-3xl opacity-20"></div>
                <div class="absolute bottom-0 left-0 -mb-16 -ml-16 w-64 h-64 bg-purple-400 rounded-full mix-blend-multiply filter blur-3xl opacity-20"></div>
                
                <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                    <div>
                        <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight">
                            Welcome back, {{ Auth::user()->name }}! 👋
                        </h2>
                        <p class="mt-2 text-lg text-gray-600 font-medium">
                            Ready to generate some beautiful certificates today?
                        </p>
                    </div>
                    
                    <a href="{{ route('workspace.index') }}" class="group relative inline-flex items-center justify-center px-6 py-3 text-base font-bold text-white bg-blue-600 rounded-full overflow-hidden shadow-xl shadow-blue-500/30 hover:bg-blue-700 transition-all hover:scale-105 active:scale-95">
                        <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white rounded-full group-hover:w-56 group-hover:h-56 opacity-10"></span>
                        Go to Workspace
                        <svg class="w-5 h-5 ml-2 -mr-1 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                    </a>
                </div>
            </div>

            <!-- Dashboard Stats / Quick Actions -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Card 1 -->
                <div class="bg-white/80 backdrop-blur-lg rounded-2xl shadow-md border border-white/50 p-6 hover:shadow-lg transition-shadow">
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900">Bulk Generation</h3>
                    <p class="text-gray-500 text-sm mt-2">Upload your Excel or CSV data to instantly generate thousands of personalized certificates in seconds.</p>
                </div>

                <!-- Card 2 -->
                <div class="bg-white/80 backdrop-blur-lg rounded-2xl shadow-md border border-white/50 p-6 hover:shadow-lg transition-shadow">
                    <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center text-purple-600 mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900">Visual Editor</h3>
                    <p class="text-gray-500 text-sm mt-2">Use the drag-and-drop workspace to preview exactly how the text aligns on your custom template design.</p>
                </div>

                <!-- Card 3 -->
                <div class="bg-white/80 backdrop-blur-lg rounded-2xl shadow-md border border-white/50 p-6 hover:shadow-lg transition-shadow">
                    <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center text-orange-600 mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900">Smart Export</h3>
                    <p class="text-gray-500 text-sm mt-2">Export your completed certificates into PDF, JPG, or PNG formats dynamically zipped for immediate download.</p>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
