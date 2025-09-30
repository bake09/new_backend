<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchDiscount extends Model
{
    // Verbindung definieren, falls eine andere als die Standard-Datenbank verwendet wird
    protected $connection = 'odbc_intern';
    
    // Angabe des Tabellennamens, falls er nicht dem Laravel-Naming entspricht
    protected $table = 'PURCH_DISCOUNTS';

    // Falls keine Timestamps in der Tabelle vorhanden sind
    public $timestamps = false;

    protected $casts = [
        'TRANSACT_DATE' => 'date:Y-m-d',
        'DATE' => 'date:Y-m-d',
    ];
}
