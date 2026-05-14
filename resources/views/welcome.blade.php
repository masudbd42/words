@extends('layouts.app')

@section('content')
    <div class="mx-auto flex w-full max-w-6xl flex-col items-center justify-center py-20 text-center">
        <div class="relative group">
            <div class="absolute -inset-1 rounded-full bg-gradient-to-r from-indigo-500 to-emerald-500 opacity-20 blur group-hover:opacity-40 transition duration-1000 group-hover:duration-200"></div>
            <div class="relative flex h-24 w-24 items-center justify-center rounded-full glass text-3xl font-black text-white shadow-2xl">
                W
            </div>
        </div>

        <h1 class="mt-10 text-5xl font-extrabold tracking-tight text-white sm:text-7xl">
            Analyze documents with <span class="text-indigo-400">precision.</span>
        </h1>
        
        <p class="mt-8 max-w-2xl text-lg leading-relaxed text-slate-400">
            The professional standard for batch PDF keyword analysis. Fast, secure, and built for high-performance data extraction.
        </p>

        <div class="mt-12 flex flex-wrap items-center justify-center gap-6">
            <a href="{{ route('analysis.index') }}" class="inline-flex items-center gap-3 rounded-full accent-gradient px-10 py-5 text-sm font-bold text-slate-950 shadow-2xl shadow-indigo-500/40 transition-all hover:scale-105 hover:brightness-110 active:scale-95">
                Launch Dashboard
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </a>
            <a href="#" class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-10 py-5 text-sm font-bold text-slate-200 transition-all hover:border-white/20 hover:bg-white/10">
                View Docs
            </a>
        </div>

        <div class="mt-24 grid grid-cols-1 gap-8 sm:grid-cols-3 w-full">
            <div class="rounded-3xl glass p-8 text-left transition-all hover:translate-y-[-4px]">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-500/10 text-indigo-400 mb-6">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <h3 class="text-lg font-bold text-white">Turbo Processing</h3>
                <p class="mt-3 text-sm leading-relaxed text-slate-500">Optimized asynchronous engine handles hundreds of documents in seconds.</p>
            </div>
            <div class="rounded-3xl glass p-8 text-left transition-all hover:translate-y-[-4px]">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-500/10 text-emerald-400 mb-6">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.046A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <h3 class="text-lg font-bold text-white">Military Security</h3>
                <p class="mt-3 text-sm leading-relaxed text-slate-500">Your documents are processed in isolated environments and never stored permanently.</p>
            </div>
            <div class="rounded-3xl glass p-8 text-left transition-all hover:translate-y-[-4px]">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-sky-500/10 text-sky-400 mb-6">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/></svg>
                    <svg class="w-6 h-6 absolute ml-3 mt-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/></svg>
                </div>
                <h3 class="text-lg font-bold text-white">Advanced Insights</h3>
                <p class="mt-3 text-sm leading-relaxed text-slate-500">Detailed keyword metrics and cross-document comparison dashboards.</p>
            </div>
        </div>
    </div>
@endsection
