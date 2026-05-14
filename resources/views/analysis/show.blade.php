@extends('layouts.app')

@section('content')
    @php
        $formatBytes = static function (?int $bytes): string {
            if (! $bytes || $bytes <= 0) {
                return '—';
            }

            if ($bytes < 1024) {
                return $bytes . ' B';
            }

            if ($bytes < 1024 * 1024) {
                return number_format($bytes / 1024, 1) . ' KB';
            }

            return number_format($bytes / (1024 * 1024), 1) . ' MB';
        };

        $renderMetadataValue = static function ($value): string {
            if (is_array($value)) {
                $parts = [];

                foreach ($value as $key => $item) {
                    if (is_array($item)) {
                        $parts[] = $key . ': ' . implode(', ', array_filter(array_map('strval', $item)));
                        continue;
                    }

                    if (filled($item)) {
                        $parts[] = is_string($key) ? $item : (string) $item;
                    }
                }

                return $parts !== [] ? implode(' · ', $parts) : '—';
            }

            return filled($value) ? (string) $value : '—';
        };
    @endphp
    <div class="mx-auto flex w-full max-w-6xl flex-col gap-8">
        <section class="grid gap-6 lg:grid-cols-[1fr_auto]">
            <div class="rounded-[2.5rem] glass-darker p-8 sm:p-10">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-[0.3em] text-indigo-400">Analysis Session</p>
                        <h1 class="mt-3 text-3xl font-bold tracking-tight text-white sm:text-4xl">
                            Batch Intelligence <span class="text-slate-500">#{{ $analysisBatch->id }}</span>
                        </h1>
                        <div class="mt-4 flex items-center gap-4 text-xs font-medium text-slate-400">
                            <span class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                {{ $analysisBatch->documents->count() }} Documents
                            </span>
                            <span class="h-1 w-1 rounded-full bg-slate-700"></span>
                            <span>{{ $analysisBatch->created_at->format('M d, Y · H:i') }}</span>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-2">
                        <button onclick="window.location.reload()" class="p-3 rounded-full glass hover:bg-white/5 text-slate-400 hover:text-white transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        </button>
                        <a href="{{ route('analysis.index') }}" class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-6 py-3 text-xs font-bold text-slate-200 transition-all hover:border-indigo-400/40 hover:bg-indigo-400/10 hover:text-white">
                            Restart Analysis
                        </a>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-3">
                    <div class="rounded-2xl bg-white/5 border border-white/5 p-4">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Processed</p>
                        <p class="mt-1 text-xl font-bold text-white">{{ $processed }}</p>
                    </div>
                    <div class="rounded-2xl bg-white/5 border border-white/5 p-4">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Pending</p>
                        <p class="mt-1 text-xl font-bold text-amber-400">{{ $pending }}</p>
                    </div>
                    <div class="rounded-2xl bg-white/5 border border-white/5 p-4">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Ready</p>
                        <p class="mt-1 text-xl font-bold text-emerald-400">{{ $completed }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-[2.5rem] glass p-8 min-w-[320px]">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-sm font-bold text-white tracking-tight">Queue Status</h2>
                    <div class="flex items-center gap-2 px-2 py-1 rounded-md bg-indigo-500/10 text-[10px] font-bold text-indigo-400 uppercase tracking-widest">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-indigo-500"></span>
                        </span>
                        Live
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="rounded-2xl bg-white/5 border border-white/5 p-4 transition-colors hover:bg-white/[0.07]">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Completed</p>
                        <p class="mt-1 text-xl font-bold text-emerald-400"><span id="completed-count">{{ $completed }}</span></p>
                    </div>
                    <div class="rounded-2xl bg-white/5 border border-white/5 p-4 transition-colors hover:bg-white/[0.07]">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Failed</p>
                        <p class="mt-1 text-xl font-bold text-rose-400"><span id="failed-count">{{ $failed }}</span></p>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-3 gap-3 text-center">
                    <div class="rounded-2xl bg-white/5 border border-white/5 px-3 py-4">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Queued</p>
                        <p id="queued-count" class="mt-1 text-lg font-bold text-slate-200">{{ max(0, $total - $processed) }}</p>
                    </div>
                    <div class="rounded-2xl bg-white/5 border border-white/5 px-3 py-4">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Metadata</p>
                        <p id="metadata-complete-count" class="mt-1 text-lg font-bold text-indigo-300">{{ $completed }}</p>
                    </div>
                    <div class="rounded-2xl bg-white/5 border border-white/5 px-3 py-4">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500">AI Source</p>
                        <p id="intelligence-source-count" class="mt-1 text-lg font-bold text-indigo-300">Live</p>
                    </div>
                </div>

                <div class="mt-6">
                    <div class="flex items-center justify-between text-[10px] font-bold uppercase tracking-widest text-slate-500 mb-2">
                        <span>Overall Progress</span>
                        <span id="progress-percentage">{{ $total > 0 ? (int) round(($processed / $total) * 100) : 0 }}%</span>
                    </div>
                    <div class="h-2 w-full overflow-hidden rounded-full bg-slate-800/50 p-0.5 border border-white/5">
                        @php
                            $percentage = $total > 0 ? (int) round(($processed / $total) * 100) : 0;
                        @endphp
                        <div
                            id="progress-bar"
                            class="h-full rounded-full accent-gradient transition-all duration-700 ease-out shadow-[0_0_15px_rgba(99,102,241,0.4)]"
                            style="width: {{ $percentage }}%;"
                        ></div>
                    </div>
                    <p id="progress-text" class="mt-3 text-[10px] font-medium text-slate-500 text-center uppercase tracking-wider">
                        Processed {{ $processed }} of {{ $total }} documents
                    </p>
                </div>
            </div>
        </section>

        <!-- Word Intelligence Section -->
        <section id="word-intelligence-card" class="rounded-[2.5rem] glass-darker p-8 sm:p-10 transition-all duration-700 opacity-0 transform translate-y-4">
            <div class="flex items-center justify-between mb-8">
                <div class="flex items-center gap-4">
                    <div class="h-12 w-12 rounded-2xl bg-indigo-500/20 flex items-center justify-center text-indigo-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-white tracking-tight">Word Intelligence Dashboard</h2>
                        <p class="text-xs text-slate-500 mt-1 uppercase tracking-widest font-bold">Discovering trending concepts across {{ $analysisBatch->documents->count() }} documents</p>
                    </div>
                </div>
                <div id="intelligence-status" class="flex items-center gap-3 text-xs font-bold text-indigo-400 uppercase tracking-widest animate-pulse">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-indigo-500"></span>
                    </span>
                    Analyzing DNA...
                </div>
            </div>

            <div id="top-words-container" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
                <!-- Top words will be injected here -->
                <div class="col-span-full py-12 text-center text-slate-600 italic text-sm">
                    Waiting for analysis engine to extract semantic patterns...
                </div>
            </div>

            <!-- Intelligence Progress Bar -->
            <div id="intelligence-progress-container" class="mt-8 pt-8 border-t border-white/5 hidden">
                <div class="flex items-center justify-between text-[10px] font-bold uppercase tracking-widest text-slate-500 mb-2">
                    <span>Synthesis Depth</span>
                    <span id="intelligence-progress-percentage">0%</span>
                </div>
                <div class="h-1.5 w-full overflow-hidden rounded-full bg-slate-800/50 p-0.5 border border-white/5">
                    <div
                        id="intelligence-progress-bar"
                        class="h-full rounded-full bg-indigo-500 transition-all duration-1000 ease-out shadow-[0_0_15px_rgba(99,102,241,0.3)]"
                        style="width: 0%;"
                    ></div>
                </div>
            </div>
        </section>

        <section class="rounded-[2.5rem] glass p-8 sm:p-10">
            <div class="grid gap-8 lg:grid-cols-[1fr_auto] lg:items-center">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-[0.3em] text-emerald-400">Proposal Fit Check</p>
                    <h2 class="mt-3 text-2xl font-bold tracking-tight text-white">Compare your research proposal with these papers</h2>
                    <p class="mt-3 max-w-2xl text-sm leading-relaxed text-slate-400">
                        Upload a proposal PDF or text file. The system compares your proposal's high-usage words and configured keywords against completed paper analyses, then shows a suitability popup with the closest matches.
                    </p>
                </div>

                <form id="proposal-comparison-form" class="flex w-full flex-col gap-3 rounded-3xl border border-white/5 bg-slate-950/30 p-4 sm:min-w-[360px]">
                    <label for="proposal-file" class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-500">Upload proposal</label>
                    <input
                        id="proposal-file"
                        name="proposal"
                        type="file"
                        accept="application/pdf,text/plain,.txt,.md"
                        class="block w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-xs text-slate-300 file:mr-4 file:rounded-full file:border-0 file:bg-indigo-500 file:px-4 file:py-2 file:text-xs file:font-bold file:text-white hover:border-indigo-500/40 focus:border-indigo-500/60 focus:outline-none"
                    />
                    <button
                        type="submit"
                        class="inline-flex items-center justify-center gap-2 rounded-full bg-emerald-500 px-5 py-3 text-xs font-black uppercase tracking-widest text-slate-950 shadow-xl shadow-emerald-500/20 transition-all hover:scale-[1.02] hover:bg-emerald-400 active:scale-95"
                    >
                        Compare Proposal
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    </button>
                    <div id="proposal-comparison-status" class="hidden rounded-2xl border border-white/5 bg-white/5 px-4 py-3 text-[11px] font-bold uppercase tracking-widest text-slate-400"></div>
                </form>
            </div>
        </section>

        <div class="overflow-hidden rounded-[2.5rem] glass-darker">
            <div class="overflow-x-auto">
                <table class="min-w-full border-collapse text-left text-sm text-slate-300">
                    <thead>
                        <tr class="bg-white/5 border-b border-white/5">
                            <th class="px-8 py-5 text-[10px] font-bold uppercase tracking-[0.2em] text-slate-500">
                                Document Metadata
                            </th>
                            @foreach ($analysisBatch->keywords as $keyword)
                                <th class="px-6 py-5 text-center text-[10px] font-bold uppercase tracking-[0.2em] text-slate-500 border-l border-white/5">
                                    {{ $keyword->keyword }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody id="documents-table-body" class="divide-y divide-white/5">
                        @php
                            $statusMap = [
                                'queued' => ['class' => 'bg-slate-500/10 text-slate-400 border-slate-500/20', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                                'processing' => ['class' => 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20', 'icon' => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15'],
                                'completed' => ['class' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20', 'icon' => 'M5 13l4 4L19 7'],
                                'failed' => ['class' => 'bg-rose-500/10 text-rose-400 border-rose-500/20', 'icon' => 'M6 18L18 6M6 6l12 12'],
                            ];
                        @endphp
                        @foreach ($analysisBatch->documents as $document)
                            <tr id="doc-row-{{ $document->id }}" class="hover:bg-white/3 transition-colors group">
                                <td class="px-8 py-6">
                                    <div class="flex flex-col gap-3">
                                        <div class="font-bold text-white group-hover:text-indigo-300 transition-colors">{{ $document->original_filename }}</div>
                                        <div class="flex flex-wrap items-center gap-2 text-[10px] font-bold uppercase tracking-widest text-slate-500">
                                            <span id="doc-pages-{{ $document->id }}" class="rounded-full border border-white/5 bg-white/5 px-2.5 py-1 text-slate-300">{{ $document->page_count ?? data_get($document->metadata, 'pdf.page_count', '—') }} pages</span>
                                            <span id="doc-size-{{ $document->id }}" class="rounded-full border border-white/5 bg-white/5 px-2.5 py-1 text-slate-300">
                                                {{ $formatBytes($document->file_size_bytes) }}
                                            </span>
                                            <span id="doc-word-count-{{ $document->id }}" class="rounded-full border border-white/5 bg-white/5 px-2.5 py-1 text-slate-300">
                                                {{ data_get($document->metadata, 'analysis.word_count', '—') }} words
                                            </span>
                                        </div>
                                        <details class="rounded-2xl border border-white/5 bg-white/5 p-4">
                                            <summary class="cursor-pointer list-none text-[10px] font-bold uppercase tracking-[0.25em] text-indigo-300">
                                                View metadata
                                            </summary>
                                            <div class="mt-4 grid gap-3 sm:grid-cols-2 text-[11px] text-slate-400">
                                                <div class="rounded-xl bg-slate-950/40 p-3">
                                                    <div class="text-[9px] uppercase tracking-[0.25em] text-slate-500">Title</div>
                                                    <div id="doc-title-{{ $document->id }}" class="mt-1 text-slate-200">{{ data_get($document->metadata, 'pdf.details.Title', 'Untitled PDF') }}</div>
                                                </div>
                                                <div class="rounded-xl bg-slate-950/40 p-3">
                                                    <div class="text-[9px] uppercase tracking-[0.25em] text-slate-500">Author</div>
                                                    <div id="doc-author-{{ $document->id }}" class="mt-1 text-slate-200">{{ data_get($document->metadata, 'pdf.details.Author', 'Unknown') }}</div>
                                                </div>
                                                <div class="rounded-xl bg-slate-950/40 p-3">
                                                    <div class="text-[9px] uppercase tracking-[0.25em] text-slate-500">Subject</div>
                                                    <div id="doc-subject-{{ $document->id }}" class="mt-1 text-slate-200">{{ $renderMetadataValue(data_get($document->metadata, 'pdf.details.Subject')) }}</div>
                                                </div>
                                                <div class="rounded-xl bg-slate-950/40 p-3">
                                                    <div class="text-[9px] uppercase tracking-[0.25em] text-slate-500">Keywords</div>
                                                    <div id="doc-keywords-{{ $document->id }}" class="mt-1 text-slate-200">{{ $renderMetadataValue(data_get($document->metadata, 'pdf.details.Keywords')) }}</div>
                                                </div>
                                                <div class="rounded-xl bg-slate-950/40 p-3">
                                                    <div class="text-[9px] uppercase tracking-[0.25em] text-slate-500">Producer</div>
                                                    <div class="mt-1 text-slate-200">{{ data_get($document->metadata, 'pdf.details.Producer', 'Unknown') }}</div>
                                                </div>
                                                <div class="rounded-xl bg-slate-950/40 p-3">
                                                    <div class="text-[9px] uppercase tracking-[0.25em] text-slate-500">Intelligence Source</div>
                                                    <div id="doc-intelligence-source-{{ $document->id }}" class="mt-1 text-slate-200">{{ data_get($document->metadata, 'analysis.intelligence_source', 'local') }}</div>
                                                </div>
                                                <div class="rounded-xl bg-slate-950/40 p-3">
                                                    <div class="text-[9px] uppercase tracking-[0.25em] text-slate-500">Unique Words</div>
                                                    <div class="mt-1 text-slate-200">{{ data_get($document->metadata, 'analysis.unique_word_count', '—') }}</div>
                                                </div>
                                            </div>
                                        </details>
                                        <div id="doc-status-container-{{ $document->id }}" class="flex items-center gap-2">
                                            @php $s = $statusMap[$document->status] ?? $statusMap['queued']; @endphp
                                            <span class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-[10px] font-bold uppercase tracking-widest {{ $s['class'] }}">
                                                <svg class="w-3 h-3 {{ $document->status === 'processing' ? 'animate-spin' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="{{ $s['icon'] }}"/></svg>
                                                {{ $document->status }}
                                            </span>
                                        </div>
                                        <div id="doc-error-{{ $document->id }}" class="{{ $document->error_message ? '' : 'hidden' }} mt-1 flex items-start gap-2 text-[11px] text-rose-400/80 leading-relaxed max-w-sm">
                                            <svg class="w-3.5 h-3.5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                            <span class="error-text">{{ $document->error_message }}</span>
                                        </div>
                                        <div id="doc-updated-{{ $document->id }}" class="text-[10px] uppercase tracking-[0.25em] text-slate-600">
                                            {{ $document->analyzed_at ? $document->analyzed_at->diffForHumans() : 'Awaiting analysis' }}
                                        </div>
                                        <button
                                            type="button"
                                            data-document-analysis-button
                                            data-document-name="{{ $document->original_filename }}"
                                            data-top-words-url="{{ route('analysis.documents.top-words', [$analysisBatch, $document]) }}"
                                            class="inline-flex w-fit items-center gap-2 rounded-full border border-indigo-500/20 bg-indigo-500/10 px-4 py-2 text-[10px] font-black uppercase tracking-widest text-indigo-300 transition-all hover:border-indigo-400/50 hover:bg-indigo-500/20 hover:text-white"
                                        >
                                            More analysis
                                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                        </button>
                                    </div>
                                </td>
                                @foreach ($analysisBatch->keywords as $keyword)
                                    @php
                                        $value = $results[$document->id][$keyword->id] ?? null;
                                    @endphp
                                    <td id="doc-{{ $document->id }}-kw-{{ $keyword->id }}" class="px-6 py-6 text-center border-l border-white/5">
                                        @if ($value !== null)
                                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl {{ $value > 0 ? 'bg-indigo-500/20 text-indigo-300 font-bold shadow-lg shadow-indigo-500/10' : 'bg-white/5 text-slate-500' }}">
                                                {{ $value }}
                                            </span>
                                        @else
                                            <span class="text-slate-700 animate-pulse">—</span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div id="document-analysis-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/85 p-4 backdrop-blur-md">
            <div class="max-h-[90vh] w-full max-w-4xl overflow-hidden rounded-[2.5rem] glass-darker shadow-2xl">
                <div class="flex flex-col gap-5 border-b border-white/5 p-6 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-[0.3em] text-indigo-400">Paper Deep Analysis</p>
                        <h3 id="document-analysis-title" class="mt-2 text-xl font-bold text-white">Top words</h3>
                    </div>
                    <button type="button" data-close-document-modal class="rounded-full border border-white/10 bg-white/5 p-3 text-slate-400 transition hover:text-white">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="max-h-[calc(90vh-110px)] overflow-y-auto p-6">
                    <div class="flex flex-col gap-4 rounded-3xl border border-white/5 bg-white/5 p-4 sm:flex-row sm:items-end">
                        <label class="flex-1">
                            <span class="text-[10px] font-bold uppercase tracking-[0.25em] text-slate-500">How many top words?</span>
                            <input id="document-word-limit" type="number" min="1" max="100" value="50" class="mt-2 w-full rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-sm font-bold text-white focus:border-indigo-500/60 focus:outline-none">
                        </label>
                        <button id="document-word-load-button" type="button" class="rounded-full bg-indigo-500 px-6 py-3 text-xs font-black uppercase tracking-widest text-white transition hover:bg-indigo-400">
                            Analyze Words
                        </button>
                    </div>
                    <div id="document-analysis-summary" class="mt-5 grid gap-3 sm:grid-cols-3"></div>
                    <div id="document-analysis-content" class="mt-6 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3"></div>
                </div>
            </div>
        </div>

        <div id="proposal-feedback-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/85 p-4 backdrop-blur-md">
            <div class="max-h-[90vh] w-full max-w-5xl overflow-hidden rounded-[2.5rem] glass-darker shadow-2xl">
                <div class="flex flex-col gap-5 border-b border-white/5 p-6 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-[0.3em] text-emerald-400">Proposal Feedback</p>
                        <h3 id="proposal-feedback-title" class="mt-2 text-xl font-bold text-white">Suitability result</h3>
                    </div>
                    <button type="button" data-close-proposal-modal class="rounded-full border border-white/10 bg-white/5 p-3 text-slate-400 transition hover:text-white">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="max-h-[calc(90vh-110px)] overflow-y-auto p-6">
                    <div id="proposal-feedback-summary" class="rounded-3xl border border-white/5 bg-white/5 p-6"></div>
                    <div class="mt-6 grid gap-6 lg:grid-cols-[0.9fr_1.1fr]">
                        <div>
                            <h4 class="text-sm font-bold text-white">Proposal top words</h4>
                            <div id="proposal-top-words" class="mt-4 grid gap-3"></div>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-white">Closest paper matches</h4>
                            <div id="proposal-document-matches" class="mt-4 grid gap-3"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const progressUrl = '{{ route('analysis.progress', $analysisBatch) }}';
        const progressText = document.getElementById('progress-text');
        const progressBar = document.getElementById('progress-bar');
        const progressPercentage = document.getElementById('progress-percentage');
        const completedCount = document.getElementById('completed-count');
        const failedCount = document.getElementById('failed-count');
        const queuedCount = document.getElementById('queued-count');
        const metadataCompleteCount = document.getElementById('metadata-complete-count');
        const intelligenceSourceCount = document.getElementById('intelligence-source-count');
        const proposalComparisonForm = document.getElementById('proposal-comparison-form');
        const proposalFileInput = document.getElementById('proposal-file');
        const proposalComparisonStatus = document.getElementById('proposal-comparison-status');
        const documentAnalysisModal = document.getElementById('document-analysis-modal');
        const documentAnalysisTitle = document.getElementById('document-analysis-title');
        const documentWordLimitInput = document.getElementById('document-word-limit');
        const documentWordLoadButton = document.getElementById('document-word-load-button');
        const documentAnalysisSummary = document.getElementById('document-analysis-summary');
        const documentAnalysisContent = document.getElementById('document-analysis-content');
        const proposalFeedbackModal = document.getElementById('proposal-feedback-modal');
        const proposalFeedbackTitle = document.getElementById('proposal-feedback-title');
        const proposalFeedbackSummary = document.getElementById('proposal-feedback-summary');
        const proposalTopWords = document.getElementById('proposal-top-words');
        const proposalDocumentMatches = document.getElementById('proposal-document-matches');
        let activeDocumentTopWordsUrl = null;

        const formatBytes = (bytes) => {
            if (!bytes || Number.isNaN(Number(bytes))) {
                return '—';
            }

            const size = Number(bytes);
            if (size < 1024) return `${size} B`;
            if (size < 1024 * 1024) return `${(size / 1024).toFixed(1)} KB`;
            return `${(size / (1024 * 1024)).toFixed(1)} MB`;
        };

        const statusMap = {
            'queued': { class: 'bg-slate-500/10 text-slate-400 border-slate-500/20', icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z' },
            'processing': { class: 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20', icon: 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15' },
            'completed': { class: 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20', icon: 'M5 13l4 4L19 7' },
            'failed': { class: 'bg-rose-500/10 text-rose-400 border-rose-500/20', icon: 'M6 18L18 6M6 6l12 12' }
        };

        const escapeHtml = (value) => String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');

        const clampLimit = (value) => Math.max(1, Math.min(100, Number.parseInt(value, 10) || 50));

        const showStatus = (node, message, type = 'info') => {
            if (!node) return;
            node.classList.remove('hidden', 'text-rose-300', 'text-emerald-300', 'text-slate-400');
            node.classList.add(type === 'error' ? 'text-rose-300' : (type === 'success' ? 'text-emerald-300' : 'text-slate-400'));
            node.textContent = message;
        };

        const renderWordList = (words, compact = false) => {
            const entries = Object.entries(words || {});

            if (entries.length === 0) {
                return '<div class="rounded-2xl border border-white/5 bg-white/5 p-5 text-center text-xs font-bold uppercase tracking-widest text-slate-500">No top words available yet</div>';
            }

            const max = Math.max(...entries.map(([, count]) => Number(count) || 0), 1);

            return entries.map(([word, count], index) => {
                const percentage = Math.max(4, Math.round(((Number(count) || 0) / max) * 100));

                return `
                    <div class="rounded-2xl border border-white/5 bg-white/5 p-4">
                        <div class="flex items-center justify-between gap-3">
                            <span class="min-w-0 truncate text-xs font-black uppercase tracking-wider text-white">${index + 1}. ${escapeHtml(word)}</span>
                            <span class="shrink-0 rounded-full bg-indigo-500/10 px-2.5 py-1 text-[10px] font-black text-indigo-300">${Number(count) || 0}</span>
                        </div>
                        <div class="${compact ? 'mt-2' : 'mt-3'} h-1.5 overflow-hidden rounded-full bg-slate-900/70">
                            <div class="h-full rounded-full bg-indigo-500" style="width: ${percentage}%"></div>
                        </div>
                    </div>
                `;
            }).join('');
        };

        const renderDocumentStatus = (doc) => {
            const statusContainer = document.getElementById(`doc-status-container-${doc.id}`);
            if (statusContainer) {
                const s = statusMap[doc.status] || statusMap.queued;
                statusContainer.innerHTML = `
                    <span class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-[10px] font-bold uppercase tracking-widest ${s.class} transition-all">
                        <svg class="w-3 h-3 ${doc.status === 'processing' ? 'animate-spin' : ''}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="${s.icon}"/></svg>
                        ${doc.status}
                    </span>
                `;
            }

            const errorContainer = document.getElementById(`doc-error-${doc.id}`);
            if (errorContainer) {
                if (doc.error) {
                    errorContainer.classList.remove('hidden');
                    errorContainer.querySelector('.error-text').textContent = doc.error;
                } else {
                    errorContainer.classList.add('hidden');
                }
            }

            const pagesNode = document.getElementById(`doc-pages-${doc.id}`);
            if (pagesNode) {
                const pageCount = doc.page_count ?? doc.metadata?.pdf?.page_count ?? '—';
                pagesNode.textContent = `${pageCount} pages`;
            }

            const sizeNode = document.getElementById(`doc-size-${doc.id}`);
            if (sizeNode) {
                sizeNode.textContent = formatBytes(doc.file_size_bytes ?? doc.metadata?.upload?.size_bytes);
            }

            const wordCountNode = document.getElementById(`doc-word-count-${doc.id}`);
            if (wordCountNode) {
                const wordCount = doc.metadata?.analysis?.word_count ?? '—';
                wordCountNode.textContent = `${wordCount} words`;
            }

            const titleNode = document.getElementById(`doc-title-${doc.id}`);
            if (titleNode) {
                titleNode.textContent = doc.metadata?.pdf?.details?.Title || 'Untitled PDF';
            }

            const authorNode = document.getElementById(`doc-author-${doc.id}`);
            if (authorNode) {
                authorNode.textContent = doc.metadata?.pdf?.details?.Author || 'Unknown';
            }

            const subjectNode = document.getElementById(`doc-subject-${doc.id}`);
            if (subjectNode) {
                subjectNode.textContent = doc.metadata?.pdf?.details?.Subject || '—';
            }

            const keywordsNode = document.getElementById(`doc-keywords-${doc.id}`);
            if (keywordsNode) {
                keywordsNode.textContent = doc.metadata?.pdf?.details?.Keywords || '—';
            }

            const intelligenceNode = document.getElementById(`doc-intelligence-source-${doc.id}`);
            if (intelligenceNode) {
                intelligenceNode.textContent = doc.metadata?.analysis?.intelligence_source || 'local';
            }

            const updatedNode = document.getElementById(`doc-updated-${doc.id}`);
            if (updatedNode) {
                updatedNode.textContent = doc.analyzed_at ? `Updated ${new Date(doc.analyzed_at).toLocaleString()}` : 'Awaiting analysis';
            }
        };

        const updateProgress = async () => {
            try {
                const response = await fetch(progressUrl, { headers: { 'Accept': 'application/json' } });
                if (!response.ok) return;

                const data = await response.json();
                const percentage = data.total > 0 ? Math.round((data.processed / data.total) * 100) : 0;

                if (progressText) progressText.textContent = `Processed ${data.processed} of ${data.total} documents`;
                if (progressBar) progressBar.style.width = `${percentage}%`;
                if (progressPercentage) progressPercentage.textContent = `${percentage}%`;
                if (completedCount) completedCount.textContent = data.completed;
                if (failedCount) failedCount.textContent = data.failed;
                if (queuedCount) queuedCount.textContent = data.pending ?? Math.max(0, data.total - data.processed);
                if (metadataCompleteCount) metadataCompleteCount.textContent = data.completed;
                if (intelligenceSourceCount) intelligenceSourceCount.textContent = data.top_words && Object.keys(data.top_words).length > 0 ? 'Live' : 'Pending';

                // Update individual documents
                if (data.documents) {
                    data.documents.forEach(doc => {
                        if (doc.results) {
                            Object.entries(doc.results).forEach(([kwId, count]) => {
                                const cell = document.getElementById(`doc-${doc.id}-kw-${kwId}`);
                                if (cell) {
                                    cell.innerHTML = `
                                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl ${count > 0 ? 'bg-indigo-500/20 text-indigo-300 font-bold shadow-lg shadow-indigo-500/10' : 'bg-white/5 text-slate-500'} transition-all animate-in zoom-in-50 duration-500">
                                            ${count}
                                        </span>
                                    `;
                                }
                            });
                        }

                        renderDocumentStatus(doc);
                    });
                }

                // Update Word Intelligence
                const intelligenceCard = document.getElementById('word-intelligence-card');
                const wordsContainer = document.getElementById('top-words-container');
                const intelligenceStatus = document.getElementById('intelligence-status');
                const intelProgressContainer = document.getElementById('intelligence-progress-container');
                const intelProgressBar = document.getElementById('intelligence-progress-bar');
                const intelProgressPercentage = document.getElementById('intelligence-progress-percentage');

                let displayWords = data.top_words;

                // Fallback to local aggregation if batch aggregate isn't ready
                if (!displayWords || Object.keys(displayWords).length === 0) {
                    const localAggregate = {};
                    let docCount = 0;
                    data.documents.forEach(doc => {
                        if (doc.top_words) {
                            docCount++;
                            Object.entries(doc.top_words).forEach(([word, count]) => {
                                localAggregate[word] = (localAggregate[word] ?? 0) + count;
                            });
                        }
                    });
                    
                    if (Object.keys(localAggregate).length > 0) {
                        const localLimit = clampLimit(data.word_limit || 50);
                        displayWords = Object.fromEntries(
                            Object.entries(localAggregate).sort((a,b) => b[1] - a[1]).slice(0, localLimit)
                        );
                        
                        // Show progress based on documents analyzed for intelligence
                        const intelPercentage = Math.round((docCount / data.total) * 100);
                        intelProgressContainer.classList.remove('hidden');
                        intelProgressBar.style.width = `${intelPercentage}%`;
                        intelProgressPercentage.textContent = `${intelPercentage}%`;
                    }
                } else {
                    // Batch aggregation is complete
                    intelProgressContainer.classList.add('hidden');
                }

                if (displayWords && Object.keys(displayWords).length > 0) {
                    intelligenceCard.classList.remove('opacity-0', 'translate-y-4');
                    
                    if (data.is_complete) {
                        intelligenceStatus.classList.remove('animate-pulse');
                        intelligenceStatus.innerHTML = '<svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg> Synthesis Complete';
                        intelProgressContainer.classList.add('hidden');
                    } else {
                        intelligenceStatus.textContent = 'Synthesizing Stream...';
                    }

                    wordsContainer.innerHTML = Object.entries(displayWords).map(([word, count]) => `
                        <div class="group relative overflow-hidden rounded-2xl bg-white/5 border border-white/5 p-4 transition-all hover:bg-white/[0.08] hover:border-indigo-500/30 hover:scale-[1.02] animate-in zoom-in-95 duration-500">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-black text-white uppercase tracking-wider group-hover:text-indigo-300 transition-colors">${escapeHtml(word)}</span>
                                <span class="text-[10px] font-bold text-indigo-400 bg-indigo-500/10 px-2 py-0.5 rounded-full">${count}</span>
                            </div>
                            <div class="mt-3 h-1 w-full rounded-full bg-slate-800/50 overflow-hidden">
                                <div class="h-full bg-indigo-500 transition-all duration-1000" style="width: ${Math.min(100, (count / Object.values(displayWords)[0]) * 100)}%"></div>
                            </div>
                        </div>
                    `).join('');
                }

                if (data.is_complete) {
                    clearInterval(progressTimer);
                    setTimeout(() => {
                        if (progressPercentage) progressPercentage.innerHTML = '<svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>';
                    }, 1000);
                }
            } catch (error) {
                console.error('Telemetry Error:', error);
            }
        };

        const openModal = (modal) => {
            if (!modal) return;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        };

        const closeModal = (modal) => {
            if (!modal) return;
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        };

        const loadDocumentTopWords = async () => {
            if (!activeDocumentTopWordsUrl || !documentAnalysisContent || !documentWordLoadButton) {
                return;
            }

            const limit = clampLimit(documentWordLimitInput?.value);
            const url = new URL(activeDocumentTopWordsUrl, window.location.origin);
            url.searchParams.set('limit', String(limit));

            documentWordLoadButton.disabled = true;
            documentWordLoadButton.classList.add('opacity-60', 'cursor-not-allowed');
            documentAnalysisContent.innerHTML = '<div class="col-span-full rounded-2xl border border-white/5 bg-white/5 p-8 text-center text-xs font-bold uppercase tracking-widest text-slate-500">Loading paper word usage...</div>';

            try {
                const response = await fetch(url.toString(), { headers: { 'Accept': 'application/json' } });
                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Unable to load paper analysis.');
                }

                if (documentAnalysisTitle) {
                    documentAnalysisTitle.textContent = `${data.document.filename} - top ${data.limit} words`;
                }

                if (documentAnalysisSummary) {
                    documentAnalysisSummary.innerHTML = `
                        <div class="rounded-2xl border border-white/5 bg-white/5 p-4">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Words analyzed</p>
                            <p class="mt-1 text-xl font-black text-white">${data.document.word_count ?? '—'}</p>
                        </div>
                        <div class="rounded-2xl border border-white/5 bg-white/5 p-4">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Unique words</p>
                            <p class="mt-1 text-xl font-black text-indigo-300">${data.document.unique_word_count ?? '—'}</p>
                        </div>
                        <div class="rounded-2xl border border-white/5 bg-white/5 p-4">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Available rankings</p>
                            <p class="mt-1 text-xl font-black text-emerald-300">${data.available}</p>
                        </div>
                    `;
                }

                documentAnalysisContent.innerHTML = renderWordList(data.top_words);
            } catch (error) {
                documentAnalysisContent.innerHTML = `<div class="col-span-full rounded-2xl border border-rose-500/20 bg-rose-500/10 p-6 text-sm text-rose-200">${escapeHtml(error.message)}</div>`;
            } finally {
                documentWordLoadButton.disabled = false;
                documentWordLoadButton.classList.remove('opacity-60', 'cursor-not-allowed');
            }
        };

        document.querySelectorAll('[data-document-analysis-button]').forEach((button) => {
            button.addEventListener('click', () => {
                activeDocumentTopWordsUrl = button.dataset.topWordsUrl;
                if (documentWordLimitInput) {
                    documentWordLimitInput.value = '50';
                }
                if (documentAnalysisTitle) {
                    documentAnalysisTitle.textContent = `${button.dataset.documentName || 'Paper'} - top words`;
                }
                if (documentAnalysisSummary) {
                    documentAnalysisSummary.innerHTML = '';
                }
                openModal(documentAnalysisModal);
                loadDocumentTopWords();
            });
        });

        documentWordLoadButton?.addEventListener('click', loadDocumentTopWords);
        document.querySelectorAll('[data-close-document-modal]').forEach((button) => button.addEventListener('click', () => closeModal(documentAnalysisModal)));
        documentAnalysisModal?.addEventListener('click', (event) => {
            if (event.target === documentAnalysisModal) {
                closeModal(documentAnalysisModal);
            }
        });

        const renderProposalFeedback = (data) => {
            const summary = data.summary || {};
            const isSuitable = Boolean(summary.suitable);
            const toneClasses = isSuitable
                ? { label: 'text-emerald-300', panel: 'border-emerald-500/20 bg-emerald-500/10', score: 'text-emerald-300' }
                : { label: 'text-rose-300', panel: 'border-rose-500/20 bg-rose-500/10', score: 'text-rose-300' };

            if (proposalFeedbackTitle) {
                proposalFeedbackTitle.textContent = summary.verdict || 'Proposal feedback';
            }

            if (proposalFeedbackSummary) {
                proposalFeedbackSummary.innerHTML = `
                    <div class="flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.3em] ${toneClasses.label}">${isSuitable ? 'Suitable' : 'Needs review'}</p>
                            <h4 class="mt-2 text-2xl font-black text-white">${escapeHtml(summary.verdict || 'Comparison complete')}</h4>
                            <p class="mt-3 max-w-2xl text-sm leading-relaxed text-slate-400">${escapeHtml(summary.message || 'Review the detailed matches below.')}</p>
                        </div>
                        <div class="flex h-28 w-28 shrink-0 items-center justify-center rounded-full border ${toneClasses.panel}">
                            <div class="text-center">
                                <div class="text-3xl font-black ${toneClasses.score}">${Number(summary.score || 0)}%</div>
                                <div class="text-[9px] font-bold uppercase tracking-widest text-slate-500">Best fit</div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-5 grid gap-3 sm:grid-cols-2">
                        <div class="rounded-2xl border border-white/5 bg-slate-950/40 p-4">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Top-paper confidence</p>
                            <p class="mt-1 text-lg font-black text-white">${Number(summary.confidence || 0)}%</p>
                        </div>
                        <div class="rounded-2xl border border-white/5 bg-slate-950/40 p-4">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Decision basis</p>
                            <p class="mt-1 text-xs font-medium text-slate-300">Top words + configured keyword overlap</p>
                        </div>
                    </div>
                `;
            }

            if (proposalTopWords) {
                proposalTopWords.innerHTML = renderWordList(data.proposal_top_words || {}, true);
            }

            if (proposalDocumentMatches) {
                const documents = data.documents || [];
                proposalDocumentMatches.innerHTML = documents.length ? documents.map((doc) => `
                    <div class="rounded-3xl border border-white/5 bg-white/5 p-5">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <h5 class="truncate text-sm font-black text-white">${escapeHtml(doc.filename)}</h5>
                                <p class="mt-1 text-[10px] font-bold uppercase tracking-widest text-slate-500">${escapeHtml(doc.verdict)} · ${doc.page_count ?? '—'} pages · ${doc.word_count ?? '—'} words</p>
                            </div>
                            <span class="rounded-full bg-indigo-500/10 px-3 py-1 text-xs font-black text-indigo-300">${Number(doc.score || 0)}%</span>
                        </div>
                        <div class="mt-4">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Shared words</p>
                            <div class="mt-2 flex flex-wrap gap-2">
                                ${(doc.shared_words || []).slice(0, 8).map((item) => `<span class="rounded-full border border-white/5 bg-slate-950/40 px-2.5 py-1 text-[10px] font-bold text-slate-300">${escapeHtml(item.word)} (${Number(item.proposal_count || 0)}/${Number(item.paper_count || 0)})</span>`).join('') || '<span class="text-xs text-slate-500">No strong shared top words.</span>'}
                            </div>
                        </div>
                        <div class="mt-4">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Keyword matches</p>
                            <p class="mt-2 text-xs text-slate-300">${(doc.keyword_matches || []).map(escapeHtml).join(', ') || 'No configured keyword overlap.'}</p>
                        </div>
                    </div>
                `).join('') : '<div class="rounded-2xl border border-white/5 bg-white/5 p-6 text-center text-xs font-bold uppercase tracking-widest text-slate-500">No completed papers are ready for comparison.</div>';
            }

            openModal(proposalFeedbackModal);
        };

        proposalComparisonForm?.addEventListener('submit', async (event) => {
            event.preventDefault();

            const file = proposalFileInput?.files?.[0];
            if (!file) {
                showStatus(proposalComparisonStatus, 'Please choose a proposal PDF or text file first.', 'error');
                return;
            }

            const button = proposalComparisonForm.querySelector('button[type="submit"]');
            const formData = new FormData();
            formData.append('proposal', file);

            button.disabled = true;
            button.classList.add('opacity-60', 'cursor-not-allowed');
            showStatus(proposalComparisonStatus, 'Reading proposal and comparing research alignment...');

            try {
                const response = await fetch('{{ route('analysis.compare-proposal', $analysisBatch) }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: formData,
                });
                const data = await response.json();

                if (!response.ok) {
                    const message = data.message || Object.values(data.errors || {})?.flat()?.[0] || 'Proposal comparison failed.';
                    throw new Error(message);
                }

                showStatus(proposalComparisonStatus, 'Comparison complete. Review the popup feedback.', 'success');
                renderProposalFeedback(data);
            } catch (error) {
                showStatus(proposalComparisonStatus, error.message, 'error');
            } finally {
                button.disabled = false;
                button.classList.remove('opacity-60', 'cursor-not-allowed');
            }
        });

        document.querySelectorAll('[data-close-proposal-modal]').forEach((button) => button.addEventListener('click', () => closeModal(proposalFeedbackModal)));
        proposalFeedbackModal?.addEventListener('click', (event) => {
            if (event.target === proposalFeedbackModal) {
                closeModal(proposalFeedbackModal);
            }
        });

        const progressTimer = setInterval(updateProgress, 2000);
        updateProgress();
    </script>
@endpush
