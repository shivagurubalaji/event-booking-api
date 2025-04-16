<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Event;

use App\Traits\StandardAPIResponse;
class BookingController extends Controller
{

    use StandardAPIResponse;

    // Event bookings directly by attendees
    public function store(Request $request, Event $event)
    {
        if ($event->attendees()->where('email', $request->email)->exists()) {
            return $this->errorResponse(
                'Already booked.',
                ['Already booked.' => ['You have already booked to this event.']],
                403
            );
        }

        if ($event->attendees()->count() >= $event->capacity) {
            return $this->errorResponse(
                'Event is full.',
                ['capacity' => ['This event has reached its capacity.']],
                403
            );
        }

        $attendee = $event->attendees()->create([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        return $this->successResponse($attendee, 'Attendee registere successfully.', 201);
    }
}
