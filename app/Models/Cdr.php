<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cdr extends Model
{
    protected $table = 'cdrs';
    public $timestamps = false;

    protected $casts = [
        'incoming' => 'boolean',
        'answered' => 'boolean',
        'hasvoicemail' => 'boolean',
        'hasmonitor' => 'boolean',
        'hasfax' => 'boolean',
        'deleted' => 'boolean',
        'privatecall' => 'boolean',
        'callbacknumberextern' => 'boolean',
        'summarystep' => 'boolean',
        'starttime' => 'datetime',
        'ringingtime' => 'datetime',
        'linktime' => 'datetime',
        'callresulttime' => 'datetime',
    ];
}