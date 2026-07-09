<?php

use App\Http\Controllers\Admin\SubmissionExportController;
use App\Http\Controllers\Admin\SubmissionStrukController;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Public\Antrian;
use App\Livewire\Public\SubmissionForm;
use Illuminate\Support\Facades\Route;

Route::get('/', SubmissionForm::class)->name('home');
Route::get('/antrian', Antrian::class)->name('antrian');

Route::middleware(['auth'])->group(function () {
    Route::view('profile', 'profile')->name('profile');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::redirect('/', '/admin/dashboard');
        Route::get('dashboard', Dashboard::class)->name('dashboard');
        Route::get('export', SubmissionExportController::class)->name('export');
        Route::get('submissions/{submission}/struk', SubmissionStrukController::class)->name('struk');
    });
});

require __DIR__.'/auth.php';
