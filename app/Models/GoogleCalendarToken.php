<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoogleCalendarToken extends Model
{
    protected $fillable = [
        'access_token',
        'refresh_token',
        'expires_at',
        'sync_token',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }

    public static function current(): ?self
    {
        return static::first();
    }
}
