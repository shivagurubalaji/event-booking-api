<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAttendeeRequest;
use App\Models\Event;
use App\Models\Attendee;

use App\Traits\StandardAPIResponse;

class AttendeeController extends Controller
{

    use StandardAPIResponse;

    public function store(StoreAttendeeRequest $request, Event $event)
    {

        if ($event->attendees()->where('email', $request->email)->exists()) {
            //return response()->json(['message' => 'Already registered.'], 409);
            return $this->errorResponse('Already registered.', ['email' => ['This attendee is already registered.']], 409);
        }

        if ($event->attendees()->count() >= $event->capacity) {
            return response()->json(['message' => 'Event is full.'], 403);
        }

        //dd($request->validated());
        //dd($event->id, gettype($event->id));

        $attendee = $event->attendees()->create($request->validated());
        return response()->json($attendee, 201);
    }

    public function index(Event $event)
    {
        return response()->json($event->attendees);
    }

    public function show(Event $event, Attendee $attendee)
    {
        return response()->json($attendee);
    }

    public function update(Request $request, Event $event, Attendee $attendee)
    {
        $attendee->update($request->all());
        return response()->json($attendee);
    }

    public function destroy(Event $event, Attendee $attendee)
    {
        $attendee->delete();
        return response()->json(null, 204);
    }
}
