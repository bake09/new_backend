<?php

namespace App\Models;

use App\Models\PurchDiscount;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    // Verbindung definieren, falls eine andere als die Standard-Datenbank verwendet wird
    protected $connection = 'odbc_intern';

    // Angabe des Tabellennamens, falls er nicht dem Laravel-Naming entspricht
    protected $table = 'VEHICLE';

    // Falls keine Timestamps in der Tabelle vorhanden sind
    public $timestamps = false;

    // Falls ein anderer Primärschlüssel als "id" verwendet wird
    // protected $primaryKey = 'BASIS_NUMBER';

    // Cast für unterschiedliche Dates – immer als Y-m-d
    protected $casts = [
        'TRANSACT_DATE' => 'date:Y-m-d',
        'ARRIVAL_DATE' => 'date:Y-m-d',
        'ASU_DATE' => 'date:Y-m-d',
        'DECLARATION_DATE' => 'date:Y-m-d',
        'EXPECTED_ARR_DATE' => 'date:Y-m-d',
        'FIRST_OCCUR_DATE' => 'date:Y-m-d',
        'FIRST_REG_DATE' => 'date:Y-m-d',
        'LAST_INVOICE_DTE' => 'date:Y-m-d',
        'LAST_OWNER_CHANGE' => 'date:Y-m-d',
        'LATEST_INV_DATE' => 'date:Y-m-d',
        'ORIG_INV_DATE' => 'date:Y-m-d',
        'TUV_DATE' => 'date:Y-m-d',
        'FIRST_REG_DATE' => 'date:Y-m-d',
    ];

    public function purchDiscounts()
    {
        return $this->hasMany(PurchDiscount::class, 'CHASSIS_NO_MODIF', 'CHASSIS_NUMBER');
    }

    // Accessor für has_purch_discounts
    public function getHasPurchDiscountsAttribute(): bool
    {
        return $this->relationLoaded('purchDiscounts') 
            ? $this->purchDiscounts->isNotEmpty() 
            : false;
    }
}
