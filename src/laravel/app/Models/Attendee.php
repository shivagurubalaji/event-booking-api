<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class Attendee extends Model
{

    protected static function booted()
    {
        static::creating(function ($attendee) {
            $attendee->id = Str::uuid()->toString();
        });
    }

    protected $fillable = [
        'event_id',
        'name',
        'email',
        'phone',
        'notes'
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
