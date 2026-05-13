@extends('layouts.app')

@section('content')
    <div class="mx-auto flex w-full max-w-5xl flex-col gap-8">
        <div>
            <p class="text-sm font-semibold uppercase tracking-wide text-slate-400">PDF Keyword Analyzer</p>
            <h1 class="mt-2 text-3xl font-semibold text-white sm:text-4xl">
                Analyze keyword frequency across multiple PDFs
            </h1>
            <p class="mt-3 text-base text-slate-300">
                Upload your documents, set target keywords, and track exact word matches with an asynchronous queue.
            </p>
        </div>

        @if ($errors->any())
            <div class="rounded-2xl border border-rose-500/40 bg-rose-500/10 p-4 text-sm text-rose-100">
                <p class="font-semibold">Please fix the following:</p>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('analysis.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <div class="rounded-2xl border border-slate-800 bg-slate-900/70 p-6 shadow-lg shadow-slate-900/40">
                <label for="keywords" class="text-sm font-semibold text-slate-200">Keywords</label>
                <textarea
                    id="keywords"
                    name="keywords"
                    rows="4"
                    placeholder="e.g. revenue, margin, net income"
                    class="mt-2 w-full rounded-xl border border-slate-800 bg-slate-950/80 px-4 py-3 text-sm text-slate-100 placeholder:text-slate-600 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/40"
                >{{ old('keywords') }}</textarea>
                <p class="mt-2 text-xs text-slate-400">
                    Separate keywords with commas. Matching is case-insensitive and uses word boundaries.
                </p>
            </div>

            <div class="rounded-2xl border border-slate-800 bg-slate-900/70 p-6 shadow-lg shadow-slate-900/40">
                <label for="documents" class="text-sm font-semibold text-slate-200">PDF documents</label>
                <label
                    id="drop-zone"
                    for="documents"
                    class="mt-3 flex cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed border-slate-700 bg-slate-950/80 px-6 py-10 text-center transition hover:border-indigo-500"
                >
                    <span class="text-sm font-medium text-slate-200">Drag &amp; drop PDFs here</span>
                    <span class="mt-1 text-xs text-slate-500">or click to browse files</span>
                    <input
                        id="documents"
                        name="documents[]"
                        type="file"
                        class="hidden"
                        multiple
                        accept="application/pdf"
                    />
                </label>
                <ul id="file-list" class="mt-4 space-y-2 text-sm text-slate-300"></ul>
                <p class="mt-2 text-xs text-slate-400">PDF only. Max file size: 10MB each.</p>
            </div>

            <div class="flex flex-wrap items-center gap-4">
                <button
                    type="submit"
                    class="inline-flex items-center justify-center rounded-full bg-indigo-500 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-500/30 transition hover:bg-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/40"
                >
                    Start analysis
                </button>
                <p class="text-xs text-slate-400">
                    Each file is processed asynchronously, so you can keep working while the queue runs.
                </p>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        const dropZone = document.getElementById('drop-zone');
        const fileInput = document.getElementById('documents');
        const fileList = document.getElementById('file-list');

        const renderFiles = () => {
            if (!fileList) {
                return;
            }

            fileList.innerHTML = '';
            Array.from(fileInput.files).forEach((file) => {
                const item = document.createElement('li');
                item.className = 'flex items-center justify-between rounded-lg border border-slate-800 bg-slate-950/60 px-4 py-2';
                item.textContent = `${file.name} · ${(file.size / 1024 / 1024).toFixed(2)} MB`;
                fileList.appendChild(item);
            });
        };

        if (dropZone && fileInput) {
            dropZone.addEventListener('dragover', (event) => {
                event.preventDefault();
                dropZone.classList.add('border-indigo-500');
            });

            dropZone.addEventListener('dragleave', () => {
                dropZone.classList.remove('border-indigo-500');
            });

            dropZone.addEventListener('drop', (event) => {
                event.preventDefault();
                dropZone.classList.remove('border-indigo-500');

                const transfer = new DataTransfer();
                Array.from(event.dataTransfer.files).forEach((file) => transfer.items.add(file));
                fileInput.files = transfer.files;
                renderFiles();
            });

            fileInput.addEventListener('change', renderFiles);
        }
    </script>
@endpush
