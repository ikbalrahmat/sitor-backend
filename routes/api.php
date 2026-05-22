<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DiklatPersonelController;
use App\Http\Controllers\MatriksRisikoController; // <-- Tambahkan import ini
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\AuditTopicController;
// Route Terbuka
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [\App\Http\Controllers\PasswordResetController::class, 'sendResetLinkEmail']);
Route::post('/reset-password', [\App\Http\Controllers\PasswordResetController::class, 'reset']);
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
    Route::get('/download-file', [DiklatPersonelController::class, 'downloadFile']);
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

    // Rute Topik Penugasan Audit
    Route::get('/audit-topics', [AuditTopicController::class, 'index']);
    Route::post('/audit-topics', [AuditTopicController::class, 'store']);
    Route::put('/audit-topics/{id}', [AuditTopicController::class, 'update']);
    Route::delete('/audit-topics/{id}', [AuditTopicController::class, 'destroy']);
    Route::post('/audit-topics/{topicId}/evaluations/{userId}', [AuditTopicController::class, 'updateEvaluation']);

    // Rute Penugasan Audit (Kompatibilitas dengan Frontend)
    Route::get('/penugasan-kompetensi', [AuditTopicController::class, 'getTopics']);
    Route::post('/penugasan-kompetensi', [AuditTopicController::class, 'syncTopics']);
});