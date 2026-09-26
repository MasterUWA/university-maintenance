<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AuditLogController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ServiceRequestController;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;

Route::middleware([
    EncryptCookies::class,
    AddQueuedCookiesToResponse::class,
    StartSession::class,
])->group(function () {
// Public
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login',    [AuthController::class, 'login']);

// Protected
Route::middleware('auth')->group(function () {
    Route::get('/auth/me',     [AuthController::class, 'me']);
    Route::post('/auth/logout',[AuthController::class, 'logout']);
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'read']);

    Route::get('/requests',           [ServiceRequestController::class, 'index']);
    Route::get('/requests/{serviceRequest}', [ServiceRequestController::class, 'show']);
    Route::match(['put', 'patch'], '/requests/{serviceRequest}', [ServiceRequestController::class, 'update']);
    Route::delete('/requests/{serviceRequest}', [ServiceRequestController::class, 'destroy']);

    Route::middleware('role:STUDENT_STAFF|ADMIN')->group(function () {
        Route::post('/requests', [ServiceRequestController::class, 'store']);
    });

    Route::middleware('role:ADMIN')->group(function () {
        Route::post('/requests/{serviceRequest}/assign', [ServiceRequestController::class, 'assign']);
        Route::get('/audit-logs', [AuditLogController::class, 'index']);
    });

    Route::middleware('role:OFFICER|ADMIN')->group(function () {
        Route::patch('/requests/{serviceRequest}/status', [ServiceRequestController::class, 'updateStatus']);
    });
});
});
