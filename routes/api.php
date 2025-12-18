<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\SubjectController;
use App\Http\Controllers\Api\ClassGroupController;
use App\Http\Controllers\Api\ScheduleController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\TelegramWebhookController;
use App\Models\User;

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'message' => 'Presensi SMPIT API is running',
        'timestamp' => now(),
    ]);
});

// Telegram Webhook
Route::post('/telegram/webhook', [TelegramWebhookController::class, 'handle']);

// Authentication and Protected API routes (with session middleware for stateful auth)
Route::middleware('web')->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']); // Check current user

    // Class sync routes
    Route::get('/classes/sync-status', [ClassGroupController::class, 'getSyncStatus']);
    Route::post('/classes/{id}/sync-students', [ClassGroupController::class, 'syncStudents']);

    // Resources
    Route::post('/students/connect-telegram', [StudentController::class, 'connectTelegram']);
    Route::get('/students/check-telegram-status', [StudentController::class, 'checkTelegramStatus']);
    Route::apiResource('students', StudentController::class);
    Route::apiResource('subjects', SubjectController::class);
    Route::apiResource('classes', ClassGroupController::class);
    Route::apiResource('schedules', ScheduleController::class);
    Route::apiResource('attendances', AttendanceController::class);

    // User routes
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    Route::get('/available-homeroom-teachers', [UserController::class, 'getAvailableHomeroomTeachers']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
});
