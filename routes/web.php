<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\ImportController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/analysis', [DashboardController::class, 'analysis'])->name('analysis');
Route::get('/insights', [DashboardController::class, 'insights'])->name('insights');

Route::prefix('export')->name('export.')->group(function () {
    Route::get('/excel', [ExportController::class, 'exportExcel'])->name('excel');
    Route::get('/product-excel', [ExportController::class, 'exportProductExcel'])->name('product-excel');
    Route::get('/category-excel', [ExportController::class, 'exportCategoryExcel'])->name('category-excel');
    Route::get('/pdf', [ExportController::class, 'exportPDF'])->name('pdf');
});

Route::prefix('import')->name('import.')->group(function () {
    Route::get('/', [ImportController::class, 'index'])->name('index');
    Route::post('/preview', [ImportController::class, 'preview'])->name('preview');
    Route::post('/confirm', [ImportController::class, 'confirm'])->name('confirm');
});
