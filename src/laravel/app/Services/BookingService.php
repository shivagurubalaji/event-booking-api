<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Attendee;
use Illuminate\Validation\ValidationException;

class BookingService
{
    public function bookEvent(Event $event, array $data): Attendee
    {
        if ($event->attendees()->where('email', $data['email'])->exists()) {
            throw new \Exception('You have already booked to this event.', 403);
        }

        if ($event->attendees()->count() >= $event->capacity) {
            throw new \Exception('This event has reached its capacity.', 403);
        }

        return $event->attendees()->create([
            'name' => $data['name'],
            'email' => $data['email'],
        ]);
    }
}
