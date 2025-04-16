<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // return ['Laravel' => app()->version()];

    
    // SQL-Abfrage mit Parameter-Bindung
    // $register_number = 'LB-FE 616';
    // $appointments = DB::connection('odbc_intern')->select("
    //     SELECT *
    //     FROM WPS_APPOINTMENTS
    //     WHERE REGISTER_NUMBER = ?
    // ", [$register_number]);
    // // Rückgabe der Ergebnisse als JSON mit angepassten Optionen
    // return response()->json($appointments, 200, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_IGNORE);
});

require __DIR__.'/auth.php';
