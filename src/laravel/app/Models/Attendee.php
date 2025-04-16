<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Attendee extends Model
{
    use HasFactory, HasUuids;

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
