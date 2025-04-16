<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    // Angabe des Tabellennamens, falls er nicht dem Laravel-Naming entspricht
    protected $table = 'CUSTOMER';

    // Verbindung definieren, falls eine andere als die Standard-Datenbank verwendet wird
    protected $connection = 'odbc_intern';

    // Falls keine Timestamps in der Tabelle vorhanden sind
    public $timestamps = false;

    // Falls ein anderer Primärschlüssel als "id" verwendet wird
    protected $primaryKey = 'CUSTOMER_NUMBER';

    // Cast für BIRTHDAY definieren – immer als Y-m-d
    protected $casts = [
        'BIRTHDAY' => 'date:Y-m-d',
    ];

    // Optional: Füge hier die Spalten hinzu, die per Mass Assignment befüllbar sind
    // protected $fillable = [
    //     'CUSTOMER_NUMBER', 'TITLE', 'FIRST_NAME', 'LAST_NAME',
    //     'ADDR_2', 'MAIL_ADDR', 'BIRTHDAY', 'E_MAIL_ADDRESS', 'MOBILE_PHONE'
    // ];
}
