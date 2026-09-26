<?php

use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\ServiceRequestWebController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Web\NotificationController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/requests',          [ServiceRequestWebController::class, 'index'])->name('requests.index');
    Route::middleware('role:STUDENT_STAFF|ADMIN')->group(function () {
        Route::get('/requests/create', [ServiceRequestWebController::class, 'create'])->name('requests.create');
        Route::post('/requests', [ServiceRequestWebController::class, 'store'])->name('requests.store');
    });
    Route::get('/requests/{id}',     [ServiceRequestWebController::class, 'show'])->name('requests.show');

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
