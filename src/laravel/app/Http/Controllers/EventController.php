<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\EventRequest;
use App\Models\Event;

use App\Traits\StandardAPIResponse;

class EventController extends Controller
{
    use StandardAPIResponse;

    // api/events GET
    public function index(Request $request)
    {

        try {

            // Apply optional filters
            $query = Event::query();

            if ($request->has('location')) {
                $query->where('location', $request->location);
            }

            // Paginate the results
            $perPage = $request->get('per_page', 10);
            $events = $query->paginate($perPage);

            return $this->successResponse($events, 'Events listed successfully.');
        } catch (\Throwable $e) {

            Log::error('EventController@index Error:', [
                'error' => $e->getMessage(),
            ]);

            return $this->errorResponse(
                'Something went wrong.',
                ['error' => [$e->getMessage()]],
                500
            );
        }
    }

    // api/events POST
    public function store(EventRequest $request)
    {

        try {

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
        } catch (\Throwable $e) {

            Log::error('EventController@store Error:', [
                'error' => $e->getMessage(),
            ]);

            return $this->errorResponse(
                'Something went wrong.',
                ['error' => [$e->getMessage()]],
                500
            );
        }
    }

    // api/events/{{event}} POST
    public function show($event)
    {

        try {
            $getEvent = Event::find($event);

            if (!$getEvent) {
                return $this->errorResponse(
                    'No event found.',
                    ['Event' => ['No matching events found.']],
                    403
                );
            }

            return $this->successResponse($getEvent, 'Event listed successfully.');
        } catch (\Throwable $e) {

            Log::error('EventController@store Error:', [
                'error' => $e->getMessage(),
                'event_id' => $event,
            ]);

            return $this->errorResponse(
                'Something went wrong.',
                ['error' => [$e->getMessage()]],
                500
            );
        }
    }

    public function update(EventRequest $request, $event)
    {

        try {
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
        } catch (\Throwable $e) {

            Log::error('EventController@store Error:', [
                'error' => $e->getMessage(),
                'event_id' => $event,
            ]);

            return $this->errorResponse(
                'Something went wrong.',
                ['error' => [$e->getMessage()]],
                500
            );
        }
    }

    public function destroy($event)
    {
        try {
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
        } catch (\Throwable $e) {

            Log::error('EventController@destroy Error:', [
                'error' => $e->getMessage(),
                'event_id' => $event,
            ]);

            return $this->errorResponse(
                'Something went wrong.',
                ['error' => [$e->getMessage()]],
                500
            );
        }
    }
}
