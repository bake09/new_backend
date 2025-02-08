<?php

use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Broadcast;

// Broadcast::routes(["prefix" => "api", "middleware" => ["auth:sanctum"]]);

Broadcast::routes(['middleware' => ['auth:sanctum']]);

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('users.1', function ($user) {
    return [
        'id' => $user->id,
    ];
});

Broadcast::channel('roles.{roleId}', function (User $user, $roleId) {
    // Überprüfe, ob der Benutzer die Rolle hat
    $role = Role::find($roleId);
    return $user->hasRole($role->name); // Nur Benutzer mit dieser Rolle können den Kanal abonnieren
});

// Broadcast::channel('roles.{roleId}', function (User $user, $roleId) {
//     $role = Role::find($roleId);
//     Log::info("User {$user->id} is trying to join the roles.{$roleId} channel. Role: $role");

//     if (!$role) {
//         Log::error("Role {$roleId} not found.");
//         return false;
//     }

//     return $user->hasRole($role->name);
// });