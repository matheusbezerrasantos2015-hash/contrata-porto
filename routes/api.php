<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\JobController;
use App\Http\Controllers\Api\ApplicationController;
use App\Http\Controllers\Api\FavoriteController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Auth (sem middleware)
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:auth_login');
Route::post('/auth/recover', [AuthController::class, 'forgotPassword'])->middleware('throttle:auth_recover');
Route::post('/auth/reset', [AuthController::class, 'resetPassword']);
Route::post('/auth/verify-email', [AuthController::class, 'verifyEmail']);
Route::post('/auth/resend-verification', [AuthController::class, 'resendVerification'])->middleware('throttle:auth_recover');

// Empresas (público)
Route::get('/empresas/{id}', [CompanyController::class, 'show'])->whereNumber('id');

// Vagas públicas
Route::get('/jobs', [JobController::class, 'index']);
Route::get('/jobs/filter', [JobController::class, 'filter']);
Route::get('/jobs/{id}', [JobController::class, 'show'])->whereNumber('id');

// Rotas protegidas (auth:sanctum)
Route::middleware('auth:sanctum')->group(function () {
    // Auth (necessita token para revogar)
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Perfil geral / User comum
    Route::get('/me', [UserController::class, 'me']);

    // Perfil Candidato (role:CANDIDATO)
    Route::middleware('role:CANDIDATO')->group(function () {
        Route::put('/profile', [UserController::class, 'update']);
        Route::delete('/profile', [UserController::class, 'deleteAccount']);

        // Candidaturas (Candidato)
        Route::post('/applications', [ApplicationController::class, 'apply']);
        Route::get('/applications/me', [ApplicationController::class, 'myApplications']);

        // Favoritos (Candidato)
        Route::post('/favorites', [FavoriteController::class, 'store']);
        Route::get('/favorites', [FavoriteController::class, 'index']);
        Route::delete('/favorites/{id}', [FavoriteController::class, 'destroy'])->whereNumber('id');
    });

    // Perfil Empresa (role:EMPRESA)
    Route::middleware('role:EMPRESA')->group(function () {
        // Empresa Profile
        Route::get('/empresa/profile', [CompanyController::class, 'getProfile']);
        Route::put('/empresa/profile', [CompanyController::class, 'updateProfile']);
        Route::delete('/empresa/profile', [CompanyController::class, 'deleteAccount']);
        Route::post('/empresas', [CompanyController::class, 'create']);

        // Vagas (Empresa)
        Route::get('/jobs/my-company', [JobController::class, 'myCompany']);
        Route::post('/jobs', [JobController::class, 'store']);
        Route::put('/jobs/{id}', [JobController::class, 'update'])->whereNumber('id');
        Route::delete('/jobs/{id}', [JobController::class, 'destroy'])->whereNumber('id');
        Route::put('/jobs/{id}/conclude', [JobController::class, 'conclude'])->whereNumber('id');
        Route::put('/jobs/{id}/status', [JobController::class, 'toggleStatus'])->whereNumber('id');

        // Candidaturas recebidas (Empresa)
        Route::get('/jobs/{id}/applications', [ApplicationController::class, 'jobApplications'])->whereNumber('id');
        Route::get('/applications/{id}', [ApplicationController::class, 'show'])->whereNumber('id');
        Route::put('/applications/{id}', [ApplicationController::class, 'updateStatus'])->whereNumber('id');
        Route::get('/applications/{id}/curriculo', [ApplicationController::class, 'downloadCurriculo'])->whereNumber('id');
    });
});
