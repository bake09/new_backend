<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasVersion4Uuids as HasUuids; 
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Todo extends Model
{
    /** @use HasFactory<\Database\Factories\TodoFactory> */
    use HasFactory, HasUuids;

    protected $guarded = [];

    protected $casts = [
        'done' => 'boolean',
        // 'due_date' => 'datetime',
        // 'due_date' => 'date',
        // 'due_date' => 'timestamp',
    ];
    
    public function user(){
        return $this->belongsTo(User::class);
    }
}
