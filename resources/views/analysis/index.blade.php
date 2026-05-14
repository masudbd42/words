@extends('layouts.app')

@section('content')
    <div class="mx-auto flex w-full max-w-6xl flex-col gap-8">
        <section class="grid gap-6 lg:grid-cols-2">
            <div class="rounded-4xl glass-darker p-8 sm:p-10 flex flex-col justify-center">
                <p class="text-[10px] font-bold uppercase tracking-[0.3em] text-indigo-400">PDF Keyword Analyzer</p>
                <h1 class="mt-4 text-3xl font-bold tracking-tight text-white sm:text-4xl lg:text-5xl leading-[1.1]">
                    Professional batch <span class="text-indigo-400">PDF analysis.</span>
                </h1>
                <p class="mt-6 text-sm leading-relaxed text-slate-400 max-w-md">
                    Extract insights from multiple documents simultaneously. Define your parameters and let our asynchronous engine handle the processing.
                </p>

                <div class="mt-6 inline-flex items-start gap-3 rounded-2xl bg-indigo-500/10 border border-indigo-500/20 p-4 max-w-md">
                    <svg class="w-4 h-4 text-indigo-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="text-[10px] font-bold text-indigo-300/80 leading-relaxed uppercase tracking-wider">
                        Scaling Tip: If your batch is capped at 20 files, increase <code class="text-white">max_file_uploads</code> in your Laragon php.ini to 200.
                    </p>
                </div>
                
                <div class="mt-10 flex items-center gap-6">
                    <div class="flex flex-col">
                        <span class="text-xl font-bold text-white">99.9%</span>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Precision</span>
                    </div>
                    <div class="h-8 w-px bg-white/10"></div>
                    <div class="flex flex-col">
                        <span class="text-xl font-bold text-white">&lt;2s</span>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Processing</span>
                    </div>
                    <div class="h-8 w-px bg-white/10"></div>
                    <div class="flex flex-col">
                        <span class="text-xl font-bold text-white">∞</span>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Scale</span>
                    </div>
                </div>
            </div>

            <div class="grid gap-6">
                <div class="rounded-4xl glass p-8">
                    <div class="flex items-center gap-4">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-500/10 text-indigo-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </div>
                        <div>
                            <h2 class="text-sm font-bold text-white">Intelligent Extraction</h2>
                            <p class="text-xs text-slate-400 mt-1">Smart word-boundary matching logic.</p>
                        </div>
                    </div>
                </div>
                <div class="rounded-4xl glass p-8">
                    <div class="flex items-center gap-4">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.046A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        </div>
                        <div>
                            <h2 class="text-sm font-bold text-white">Secure Processing</h2>
                            <p class="text-xs text-slate-400 mt-1">Encrypted document handling pipeline.</p>
                        </div>
                    </div>
                </div>
                <div class="rounded-4xl glass p-8">
                    <div class="flex items-center gap-4">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-sky-500/10 text-sky-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/></svg>
                        </div>
                        <div>
                            <h2 class="text-sm font-bold text-white">Live Telemetry</h2>
                            <p class="text-xs text-slate-400 mt-1">Real-time queue monitoring dashboard.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        @if ($errors->any())
            <div class="rounded-2xl border border-rose-500/20 bg-rose-500/5 px-6 py-4 text-sm text-rose-200">
                <div class="flex items-center gap-3 font-bold uppercase tracking-wider text-[10px]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    Action Required
                </div>
                <ul class="mt-3 list-disc space-y-1 pl-5 text-rose-100/70">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div id="upload-overlay" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 backdrop-blur-sm transition-all shadow-2xl">
            <div class="w-full max-w-md rounded-[2.5rem] glass p-10 text-center shadow-2xl">
                <div class="relative mx-auto mb-8 h-24 w-24">
                    <svg class="h-full w-full -rotate-90" viewBox="0 0 100 100">
                        <circle class="text-white/5" stroke-width="8" stroke="currentColor" fill="transparent" r="40" cx="50" cy="50"/>
                        <circle id="upload-circle" class="text-indigo-500 transition-all duration-300" stroke-width="8" stroke-dasharray="251.2" stroke-dashoffset="251.2" stroke-linecap="round" stroke="currentColor" fill="transparent" r="40" cx="50" cy="50"/>
                    </svg>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <span id="upload-percent" class="text-xl font-black text-white">0%</span>
                    </div>
                </div>
                <h3 class="text-lg font-bold text-white mb-2">Uploading Documents</h3>
                <p id="upload-status" class="text-xs font-medium text-slate-400">Preparing batch synchronization...</p>
                
                <div id="upload-error" class="mt-6 hidden rounded-2xl bg-rose-500/10 border border-rose-500/20 p-4 text-[10px] font-bold text-rose-400 uppercase tracking-widest"></div>
            </div>
        </div>

        <form id="analysis-form" class="grid gap-8 lg:grid-cols-2">
            @csrf
            {{-- Form fields... --}}

            <div class="flex flex-col gap-4">
                <div class="rounded-4xl glass p-8 flex-1">
                    <div class="flex items-center justify-between">
                        <label for="keywords" class="text-sm font-bold text-white tracking-tight">Keywords Configuration</label>
                        <span class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Required</span>
                    </div>
                    <div class="mt-6 relative group">
                        <textarea
                            id="keywords"
                            name="keywords"
                            rows="5"
                            placeholder="e.g. revenue, margin, net income, user growth"
                            class="w-full rounded-2xl border border-white/5 bg-slate-950/40 px-5 py-4 text-sm text-slate-200 placeholder:text-slate-600 focus:border-indigo-500/50 focus:outline-none focus:ring-4 focus:ring-indigo-500/10 transition-all resize-none"
                        >{{ old('keywords') }}</textarea>
                        <div class="absolute inset-0 rounded-2xl pointer-events-none border border-white/5 group-focus-within:border-indigo-500/30 transition-colors"></div>
                    </div>
                    <div class="mt-4 flex items-start gap-3 p-4 rounded-xl bg-white/5 border border-white/5">
                        <svg class="w-4 h-4 text-indigo-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <p class="text-xs leading-relaxed text-slate-400">
                            Matching is case-insensitive. Use commas to separate multiple terms.
                        </p>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-4">
                <div class="rounded-4xl glass p-8 flex-1 flex flex-col">
                    <div class="flex items-center justify-between">
                        <label for="documents" class="text-sm font-bold text-white tracking-tight">Document Upload</label>
                        <div class="flex items-center gap-3">
                            <span id="file-count" class="hidden text-[10px] font-bold uppercase tracking-widest text-indigo-400">0 Files Selected</span>
                            <span class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Max 200 PDFs</span>
                        </div>
                    </div>
                    
                    <div class="mt-6">
                        <label
                            id="drop-zone"
                            for="documents"
                            class="relative flex cursor-pointer flex-col items-center justify-center rounded-4xl border-2 border-dashed border-white/10 bg-slate-950/20 px-6 py-10 text-center transition-all hover:border-indigo-500/40 hover:bg-indigo-500/5 active:scale-[0.98]"
                        >
                            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white/5 text-slate-400 mb-4 transition-transform group-hover:scale-110">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                            </div>
                            <span class="text-sm font-bold text-white">Drag & drop documents</span>
                            <span class="mt-2 text-xs text-slate-500">Support for batch uploading up to 200 files</span>
                            <input
                                id="documents"
                                name="documents[]"
                                type="file"
                                class="hidden"
                                multiple
                                accept="application/pdf"
                            />
                        </label>
                    </div>
                    
                    <div class="mt-6 flex-1 min-h-0">
                        <ul id="file-list" class="max-h-75 overflow-y-auto pr-2 space-y-3 scrollbar-thin scrollbar-thumb-white/10 scrollbar-track-transparent"></ul>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-2 flex flex-col md:flex-row items-center justify-between gap-6 rounded-4xl glass-darker px-8 py-6">
                <div class="flex items-center gap-4">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-500/20 text-emerald-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <p class="text-xs font-medium text-slate-400">
                        System ready for asynchronous batch processing.
                    </p>
                </div>
                
                <button
                    type="submit"
                    class="w-full md:w-auto inline-flex items-center justify-center gap-3 rounded-full accent-gradient px-8 py-4 text-sm font-bold text-slate-950 shadow-2xl shadow-indigo-500/40 transition-all hover:scale-[1.02] hover:brightness-110 active:scale-95"
                >
                    Initialize Analysis
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </button>
            </div>
            <div class="col-span-full">
                <div class="rounded-4xl glass-darker p-8 sm:p-10 border border-white/5">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                        <div class="flex items-center gap-4">
                            <div class="h-12 w-12 rounded-2xl bg-indigo-500/20 flex items-center justify-center text-indigo-400">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-white tracking-tight">Analysis Intelligence Depth</h3>
                                <p class="text-xs text-slate-500 mt-1 uppercase tracking-widest font-bold">Configure the discovery threshold for semantic word patterns</p>
                            </div>
                        </div>

                        <div class="flex items-center p-1.5 rounded-2xl bg-slate-900/50 border border-white/5">
                            <label class="relative cursor-pointer">
                                <input type="radio" name="word_limit" value="10" class="peer sr-only">
                                <span class="px-6 py-2.5 rounded-xl text-xs font-bold text-slate-500 transition-all peer-checked:bg-indigo-500 peer-checked:text-white inline-block">Top 10</span>
                            </label>
                            <label class="relative cursor-pointer">
                                <input type="radio" name="word_limit" value="20" class="peer sr-only" checked>
                                <span class="px-6 py-2.5 rounded-xl text-xs font-bold text-slate-500 transition-all peer-checked:bg-indigo-500 peer-checked:text-white inline-block">Top 20</span>
                            </label>
                            <label class="relative cursor-pointer">
                                <input type="radio" name="word_limit" value="50" class="peer sr-only">
                                <span class="px-6 py-2.5 rounded-xl text-xs font-bold text-slate-500 transition-all peer-checked:bg-indigo-500 peer-checked:text-white inline-block">Top 50</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        const form = document.getElementById('analysis-form');
        const dropZone = document.getElementById('drop-zone');
        const fileInput = document.getElementById('documents');
        const fileList = document.getElementById('file-list');
        const fileCountIndicator = document.getElementById('file-count');
        
        const overlay = document.getElementById('upload-overlay');
        const uploadCircle = document.getElementById('upload-circle');
        const uploadPercent = document.getElementById('upload-percent');
        const uploadStatus = document.getElementById('upload-status');
        const uploadError = document.getElementById('upload-error');

        const renderFiles = () => {
            if (!fileList) return;
            const files = Array.from(fileInput.files);
            const count = files.length;

            if (fileCountIndicator) {
                fileCountIndicator.textContent = `${count} Files Selected`;
                fileCountIndicator.classList.toggle('hidden', count === 0);
                fileCountIndicator.classList.remove('text-rose-400');
                fileCountIndicator.classList.add('text-indigo-400');
            }

            fileList.innerHTML = '';
            files.forEach((file) => {
                const item = document.createElement('li');
                item.className = 'flex items-center justify-between rounded-2xl glass px-5 py-4 text-slate-200 animate-in fade-in slide-in-from-bottom-2 duration-300';
                item.innerHTML = `
                    <div class="flex items-center gap-3 overflow-hidden">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-500/10 text-indigo-400 shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <span class="truncate text-xs font-bold">${file.name}</span>
                    </div>
                    <span class="shrink-0 text-[10px] font-bold text-slate-500 bg-white/5 px-2 py-1 rounded-md uppercase tracking-wider">${(file.size / 1024 / 1024).toFixed(2)} MB</span>
                `;
                fileList.appendChild(item);
            });
        };

        if (dropZone && fileInput) {
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, e => {
                    e.preventDefault();
                    e.stopPropagation();
                }, false);
            });

            dropZone.addEventListener('dragover', () => {
                dropZone.classList.add('border-indigo-500/60', 'bg-indigo-500/10');
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, () => {
                    dropZone.classList.remove('border-indigo-500/60', 'bg-indigo-500/10');
                });
            });

            dropZone.addEventListener('drop', (event) => {
                const transfer = new DataTransfer();
                Array.from(event.dataTransfer.files).forEach((file) => {
                    if (file.type === 'application/pdf') {
                        transfer.items.add(file);
                    }
                });
                fileInput.files = transfer.files;
                renderFiles();
            });

            fileInput.addEventListener('change', renderFiles);
        }

        const submitBtn = form.querySelector('button[type="submit"]');

        const sleep = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

        const uploadDocumentWithRetry = async (uploadUrl, file, attemptLimit = 4) => {
            let lastError = null;

            for (let attempt = 1; attempt <= attemptLimit; attempt++) {
                try {
                    const formData = new FormData();
                    formData.append('document', file);

                    const uploadResponse = await fetch(uploadUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: formData
                    });

                    if (!uploadResponse.ok) {
                        const errData = await uploadResponse.json().catch(() => ({}));
                        const error = new Error(errData.message || `Upload failed: ${uploadResponse.status}`);
                        error.status = uploadResponse.status;
                        throw error;
                    }

                    return await uploadResponse.json();
                } catch (error) {
                    lastError = error;

                    const retryable = !error.status || error.status >= 500;
                    if (attempt >= attemptLimit || !retryable) {
                        break;
                    }

                    const delay = Math.min(8000, 500 * (2 ** (attempt - 1)));
                    uploadStatus.textContent = `Retrying upload after ${delay / 1000}s backoff (${attempt}/${attemptLimit - 1})...`;
                    await sleep(delay);
                }
            }

            throw lastError || new Error('Upload failed after retries.');
        };

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const files = Array.from(fileInput.files);
            const keywords = document.getElementById('keywords').value;
            const wordLimit = form.querySelector('input[name="word_limit"]:checked').value;

            if (!keywords || files.length === 0) return;

            // Prevent double submission
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-50', 'cursor-not-allowed');

            // Show Overlay
            overlay.style.display = 'flex';
            uploadError.classList.add('hidden');
            uploadStatus.textContent = 'Establishing secure connection...';
            uploadPercent.textContent = '0%';
            uploadCircle.style.strokeDashoffset = '251.2';

            try {
                // Phase 1: Initialize Batch
                uploadStatus.textContent = 'Initializing batch on server...';
                const initResponse = await fetch('{{ route('analysis.init') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ keywords, total_files: files.length, word_limit: wordLimit })
                });

                if (!initResponse.ok) {
                    const errData = await initResponse.json().catch(() => ({}));
                    throw new Error(errData.message || 'Server connection failed during initialization.');
                }
                
                const batch = await initResponse.json();

                // Phase 2: Sequential Upload
                const uploadRouteBase = '{{ route('analysis.upload', ['analysisBatch' => 0]) }}';

                for (let i = 0; i < files.length; i++) {
                    const file = files[i];
                    const percent = Math.round(((i + 1) / files.length) * 100);
                    const offset = 251.2 - (251.2 * percent) / 100;

                    uploadStatus.textContent = `Syncing document ${i + 1} of ${files.length}...`;
                    uploadPercent.textContent = `${percent}%`;
                    uploadCircle.style.strokeDashoffset = offset;

                    const uploadUrl = uploadRouteBase.replace('/0/', `/${batch.id}/`);
                    const result = await uploadDocumentWithRetry(uploadUrl, file);
                    uploadStatus.textContent = `Document ${i + 1} synced${result.document_id ? ` (#${result.document_id})` : ''}. Awaiting analysis...`;
                }

                uploadStatus.textContent = 'Synchronized! Redirecting to workspace...';
                setTimeout(() => {
                    window.location.href = batch.redirect;
                }, 500);

            } catch (err) {
                console.error('Batch Error:', err);
                uploadError.classList.remove('hidden');
                uploadError.textContent = `CRITICAL: ${err.message}`;
                
                // Re-enable form
                submitBtn.disabled = false;
                submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                
                setTimeout(() => {
                    overlay.style.display = 'none';
                }, 5000);
            }
        });
    </script>
@endpush
