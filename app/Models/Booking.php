<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Event;

class Booking extends Model
{
    protected $fillable = [
        'user_id',
        'event_id',
        'tickets_booked',
        'total_price'
    ];

    
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    
    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}