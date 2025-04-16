<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Requests\EventRequest;
use App\Models\Event;

use App\Traits\StandardAPIResponse;

class EventController extends Controller
{
    use StandardAPIResponse;

    // api/events GET
    public function index(Request $request)
    {
        //return response()->json(Event::with('attendees')->get());
        //$allevents = Event::get();
        //return $this->successResponse($allevents, 'Events listed successfully.', 201);

        // Apply optional filters
        $query = Event::query();

        if ($request->has('location')) {
            $query->where('location', $request->location);
        }

        // Paginate the results
        $perPage = $request->get('per_page', 10);
        $events = $query->paginate($perPage);

        return $this->successResponse($events, 'Events listed successfully.');
    }

    // api/events POST
    public function store(EventRequest $request)
    {
        // Parse ISO 8601 datetime
        //$start_time = Carbon::parse($request->start_time)->toDateTimeString();
        //$end_time = Carbon::parse($request->end_time)->toDateTimeString();

        $validatedData = $request->validated();
        $validatedData['start_time'] = Carbon::parse($request->start_time)->toDateTimeString();
        $validatedData['end_time'] = Carbon::parse($request->end_time)->toDateTimeString();

        if (Event::hasTimeConflict(
            $validatedData['location'],
            $validatedData['start_time'],
            $validatedData['end_time']
        )) {
            return $this->errorResponse('An event already exists at this location and time.', 422);
        }

        $event = $request->user()->events()->create($validatedData);

        return $this->successResponse($event, 'Event registered successfully.', 201);
    }

    // api/events/{{event}} POST
    public function show($event)
    {
        $getEvent = Event::find($event);

        if (!$getEvent) {
            return $this->errorResponse(
                'No event found.',
                ['Event' => ['No matching events found.']],
                403
            );
        }
    
        return $this->successResponse($getEvent, 'Event listed successfully.');
    }

    public function update(EventRequest $request, $event)
    {

        $getEvent = Event::find($event);
        if (!$getEvent) {
            return $this->errorResponse(
                'No event found.',
                ['Event' => ['No matching events found.']],
                403
            );
        }

        $validatedData = $request->validated();
        $validatedData['start_time'] = Carbon::parse($request->start_time)->toDateTimeString();
        $validatedData['end_time'] = Carbon::parse($request->end_time)->toDateTimeString();
    
        if (Event::hasTimeConflict(
            $validatedData['location'],
            $validatedData['start_time'],
            $validatedData['end_time'],
            $getEvent->id
        )) {
            return $this->errorResponse('Another event already exists at this location and time.', 422);
        }
    
        $getEvent->update($validatedData);
    
        return $this->successResponse($event, 'Event updated successfully.');
    }

    public function destroy($event)
    {
        $getEvent = Event::find($event);
        if (!$getEvent) {
            return $this->errorResponse(
                'No event found.',
                ['Event' => ['No matching events found.']],
                403
            );
        }
        $getEvent->delete();
        return $this->successResponse("Event", 'Event deleted.');
        // response()->json(null, 204);
    }
}
