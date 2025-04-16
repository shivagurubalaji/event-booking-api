<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Event;

class BookingController extends Controller
{
    public function store(Request $request, Event $event)
    {
        if ($event->attendees()->where('email', $request->email)->exists()) {
            return response()->json(['message' => 'Already booked.'], 409);
        }

        if ($event->attendees()->count() >= $event->capacity) {
            return response()->json(['message' => 'Event is fully booked.'], 403);
        }

        $attendee = $event->attendees()->create([
            'user_id' => $request->user()->id,
            'name' => $request->name,
            'email' => $request->email,
        ]);

        return response()->json($attendee, 201);
    }
}
