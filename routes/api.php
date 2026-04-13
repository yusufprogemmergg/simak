<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProfilePerusahaanController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\KavlingController;
use App\Http\Controllers\Api\SalesTransactionController;
use App\Http\Controllers\Api\SalesController;
use App\Http\Controllers\Api\BuyerController;
use App\Http\Controllers\Api\FleksiblePaymentController;
use App\Http\Controllers\Api\AngsuranController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\AdminLicenseController;

// ─────────────────────────────────────────────────────
// AUTH (public)
// ─────────────────────────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);
    Route::post('/logout',   [AuthController::class, 'logout'])->middleware('auth:sanctum');
});

// ─────────────────────────────────────────────────────
// SUPER ADMIN — manajemen license key
// ─────────────────────────────────────────────────────
Route::prefix('admin')->middleware(['auth:sanctum', 'super_admin'])->group(function () {
    Route::get('/license',        [AdminLicenseController::class, 'index']);
    Route::post('/license',       [AdminLicenseController::class, 'store']);
    Route::get('/license/{id}',   [AdminLicenseController::class, 'show']);
    Route::put('/license/{id}',   [AdminLicenseController::class, 'update']);
    Route::delete('/license/{id}',[AdminLicenseController::class, 'destroy']);
});

// ─────────────────────────────────────────────────────
// CURRENT USER INFO
// ─────────────────────────────────────────────────────
Route::middleware(['auth:sanctum', 'license'])->group(function () {
    Route::get('/me', function () {
        return auth()->user();
    });
});

// ─────────────────────────────────────────────────────
// PROFILE PERUSAHAAN (owner only)
// ─────────────────────────────────────────────────────
Route::middleware(['auth:sanctum', 'owner'])->group(function () {
    Route::get('/dashboard',                           [DashboardController::class, 'index']);
    Route::get('/profile-perusahaan',                  [ProfilePerusahaanController::class, 'index']);
    Route::post('/profile-perusahaan',                 [ProfilePerusahaanController::class, 'store']);
    Route::post('/profile-perusahaan/{id}',            [ProfilePerusahaanController::class, 'update']);
    Route::delete('/profile-perusahaan/{id}',          [ProfilePerusahaanController::class, 'destroy']);
    Route::delete('/profile-perusahaan/logo',          [ProfilePerusahaanController::class, 'deleteLogo']);
});

// ─────────────────────────────────────────────────────
// MASTER DATA (owner — harus punya license aktif)
// ─────────────────────────────────────────────────────
Route::prefix('master')->middleware(['auth:sanctum', 'license'])->group(function () {

    // Project
    Route::get('/project',                  [ProjectController::class, 'index']);
    Route::get('/project/export-excel',     [ProjectController::class, 'exportExcel']);
    Route::post('/project',                 [ProjectController::class, 'store']);
    Route::post('/project/{id}',            [ProjectController::class, 'update']);
    Route::delete('/project/{id}',          [ProjectController::class, 'destroy']);

    // Kavling
    Route::get('/kavling',                  [KavlingController::class, 'index']);
    Route::get('/kavling/export-excel',     [KavlingController::class, 'exportExcel']);
    Route::post('/kavling',                 [KavlingController::class, 'store']);
    Route::post('/kavling/{id}',            [KavlingController::class, 'update']);
    Route::delete('/kavling/{id}',          [KavlingController::class, 'destroy']);

    // Buyer
    Route::get('/buyer',           [BuyerController::class, 'index']);
    Route::get('/buyer/{id}',      [BuyerController::class, 'show']);
    Route::post('/buyer',          [BuyerController::class, 'store']);
    Route::put('/buyer/{id}',      [BuyerController::class, 'update']);
    Route::delete('/buyer/{id}',   [BuyerController::class, 'destroy']);

    // Sales (staff)
    Route::get('/sales',           [SalesController::class, 'index']);
    Route::get('/sales/{id}',      [SalesController::class, 'show']);
    Route::post('/sales',          [SalesController::class, 'store']);
    Route::put('/sales/{id}',      [SalesController::class, 'update']);
    Route::delete('/sales/{id}',   [SalesController::class, 'destroy']);

    // Sales Transaction
    Route::get('/sales-transaction',                          [SalesTransactionController::class, 'index']);
    Route::get('/sales-transaction/{id}',                     [SalesTransactionController::class, 'show']);
    Route::post('/sales-transaction',                         [SalesTransactionController::class, 'store']);
    Route::post('/sales-transaction/{id}',                    [SalesTransactionController::class, 'update']);
    Route::delete('/sales-transaction/{id}',                  [SalesTransactionController::class, 'destroy']);
    Route::post('/sales-transaction/{id}/pay-dp',             [SalesTransactionController::class, 'payDp']);
    Route::post('/sales-transaction/{id}/pay-off',            [SalesTransactionController::class, 'payOff']);
    Route::post('/sales-transaction/{id}/cancel',             [SalesTransactionController::class, 'cancelSale']);

    // Flexible Payment
    Route::post('/flexible-payment', [FleksiblePaymentController::class, 'store']);

    // Angsuran Direct Pay
    Route::post('/angsuran/{id}/pay', [AngsuranController::class, 'payLunas']);

    // Print Kuitansi
    Route::get('/payment-history/{id}/print-kuitansi', [\App\Http\Controllers\Api\PaymentHistoryController::class, 'printKuitansi']);
});