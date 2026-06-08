<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\SubmissionController;
use App\Http\Controllers\Api\GreideScanController;
use App\Http\Controllers\AnnotateController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DonationRedirectController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\MomentController;
use App\Http\Controllers\ObservationManageController;
use Illuminate\Support\Facades\Route;

Route::get('/gezondheid', HealthController::class)->name('health');
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::redirect('/ondernemers', '/');
Route::post('/api/scan', [GreideScanController::class, 'store'])->middleware('throttle:12,1')->name('api.scan');
Route::post('/api/scan/inzenden', [GreideScanController::class, 'submit'])->middleware('throttle:6,1')->name('api.scan.submit');
Route::get('/momenten', [MomentController::class, 'index'])->name('moments.index');
Route::get('/media/observations/{observation}', [MediaController::class, 'observation'])->name('media.observation');
Route::get('/momenten/{observation:slug}', [MomentController::class, 'show'])->name('moments.show');
Route::get('/steun', [DonationRedirectController::class, 'general'])->name('donate');
Route::get('/momenten/{observation:slug}/steun', [DonationRedirectController::class, 'moment'])->name('donate.moment');

Route::redirect('/upload', '/#scan')->name('upload.create');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::middleware('role:annotator,admin')->group(function () {
        Route::get('/annoteren', [AnnotateController::class, 'index'])->name('annotate.index');
        Route::get('/annoteren/{observation}/foto', [AnnotateController::class, 'photo'])->name('annotate.photo');
        Route::get('/annoteren/{observation}', [AnnotateController::class, 'edit'])->name('annotate.edit');
        Route::post('/annoteren/{observation}', [AnnotateController::class, 'store'])->name('annotate.store');
        Route::post('/annoteren/{observation}/ai', [AnnotateController::class, 'rescan'])->name('annotate.ai');
        Route::delete('/annoteren/{observation}', [ObservationManageController::class, 'destroy'])->name('annotate.destroy');
        Route::delete('/momenten/{observation:slug}', [ObservationManageController::class, 'destroy'])->name('moments.destroy');
    });

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/inzendingen', [SubmissionController::class, 'index'])->name('submissions.index');
        Route::get('/inzendingen/{observation}', [SubmissionController::class, 'edit'])->name('submissions.edit');
        Route::put('/inzendingen/{observation}', [SubmissionController::class, 'update'])->name('submissions.update');
        Route::post('/momenten/{observation}/unpublish', [AdminDashboardController::class, 'unpublish'])->name('unpublish');
    });
});
