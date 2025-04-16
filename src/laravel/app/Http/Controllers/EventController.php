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
        $start_time = Carbon::parse($request->start_time)->toDateTimeString();
        $end_time = Carbon::parse($request->end_time)->toDateTimeString();

        $validatedData = $request->validated();
        $validatedData['start_time'] = $start_time;
        $validatedData['end_time'] = $end_time;

        // Check for overlapping events at the same location
        $conflict = Event::where('location', $validatedData['location'])
            ->where(function ($query) use ($start_time, $end_time) {
                $query->whereBetween('start_time', [$start_time, $end_time])
                    ->orWhereBetween('end_time', [$start_time, $end_time])
                    ->orWhere(function ($query) use ($start_time, $end_time) {
                        $query->where('start_time', '<=', $start_time)
                            ->where('end_time', '>=', $end_time);
                    });
            })->exists();

        if ($conflict) {
            return $this->errorResponse('An event already exists at this location and time.', 422);
        }

        $event = $request->user()->events()->create($validatedData);

        return $this->successResponse($event, 'Event registered successfully.', 201);
    }

    // api/events/{{event}} POST
    public function show(Event $event)
    {
        $getEvent = $event->load('attendees');
        return $this->successResponse($getEvent, 'Event listed successfully.');
    }

    public function update(EventRequest $request, Event $event)
    {
        $event->update($request->validated());
        return response()->json($event);
    }

    public function destroy(Event $event)
    {
        $event->delete();
        return response()->json(null, 204);
    }
}
