<?php

use App\Http\Controllers\ComparisonPageController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportDownloadController;
use App\Http\Controllers\ExportPageController;
use App\Http\Controllers\IndicatorPageController;
use App\Http\Controllers\NewsPageController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['jwt.web'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');
    Route::get('porownanie', [ComparisonPageController::class, 'index'])->name('comparison.index');
    Route::get('wskazniki', [IndicatorPageController::class, 'index'])->name('indicators.index');
    Route::get('aktualnosci', [NewsPageController::class, 'index'])->name('news.index');
    Route::get('eksport', ExportPageController::class)->name('export.index');
    Route::get('eksport/pobierz', ExportDownloadController::class)->name('export.download');
});

require __DIR__.'/settings.php';
