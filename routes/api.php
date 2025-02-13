<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\TeamController;
use App\Http\Controllers\TodoController;

use App\Http\Controllers\UserController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\Auth\AuthTokenController;


Route::middleware(['auth:sanctum'])->group(function () {

    // Auth Token!
    Route::post('auth/token/logout', [AuthTokenController::class, 'logout']);

    // Resources
    Route::apiResource('/images', ImageController::class);
    Route::apiResource('user', UserController::class);

    // Todo
    Route::apiResource('todo', TodoController::class);
    Route::patch('todo/toggledone/{todo}', [TodoController::class, 'toggledone']);

    // Roles and Permissions
    Route::apiResource('roles', PermissionController::class);
    Route::apiResource('team', TeamController::class);
    Route::post('roles/{role}/permissions', [PermissionController::class, 'assignPermissions']);
    Route::delete('roles/{role}/permissions/{permission}', [PermissionController::class, 'removePermission']);
});

Route::prefix('auth/token')->group(function () {
    // Auth
    Route::post('/login', [AuthTokenController::class, 'login']);
    Route::post('/register', [AuthTokenController::class, 'register']);
});