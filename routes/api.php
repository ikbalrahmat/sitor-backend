<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DiklatPersonelController;
use App\Http\Controllers\MatriksRisikoController; // <-- Tambahkan import ini
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AuditController;
// Route Terbuka
Route::post('/login', [AuthController::class, 'login']);
// Route Tertutup (Harus bawa Token)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
    Route::put('/users/{id}/unlock', [UserController::class, 'unlock']);
    
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    // <-- ROUTE DIKLAT -->
    Route::apiResource('diklat', DiklatPersonelController::class);
    // <-- ROUTE MATRIKS RISIKO -->
    Route::get('/matriks-risiko', [MatriksRisikoController::class, 'index']);
    Route::put('/matriks-risiko/{userId}', [MatriksRisikoController::class, 'update']);
    Route::get('/logs', [ActivityLogController::class, 'index']);
    Route::get('/audits', [AuditController::class, 'index']);

    // Rute Modul Penugasan Audit
    Route::get('/audits', [AuditController::class, 'index']);
    Route::post('/audits', [AuditController::class, 'store']);
    Route::get('/auditors-competencies', [AuditController::class, 'getAuditors']);
    Route::post('/audits/{audit}/teams', [AuditController::class, 'storeTeam']);
    Route::delete('/audits/{audit}/teams/{team}', [AuditController::class, 'destroyTeam']);
});