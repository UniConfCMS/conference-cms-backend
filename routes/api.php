<?php

use App\Http\Controllers\api\SuperAdminController;
use App\Http\Controllers\api\ConferenceController;
use App\Http\Controllers\api\EditorController;
use App\Http\Controllers\api\PageController;
use App\Http\Controllers\api\UserController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PasswordResetController;

Route::post('/login', [AuthController::class, 'login']);
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

Route::get('/set-password', [AuthController::class, 'showSetPasswordForm'])->name('set-password');
Route::post('/set-password', [AuthController::class, 'setPassword']);

Route::middleware('auth:sanctum')->prefix('super-admin')->group(function () {
    Route::get('/users', [SuperAdminController::class, 'index']);
    Route::post('/users', [SuperAdminController::class, 'store']);
    Route::get('/users/{id}', [SuperAdminController::class, 'show']);
    Route::put('/users/{id}', [SuperAdminController::class, 'update']);
    Route::delete('/users/{id}', [SuperAdminController::class, 'destroy']);
    Route::patch('/users/{id}/assign-role', [SuperAdminController::class, 'assignRole']);
});



//Routes for user managment
Route::middleware('auth:sanctum')->prefix('admin')->group(function () {

    // --- Users ---
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
    Route::patch('/users/{id}/assign-role', [UserController::class, 'assignRole']);


    Route::post('/editors/assign', [EditorController::class, 'assignEditor']);
    Route::delete('/editors/{id}', [EditorController::class, 'deleteEditor']);
    Route::get('/conferences/{conference_id}/editors', [EditorController::class, 'getEditorsByConference']);


     // --- Conferences ---
     Route::get('/conferences', [ConferenceController::class, 'getAllConferences']);
     Route::post('/conferences', [ConferenceController::class, 'createConference']);
     Route::put('/conferences/{id}', [ConferenceController::class, 'updateConference']);
     Route::delete('/conferences/{id}', [ConferenceController::class, 'deleteConference']);


     // --- Pages ---
     Route::get('/conferences/{conference_id}/pages', [PageController::class, 'getPagesByConference']);
     Route::post('/conferences/{conferenceId}/pages', [PageController::class, 'createPage']);
     Route::delete('/conferences/{conference_id}/pages/{id}', [PageController::class, 'deletePage']);
     Route::put('/conferences/{conference_id}/pages/{id}', [PageController::class, 'updatePageContent']);
});
