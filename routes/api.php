<?php

use App\Http\Controllers\api\SuperAdminController;
use App\Http\Controllers\api\AdminController;
use App\Http\Controllers\api\UserController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PasswordResetController;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
//protected routes
Route::middleware('auth:sanctum')->group(function (){
    Route::get('/me', [AuthController::class,'me']);
    Route::post('/logout', [AuthController::class,'logout']);
});

//Reset password routes
Route::post('/password/reset/send', [PasswordResetController::class, 'sendResetLink']);
Route::post('/password/reset', [PasswordResetController::class, 'resetPassword']);
//Link from the mail
Route::get('/password/reset/{token}', [PasswordResetController::class, 'resetPassword'])->name('password.reset');

Route::middleware('auth:sanctum')->prefix('super-admin')->group(function () {
    Route::get('/users', [SuperAdminController::class, 'index']);
    Route::post('/users', [SuperAdminController::class, 'store']);
    Route::get('/users/{id}', [SuperAdminController::class, 'show']);
    Route::put('/users/{id}', [SuperAdminController::class, 'update']);
    Route::delete('/users/{id}', [SuperAdminController::class, 'destroy']);
    Route::patch('/users/{id}/assign-role', [SuperAdminController::class, 'assignRole']);
});

Route::middleware('auth:sanctum')->prefix('admin')->group(function () {

    // --- Conferences ---
    Route::get('/conferences', [AdminController::class, 'getAllConferences']);
    Route::post('/conferences', [AdminController::class, 'createConference']);
    Route::put('/conferences/{id}', [AdminController::class, 'updateConference']);
    Route::delete('/conferences/{id}', [AdminController::class, 'deleteConference']);

    // --- Editors ---
    Route::post('/editors/assign', [AdminController::class, 'assignEditor']);
    Route::delete('/editors/{id}', [AdminController::class, 'deleteEditor']);

    // --- Pages ---
    Route::get('/conferences/{conference_id}/pages', [AdminController::class, 'getPagesByConference']);
    Route::post('/conferences/{conferenceId}/pages', [AdminController::class, 'createPage']);
    Route::delete('/conferences/{conference_id}/pages/{id}', [AdminController::class, 'deletePage']);
    Route::put('/conferences/{conference_id}/pages/{id}', [AdminController::class, 'updatePageContent']);
});

//Routes for user managment
Route::middleware('auth:sanctum')->prefix('admin')->group(function () {
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
    Route::patch('/users/{id}/assign-role', [UserController::class, 'assignRole']);
});
