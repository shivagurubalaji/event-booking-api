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
            \Log::error('Attendee registration failed', [
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
