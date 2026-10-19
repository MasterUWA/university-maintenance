<?php

use App\Http\Controllers\Web\AnalyticsController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\ServiceRequestWebController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Web\NotificationController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');
Route::view('/about', 'about')->name('about');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/requests',          [ServiceRequestWebController::class, 'index'])->name('requests.index');
    Route::middleware('role:STUDENT_STAFF|ADMIN')->group(function () {
        Route::get('/requests/create', [ServiceRequestWebController::class, 'create'])->name('requests.create');
        Route::post('/requests', [ServiceRequestWebController::class, 'store'])->name('requests.store');
    });
    Route::get('/requests/{id}',     [ServiceRequestWebController::class, 'show'])->name('requests.show');
    Route::post('/requests/{id}/comments', [ServiceRequestWebController::class, 'comment'])->name('requests.comments.store');
    Route::post('/requests/{id}/rating', [ServiceRequestWebController::class, 'rate'])->name('requests.rate');

    Route::middleware('role:OFFICER|ADMIN')->group(function () {
        Route::patch('/requests/{id}/status', [ServiceRequestWebController::class, 'updateStatus'])->name('requests.status');
    });

    Route::middleware('role:ADMIN')->group(function () {
        Route::get('/admin/analytics', [AnalyticsController::class, 'index'])->name('admin.analytics');
        Route::post('/admin/requests/{id}/assign', [ServiceRequestWebController::class, 'assign'])->name('admin.requests.assign');
        Route::post('/admin/requests/{id}/auto-assign', [ServiceRequestWebController::class, 'autoAssign'])->name('admin.requests.auto-assign');
    });

    Route::middleware('role:ADMIN')->group(function () {
        Route::get('/admin/requests', [ServiceRequestWebController::class, 'adminIndex'])->name('admin.requests');
        Route::get('/admin/requests/export', [ServiceRequestWebController::class, 'export'])->name('admin.requests.export');
        Route::patch('/admin/requests/{id}', [ServiceRequestWebController::class, 'update'])->name('admin.requests.update');
        Route::delete('/admin/requests/{id}', [ServiceRequestWebController::class, 'destroy'])->name('admin.requests.destroy');
    });

    Route::middleware('role:OFFICER')->group(function () {
        Route::get('/officer/assignments', [ServiceRequestWebController::class, 'officerIndex'])->name('officer.assignments');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'read'])->name('notifications.read');
});

require __DIR__.'/auth.php';
