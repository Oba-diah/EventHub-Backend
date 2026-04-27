<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserOtp extends Model

{
    protected $fillable = [
        'otp',
        'expires_at',
        'user_id'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function isExpired()
    {
        return $this->expires_at->isPast();
    }
}