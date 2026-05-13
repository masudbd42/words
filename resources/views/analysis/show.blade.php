@extends('layouts.app')

@section('content')
    <div class="mx-auto flex w-full max-w-6xl flex-col gap-8">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wide text-slate-400">Analysis Results</p>
                <h1 class="mt-2 text-3xl font-semibold text-white sm:text-4xl">
                    Keyword frequency dashboard
                </h1>
                <p class="mt-2 text-sm text-slate-300">
                    Batch #{{ $analysisBatch->id }} · {{ $analysisBatch->documents->count() }} documents
                </p>
            </div>
            <a
                href="{{ route('analysis.index') }}"
                class="inline-flex items-center justify-center rounded-full border border-slate-700 px-5 py-2 text-sm font-semibold text-slate-200 transition hover:border-indigo-500 hover:text-white"
            >
                New analysis
            </a>
        </div>

        <div class="rounded-2xl border border-slate-800 bg-slate-900/70 p-6 shadow-lg shadow-slate-900/40">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="text-sm font-semibold text-slate-200">Processing status</p>
                    <p id="progress-text" class="mt-1 text-xs text-slate-400">
                        Processed {{ $processed }} of {{ $total }} documents.
                    </p>
                </div>
                <div class="text-xs text-slate-400">
                    Completed: <span id="completed-count" class="font-semibold text-emerald-300">{{ $completed }}</span> ·
                    Failed: <span id="failed-count" class="font-semibold text-rose-300">{{ $failed }}</span>
                </div>
            </div>
            <div class="mt-4 h-2 w-full overflow-hidden rounded-full bg-slate-800">
                @php
                    $percentage = $total > 0 ? (int) round(($processed / $total) * 100) : 0;
                @endphp
                <div
                    id="progress-bar"
                    class="h-full rounded-full bg-indigo-500 transition-all"
                    style="width: {{ $percentage }}%;"
                ></div>
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900/70 shadow-lg shadow-slate-900/40">
            <div class="overflow-x-auto">
                <table class="min-w-full border-collapse text-left text-sm text-slate-200">
                    <thead class="bg-slate-900">
                        <tr>
                            <th class="border-b border-slate-800 px-4 py-3 text-xs font-semibold uppercase tracking-wider text-slate-400">
                                Document
                            </th>
                            @foreach ($analysisBatch->keywords as $keyword)
                                <th class="border-b border-slate-800 px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-slate-400">
                                    {{ $keyword->keyword }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        @php
                            $statusClasses = [
                                'queued' => 'bg-slate-800 text-slate-300',
                                'processing' => 'bg-indigo-500/20 text-indigo-200',
                                'completed' => 'bg-emerald-500/20 text-emerald-200',
                                'failed' => 'bg-rose-500/20 text-rose-200',
                            ];
                        @endphp
                        @foreach ($analysisBatch->documents as $document)
                            <tr class="hover:bg-slate-900/60">
                                <td class="px-4 py-4">
                                    <div class="font-medium text-white">{{ $document->original_filename }}</div>
                                    <div class="mt-2 inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $statusClasses[$document->status] ?? 'bg-slate-800 text-slate-300' }}">
                                        {{ ucfirst($document->status) }}
                                    </div>
                                    @if ($document->error_message)
                                        <p class="mt-2 text-xs text-rose-300">{{ $document->error_message }}</p>
                                    @endif
                                </td>
                                @foreach ($analysisBatch->keywords as $keyword)
                                    @php
                                        $value = $results[$document->id][$keyword->id] ?? null;
                                    @endphp
                                    <td class="px-4 py-4 text-center text-sm">
                                        {{ $value !== null ? $value : '—' }}
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const progressUrl = '{{ route('analysis.progress', $analysisBatch) }}';
        const progressText = document.getElementById('progress-text');
        const progressBar = document.getElementById('progress-bar');
        const completedCount = document.getElementById('completed-count');
        const failedCount = document.getElementById('failed-count');

        const updateProgress = async () => {
            try {
                const response = await fetch(progressUrl, { headers: { 'Accept': 'application/json' } });
                if (!response.ok) {
                    return;
                }

                const data = await response.json();
                const percentage = data.total > 0 ? Math.round((data.processed / data.total) * 100) : 0;

                if (progressText) {
                    progressText.textContent = `Processed ${data.processed} of ${data.total} documents.`;
                }
                if (progressBar) {
                    progressBar.style.width = `${percentage}%`;
                }
                if (completedCount) {
                    completedCount.textContent = data.completed;
                }
                if (failedCount) {
                    failedCount.textContent = data.failed;
                }

                if (data.is_complete) {
                    clearInterval(progressTimer);
                }
            } catch (error) {
                console.error(error);
            }
        };

        const progressTimer = setInterval(updateProgress, 3000);
        updateProgress();
    </script>
@endpush
