<?php

use App\Http\Controllers\api\SuperAdminController;
use App\Http\Controllers\api\ConferenceController;
use App\Http\Controllers\api\PageController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
//protected routes
Route::middleware('auth:sanctum')->group(function (){
    Route::get('/me', [AuthController::class,'me']);
    Route::post('/logout', [AuthController::class,'logout']);
});

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