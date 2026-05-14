<?php

use App\Http\Controllers\AnalysisController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AnalysisController::class, 'index'])->name('analysis.index');
Route::post('/analyses/init', [AnalysisController::class, 'initBatch'])->name('analysis.init');
Route::post('/analyses/{analysisBatch}/upload', [AnalysisController::class, 'uploadDocument'])->name('analysis.upload');
Route::get('/analyses/{analysisBatch}', [AnalysisController::class, 'show'])->name('analysis.show');
Route::get('/analyses/{analysisBatch}/progress', [AnalysisController::class, 'progress'])->name('analysis.progress');
