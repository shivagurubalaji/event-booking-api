<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
        try {
            // Check for duplicate attendee
            if ($event->attendees()->where('email', $request->email)->exists()) {
                return $this->errorResponse(
                    'Already registered.',
                    ['email' => ['This attendee is already registered.']],
                    409
                );
            }
    
            // Check for overbooking
            if ($event->attendees()->count() >= $event->capacity) {
                return $this->errorResponse(
                    'Event is full.',
                    ['capacity' => ['This event has reached its capacity.']],
                    403
                );
            }
    
            $attendee = $event->attendees()->create($request->validated());

            return $this->successResponse($attendee, 'Attendee registered successfully.', 201);
            
    
        } catch (\Throwable $e) {
            // Log for debugging
            Log::error('Attendee registration failed', [
                'error' => $e->getMessage(),
                'event_id' => $event->id ?? null,
                'request' => $request->all(),
            ]);
    
            return $this->errorResponse(
                'Something went wrong while registering the attendee.',
                ['error' => [$e->getMessage()]],
                500
            );
        }
    }
    

    public function index($event)
    {
        //return response()->json($event->attendees);
        $getEvent = Event::with('attendees')->find($event);
        if (!$getEvent) {
            return $this->errorResponse(
                'No event found.',
                ['Event' => ['No matching events found.']],
                403
            );
        }
        return $this->successResponse($getEvent->attendees, 'Attendees list for the event loaded.', 201);

    }

    public function show($attendee)
    {
        //return response()->json($attendee);
        $getAttendee = Attendee::find($attendee);

        if (!$getAttendee) {
            return $this->errorResponse(
                'No attendee found.',
                ['Attendee' => ['No matching attendee found.']],
                403
            );
        }
    
        return $this->successResponse($getAttendee, 'Attendee listed successfully.');
    }

    public function update(StoreAttendeeRequest $request, $attendee)
    {
        //$attendee->update($request->all());
        //return response()->json($attendee);
        $getAttendee = Attendee::find($attendee);
        if (!$getAttendee) {
            return $this->errorResponse(
                'No attendee found.',
                ['Attendee' => ['No matching attendee found.']],
                403
            );
        }

        $validatedData = $request->validated();

        $getAttendee->update($validatedData);
    
        return $this->successResponse($attendee, 'Attendee updated successfully.');
    }

    public function destroy($attendee)
    {
        //$attendee->delete();
        //return response()->json(null, 204);
        $getAttendee = Attendee::find($attendee);
        if (!$getAttendee) {
            return $this->errorResponse(
                'No attendee found.',
                ['Attendee' => ['No matching attendee found.']],
                403
            );
        }
        $getAttendee->delete();
        return $this->successResponse("Attendee", 'Attendee deleted.');
    }
}
