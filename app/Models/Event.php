<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
      protected $fillable = [
        'id',
        'title',
        'location',
        'date',
        'time',
        'price',
        'available_ticket',
        'description',
    ];
}
