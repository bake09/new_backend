<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchDiscType extends Model
{
    // Verbindung definieren, falls eine andere als die Standard-Datenbank verwendet wird
    protected $connection = 'odbc_intern';
    
    // Angabe des Tabellennamens, falls er nicht dem Laravel-Naming entspricht
    protected $table = 'PURCH_DISC_TYPES';

    // Falls keine Timestamps in der Tabelle vorhanden sind
    public $timestamps = false;

    protected $casts = [
        'Erstellungsdatum' => 'date:Y-m-d',
        'TRANSACT_DATE' => 'date:Y-m-d',
        'Erstellungsdatum' => 'date:Y-m-d',
        'DATE' => 'date:Y-m-d',
        
        // 'DISCOUNT_CD' => $this->DISCOUNT_CD,
        // 'DISCOUNT_NUMBER' => $this->DISCOUNT_NUMBER,
        // 'FACTORY_MODEL_CODE' => $this->FACTORY_MODEL_CODE,
        // 'OPTIONS_1' => $this->OPTIONS_1,
        // 'OPTIONS_2' => $this->OPTIONS_2,
        // 'OPTIONS_3' => $this->OPTIONS_3,
        // 'OPTIONS_4' => $this->OPTIONS_4,
        // 'OPTIONS_5' => $this->OPTIONS_5,
        // 'STATE_CODE' => $this->STATE_CODE,
        // 'TRANSACT_DATE' => $this->TRANSACT_DATE,
        // 'HANDLER' => $this->HANDLER,
        // 'DISCOUNT_TEXT' => $this->DISCOUNT_TEXT,
        // 'DISCOUNT_PERCENT' => $this->DISCOUNT_PERCENT,
        // 'DISCOUNT_AMOUNT_X' => $this->DISCOUNT_AMOUNT_X,
        // 'PERCENT_CALC_BASIS' => $this->PERCENT_CALC_BASIS,
        // 'CONV_FLAG' => $this->CONV_FLAG,
        // 'timestamp' => $this->timestamp,
        // 'UNIQUE_IDENT' => $this->UNIQUE_IDENT
    ];
}
