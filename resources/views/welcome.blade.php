<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CharterB - Premium Certificate Generator</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,600,700,900&display=swap" rel="stylesheet" />

    <!-- Styles / Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            color: #0f172a;
            -webkit-font-smoothing: antialiased;
        }

        /* Subtle background gradient */
        .bg-gradient-premium {
            background: linear-gradient(135deg, #e0e7ff 0%, #f1f5f9 50%, #dbeafe 100%);
        }

        /* Glassmorphism utility */
        .glass {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.4);
        }

        /* Micro-animations */
        .hover-lift {
            transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.3s ease;
        }
        .hover-lift:hover {
            transform: translateY(-5px) scale(1.02);
            box-shadow: 0 20px 40px -10px rgba(59, 130, 246, 0.2);
        }

        .fade-in-up {
            animation: fadeInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            opacity: 0;
            transform: translateY(20px);
        }

        .delay-100 { animation-delay: 100ms; }
        .delay-200 { animation-delay: 200ms; }
        .delay-300 { animation-delay: 300ms; }

        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Floating shapes animation */
        @keyframes float {
            0% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(5deg); }
            100% { transform: translateY(0px) rotate(0deg); }
        }
        
        .floating-shape-1 { animation: float 6s ease-in-out infinite; }
        .floating-shape-2 { animation: float 8s ease-in-out infinite reverse; }
    </style>
</head>
<body class="bg-gradient-premium min-h-screen flex flex-col overflow-x-hidden relative">

    <!-- Decorative background blobs -->
    <div class="absolute top-[-10%] left-[-10%] w-[40rem] h-[40rem] bg-blue-300 rounded-full mix-blend-multiply filter blur-[100px] opacity-40 floating-shape-1 pointer-events-none"></div>
    <div class="absolute top-[20%] right-[-10%] w-[35rem] h-[35rem] bg-purple-300 rounded-full mix-blend-multiply filter blur-[100px] opacity-40 floating-shape-2 pointer-events-none"></div>

    <!-- Navigation -->
    <header class="glass sticky top-0 z-50 transition-all duration-300 w-full border-b border-white/50 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <div class="flex items-center gap-2 cursor-pointer hover:opacity-80 transition-opacity">
                    <img src="{{ asset('images/logo.png') }}" alt="CharterB Logo" class="w-10 h-10 object-contain drop-shadow-md">
                    <span class="text-2xl font-black tracking-tight bg-clip-text text-transparent bg-gradient-to-r from-blue-700 to-indigo-800">
                        CharterB
                    </span>
                </div>
                
                @if (Route::has('login'))
                    <nav class="flex items-center gap-4">
                        @auth
                            <a href="{{ url('/workspace') }}" class="text-sm font-semibold text-gray-700 hover:text-blue-600 transition-colors">Workspace</a>
                            <a href="{{ url('/dashboard') }}" class="text-sm font-semibold text-gray-700 hover:text-blue-600 transition-colors">Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="text-sm font-bold text-gray-600 hover:text-blue-600 transition-colors px-4 py-2">Log in</a>

                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="text-sm font-bold bg-blue-600 text-white px-6 py-2.5 rounded-full hover:bg-blue-700 hover:shadow-lg hover:shadow-blue-500/30 transition-all active:scale-95">
                                    Get Started
                                </a>
                            @endif
                        @endauth
                    </nav>
                @endif
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <main class="flex-grow flex items-center relative z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-32 w-full">
            <div class="text-center max-w-4xl mx-auto">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass border border-blue-200 text-blue-700 text-sm font-semibold mb-8 fade-in-up shadow-sm">
                    <span class="relative flex h-3 w-3">
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-3 w-3 bg-blue-500"></span>
                    </span>
                    The Ultimate Certificate Engine
                </div>
                
                <h1 class="text-5xl md:text-7xl font-black text-gray-900 tracking-tight leading-[1.1] mb-8 fade-in-up delay-100">
                    Generate Certificates <br class="hidden md:block" />
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-indigo-600">at Lightning Speed.</span>
                </h1>
                
                <p class="mt-4 text-lg md:text-xl text-gray-600 mb-12 max-w-2xl mx-auto font-medium leading-relaxed fade-in-up delay-200">
                    Drag, drop, and bulk-generate thousands of personalized certificates in seconds. Stop wasting time on manual data entry and start celebrating achievements.
                </p>
                
                <div class="flex flex-col sm:flex-row gap-4 justify-center fade-in-up delay-300">
                    @auth
                        <a href="{{ route('workspace.index') }}" class="group relative inline-flex items-center justify-center px-8 py-4 text-lg font-bold text-white bg-blue-600 rounded-full overflow-hidden shadow-xl shadow-blue-500/20 hover:bg-blue-700 hover-lift active:scale-95">
                            <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white rounded-full group-hover:w-56 group-hover:h-56 opacity-10"></span>
                            Go to Workspace
                            <svg class="w-5 h-5 ml-2 -mr-1 transition-transform group-hover:translate-x-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                        </a>
                    @else
                        <a href="{{ route('register') }}" class="group relative inline-flex items-center justify-center px-8 py-4 text-lg font-bold text-white bg-blue-600 rounded-full overflow-hidden shadow-xl shadow-blue-500/20 hover:bg-blue-700 hover-lift active:scale-95">
                            <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white rounded-full group-hover:w-56 group-hover:h-56 opacity-10"></span>
                            Create Free Account
                            <svg class="w-5 h-5 ml-2 -mr-1 transition-transform group-hover:translate-x-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                        </a>
                    @endauth
                    
                    <a href="#features" class="inline-flex items-center justify-center px-8 py-4 text-lg font-bold text-gray-700 bg-white/80 backdrop-blur border border-gray-200 rounded-full hover:bg-white hover:text-blue-600 hover-lift shadow-sm active:scale-95 transition-all">
                        Learn More
                    </a>
                </div>
            </div>
        </div>
    </main>

    <!-- Features Section -->
    <section id="features" class="relative z-10 py-20 bg-white/40 backdrop-blur-md border-t border-white/60">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-black text-gray-900 tracking-tight">Everything you need to automate credentials</h2>
            </div>
            
            <div class="grid md:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="glass p-8 rounded-3xl hover-lift">
                    <div class="w-14 h-14 rounded-2xl bg-blue-100 flex items-center justify-center mb-6 text-blue-600">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Bulk Processing</h3>
                    <p class="text-gray-600 font-medium leading-relaxed">Upload a CSV or Excel file containing hundreds of names and let our engine map them instantly.</p>
                </div>
                
                <!-- Feature 2 -->
                <div class="glass p-8 rounded-3xl hover-lift">
                    <div class="w-14 h-14 rounded-2xl bg-indigo-100 flex items-center justify-center mb-6 text-indigo-600">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Visual Editor</h3>
                    <p class="text-gray-600 font-medium leading-relaxed">Drag and drop recipient names directly onto your template with pixel-perfect precision and smart guides.</p>
                </div>
                
                <!-- Feature 3 -->
                <div class="glass p-8 rounded-3xl hover-lift">
                    <div class="w-14 h-14 rounded-2xl bg-purple-100 flex items-center justify-center mb-6 text-purple-600">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Premium Export</h3>
                    <p class="text-gray-600 font-medium leading-relaxed">Export generated certificates as crisp PNG, JPG, or PDF formats packaged directly into a downloadable ZIP archive.</p>
                </div>
            </div>
        </div>
    </section>

    <footer class="bg-white border-t border-gray-100 mt-auto py-8 relative z-10 text-center">
        <p class="text-gray-500 font-medium text-sm">
            &copy; {{ date('Y') }} CharterB. All rights reserved. Built with precision.
        </p>
    </footer>

</body>
</html>