<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\IncidentReportsController;
use App\Http\Controllers\ResponseController;
use App\Http\Controllers\SmsReportController;

// Auth Pages
Route::get('/', [AdminController::class, 'login'])->name('login');

Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

// Unified chat + scoped status
Route::post('/store-response', [ResponseController::class, 'storeResponse']);
Route::post('/update-report-status', [ResponseController::class, 'updateReportStatus']);

Route::get('/fetch-fire-reports', [IncidentReportsController::class, 'fetchFireReports'])->name('fetch.fireReports');

// Admin routes
Route::prefix('app')->middleware('firebase.auth')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/fire-fighters', [AdminController::class, 'fireFighters'])->name('fire-fighters');
    Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
    Route::get('/sms-reports', [SmsReportController::class, 'index'])->name('sms-reports');
    Route::post('/sms-reports/update-status/{id}', [SmsReportController::class, 'updateSmsReportStatus'])->name('sms-reports.update-status');
    Route::get('/incident-reports', [IncidentReportsController::class, 'index'])->name('incident-reports');
});
