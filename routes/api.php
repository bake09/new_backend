<?php

use App\Models\Todo;

use App\Models\User;
use App\Models\Vehicle;

use App\Models\Customer;
use Illuminate\Http\Request;
use App\Notifications\TodoCreated;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Contracts\Role;
use App\Http\Controllers\CdrController;

use App\Http\Controllers\TeamController;
use App\Http\Controllers\TodoController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\StarfaceRestController;
use App\Http\Controllers\Auth\AuthTokenController;
use App\Http\Controllers\NotificationManagerController;


function utf8_fit($v) {
    if (is_string($v) && !mb_check_encoding($v, 'UTF-8')) {
        return mb_convert_encoding($v, 'UTF-8', 'Windows-1252');
    }
    if (is_array($v)) {
        return array_map('utf8_fit', $v);
    }
    return $v;
}

function trim_data(array $data): array {
    return array_map(fn ($v) => is_string($v) ? trim($v) : $v, $data);
}

Route::middleware(['auth:sanctum'])->group(function () {
    // Auth Token!
    Route::post('auth/token/logout', [AuthTokenController::class, 'logout']);

    // Resources
    Route::apiResource('/images', ImageController::class);
    Route::apiResource('user', UserController::class);
    Route::patch('user/{user}/assignRole', [UserController::class, 'assignRole']);

    // Todo
    Route::apiResource('todo', TodoController::class);
    Route::patch('todo/toggledone/{todo}', [TodoController::class, 'toggledone']);

    // Roles and Permissions
    Route::apiResource('roles', PermissionController::class);
    Route::apiResource('team', TeamController::class);
    Route::post('roles/{role}/permissions', [PermissionController::class, 'assignPermissions']);
    Route::delete('roles/{role}/permissions/{permission}', [PermissionController::class, 'removePermission']);

    // NEW
    Route::post('addPermission', [PermissionController::class, 'addPermission']);
    Route::patch('roles/{role}/updateRole', [PermissionController::class, 'updateRole']);
    Route::post('roles/{role}/syncPermissions', [PermissionController::class, 'syncPermissionsToRole']);

    // Notifications
    Route::post('/notifications/subscribe', [NotificationManagerController::class, 'subscribe']);
    Route::post('/notifications/unsubscribe', [NotificationManagerController::class, 'unsubscribe']);

    // Vehicle
    Route::apiResource('vehicle', VehicleController::class);
    Route::get('purchdiscounts', [VehicleController::class, 'purchdiscounts']);
    Route::get('purchdisctype', [VehicleController::class, 'purchdisctypes']);
    Route::get('doublepurchdisctype', [VehicleController::class, 'doublepurchdisctype']);

    // CDR
    Route::get('cdr', [CdrController::class, 'index']);
    Route::get('sf-rest', [CdrController::class, 'login']);
    Route::get('sf-users', [CdrController::class, 'getSFUsers']);

    Route::post('cdr-reports', [CdrController::class, 'createCDRreports']);
});

Route::prefix('auth/token')->group(function () {
    // Auth
    Route::post('/login', [AuthTokenController::class, 'login']);
    Route::post('/register', [AuthTokenController::class, 'register']);
    
    Route::post('/forgot-password', [AuthTokenController::class, 'sendResetLink']);
    Route::post('/reset-password', [AuthTokenController::class, 'resetPassword']);
});

Route::get('test', function() {
    return 'test Sring';
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

        $data = $customers->toArray();

        return response()->json(utf8_fit($data), 200, [], JSON_UNESCAPED_UNICODE);
});

// Export TEST
Route::get('/export-users', [UserController::class, 'export']);