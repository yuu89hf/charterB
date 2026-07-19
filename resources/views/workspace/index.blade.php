<x-app-layout>
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
    <link rel="stylesheet" href="{{ asset('css/workspace-editor.css') }}">
    
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Workspace') }}
        </h2>
    </x-slot>

    @include('workspace.partials.loading-overlay')

    <div class="py-6 h-[calc(100vh-64px)] overflow-hidden">
        <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 h-full flex flex-col lg:flex-row gap-4 relative overflow-hidden">
            
            @include('workspace.partials.canvas-preview')
            @include('workspace.partials.sidebar-form')

        </div>
    </div>

    <script src="{{ asset('js/workspace-editor.js') }}"></script>
</x-app-layout>