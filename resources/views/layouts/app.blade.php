<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Laravel') }}</title>

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="min-h-screen bg-[#020617] text-slate-200 antialiased font-sans">
        {{-- High-performance background --}}
        <div class="fixed inset-0 -z-10 bg-[radial-gradient(ellipse_at_top,_oklch(0.25_0.05_260)_0%,_transparent_70%),radial-gradient(ellipse_at_bottom_right,_oklch(0.2_0.05_200)_0%,_transparent_50%)]"></div>
        <div class="fixed inset-0 -z-10 opacity-[0.03] [background-image:radial-gradient(rgba(255,255,255,0.2)_1px,transparent_1px)] [background-size:32px_32px]"></div>

        <main class="mx-auto flex min-h-screen w-full max-w-7xl flex-col px-4 py-6 sm:px-6 lg:px-8">
            <header class="mb-8 flex flex-col gap-4 rounded-3xl glass px-6 py-4 md:flex-row md:items-center md:justify-between transition-all duration-300">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl accent-gradient text-lg font-black text-slate-950 shadow-xl shadow-indigo-500/20 transition-transform hover:scale-105 active:scale-95">
                        W
                    </div>
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-[0.3em] text-indigo-400/80">Premium Analysis</p>
                        <h1 class="text-base font-bold tracking-tight text-white sm:text-lg">Word Analytics Dashboard</h1>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <nav class="hidden md:flex items-center gap-1 mr-4">
                        <a href="#" class="px-4 py-2 text-xs font-medium text-slate-400 hover:text-white transition-colors">Documentation</a>
                        <a href="#" class="px-4 py-2 text-xs font-medium text-slate-400 hover:text-white transition-colors">Support</a>
                    </nav>
                    <a href="{{ route('analysis.index') }}" class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-5 py-2.5 text-xs font-bold text-slate-200 transition-all hover:border-indigo-400/40 hover:bg-indigo-400/10 hover:text-white active:scale-95">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        New Analysis
                    </a>
                </div>
            </header>

            <section class="flex-1">
                @yield('content')
            </section>
            
            <footer class="mt-12 py-8 border-t border-white/5 text-center">
                <p class="text-[10px] font-medium uppercase tracking-[0.2em] text-slate-500">
                    &copy; {{ date('Y') }} Word Analytics Portal. Built for performance.
                </p>
            </footer>
        </main>

        @stack('scripts')
    </body>
</html>
