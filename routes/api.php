<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\Auth\ProfileController;
use App\Http\Controllers\Api\RoomController;
use App\Http\Controllers\Api\FixedScheduleController;
use App\Http\Controllers\Api\User\UserController;
use App\Http\Controllers\Api\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\ReservationController;
use App\Http\Controllers\Api\DashboardController;

// ===============================
// 🔓 AUTH ROUTES (Public)
// ===============================
Route::post('/login', [LoginController::class, 'login'])->name('auth.login');
Route::post('/register', [RegisterController::class, 'register'])->name('auth.register');

// ===============================
// 🔐 PROTECTED ROUTES (auth:sanctum)
// ===============================
Route::middleware('auth:sanctum')->group(function () {

    // ===============================
    // 👤 PROFILE & LOGOUT
    // ===============================
    Route::get('/profile', [ProfileController::class, 'profile'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'updateProfile'])->name('profile.update');
    Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');

    // ===============================
    // 🧑‍💼 ADMIN & KARYAWAN (Shared)
    // ===============================
    Route::middleware('role:admin|karyawan')->group(function () {
        Route::apiResource('rooms', RoomController::class);
        Route::apiResource('fixed-schedules', FixedScheduleController::class);
    });

    // ===============================
    // 🛠️ ADMIN ONLY ROUTES
    // ===============================
    Route::middleware('role:admin')->prefix('admin')->group(function () {

        // 👥 CRUD USER (via Admin Panel)
        Route::apiResource('users', AdminUserController::class);

        // 📊 Dashboard Statistik
        Route::get('/dashboard', [DashboardController::class, 'stats'])->name('admin.dashboard.stats');

        // 📅 Statistik Reservasi Per Bulan
        Route::get('/statistik-reservasi', [ReservationController::class, 'monthlyStatistics']);

        // 📝 Manajemen Reservasi (satu endpoint untuk semua status)
        Route::put('reservations/{id}/status/{action}', [ReservationController::class, 'updateStatus']);

        // ❌ Delete Reservasi
        Route::delete('reservations/{id}', [ReservationController::class, 'destroy']);

        // 📦 Export Data Reservasi
        Route::get('/reservations/export', [ReservationController::class, 'export'])
            ->name('reservations.export');
    });

    // ===============================
    // 👩‍💼 KARYAWAN ONLY ROUTES
    // ===============================
    Route::middleware('role:karyawan')->prefix('karyawan')->group(function () {
        Route::post('reservations', [ReservationController::class, 'store']);
        Route::put('reservations/{id}/cancel', [ReservationController::class, 'cancel']);
    });

    // ===============================
    // 📖 GENERAL RESERVATION (Read Only)
    // ===============================
    Route::get('/reservations', [ReservationController::class, 'index']);
    Route::get('/reservations/{id}', [ReservationController::class, 'show']);
});
