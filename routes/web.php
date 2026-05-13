<?php

use App\Http\Controllers\AnalysisController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AnalysisController::class, 'index'])->name('analysis.index');
Route::post('/analyses', [AnalysisController::class, 'store'])->name('analysis.store');
Route::get('/analyses/{analysisBatch}', [AnalysisController::class, 'show'])->name('analysis.show');
Route::get('/analyses/{analysisBatch}/progress', [AnalysisController::class, 'progress'])->name('analysis.progress');
