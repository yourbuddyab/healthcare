<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $fillable = [
        'user_id',
        'professional_id',
        'start_time',
        'end_time',
        'status',
    ];

    public $timestamps = ['start_time', 'end_time'];

    public function professional()
    {
        return $this->belongsTo(Professional::class);
    }
}
