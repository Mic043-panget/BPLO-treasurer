<?php

use App\Http\Controllers\Treasurer\DashboardController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/treasurer/dashboard');

Route::view('/treasurer/integration', 'treasurer.integration')->name('treasurer.integration');

Route::prefix('treasurer')->name('treasurer.')->group(function (): void {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/payments', [DashboardController::class, 'payments'])->name('payments');
    Route::get('/history', [DashboardController::class, 'history'])->name('history');
});
