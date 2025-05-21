<?php

use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

use App\Http\Controllers\TeamController;
use App\Http\Controllers\TodoController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\Auth\AuthTokenController;
use App\Http\Controllers\NotificationManagerController;

use App\Models\Customer;

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

    // Notifications
    Route::post('/notifications/subscribe', [NotificationManagerController::class, 'subscribe']);
    Route::post('/notifications/unsubscribe', [NotificationManagerController::class, 'unsubscribe']);
});

Route::prefix('auth/token')->group(function () {
    // Auth
    Route::post('/login', [AuthTokenController::class, 'login']);
    Route::post('/register', [AuthTokenController::class, 'register']);
});

Route::get('test', function() {
    return "test";
});

Route::get('findappointment', function(){
    // SQL-Abfrage mit Parameter-Bindung
    $register_number = 'LB-FE 616';
    $appointments = DB::connection('odbc_intern')->select("
        SELECT *
        FROM WPS_APPOINTMENTS
        WHERE REGISTER_NUMBER = ?
    ", [$register_number]);
    // Rückgabe der Ergebnisse als JSON mit angepassten Optionen
    return response()->json($appointments, 200, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_IGNORE);
});

Route::post('/check-email', function(Request $request) {
    
    $request->validate([
        'email' => 'required|email'
    ]);

    $email = trim($request->email);

    $customers = Customer::select([
            'CUSTOMER_NUMBER',
            'TITLE',
            'FIRST_NAME',
            'LAST_NAME',
            'ADDR_2',
            'MAIL_ADDR',
            'BIRTHDAY',
            'E_MAIL_ADDRESS',
            'MOBILE_PHONE'
        ])
        ->whereRaw('E_MAIL_ADDRESS = ?', [$email])
        ->get();

    $trimmedCustomers = $customers->map(function($customer) {
        $customerArray = $customer->toArray();
        array_walk_recursive($customerArray, function(&$value) {
            if (is_string($value)) {
                $value = trim($value);
            }
        });
        return $customerArray;
    });

    return $trimmedCustomers->isNotEmpty() 
        ? response()->json($trimmedCustomers, 200, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_IGNORE)
        : response()->json(['error' => 'Not found'], 404);
});