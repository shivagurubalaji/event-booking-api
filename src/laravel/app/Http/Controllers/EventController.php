<?php

namespace App\Http\Controllers;

use App\Http\Requests\EventRequest;
use App\Services\EventService;
use App\Traits\StandardAPIResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EventController extends Controller
{
    use StandardAPIResponse;

    protected EventService $eventService;

    public function __construct(EventService $eventService)
    {
        $this->eventService = $eventService;
    }

    public function index(Request $request)
    {
        try {
            $events = $this->eventService->listEvents($request->only('location'), $request->get('per_page', 10));
            return $this->successResponse($events, 'Events listed successfully.');
        } catch (\Throwable $e) {
            Log::error('EventController@index Error:', ['error' => $e->getMessage()]);
            return $this->errorResponse('Something went wrong.', ['error' => [$e->getMessage()]], 500);
        }
    }

    public function store(EventRequest $request)
    {
        try {
            $event = $this->eventService->createEvent($request->validated(), $request->user());
            return $this->successResponse($event, 'Event registered successfully.', 201);
        } catch (\Throwable $e) {
            Log::error('EventController@store Error:', ['error' => $e->getMessage()]);
            return $this->errorResponse($e->getMessage(), [], $e->getCode() ?: 500);
        }
    }

    public function show($eventId)
    {
        try {
            $event = $this->eventService->getEvent($eventId);

            if (!$event) {
                return $this->errorResponse('No event found.', ['Event' => ['No matching events found.']], 404);
            }

            return $this->successResponse($event, 'Event listed successfully.');
        } catch (\Throwable $e) {
            Log::error('EventController@show Error:', ['error' => $e->getMessage(), 'event_id' => $eventId]);
            return $this->errorResponse('Something went wrong.', ['error' => [$e->getMessage()]], 500);
        }
    }

    public function update(EventRequest $request, $eventId)
    {
        try {
            $event = $this->eventService->updateEvent($eventId, $request->validated());
            return $this->successResponse($event, 'Event updated successfully.');
        } catch (\Throwable $e) {
            Log::error('EventController@update Error:', ['error' => $e->getMessage(), 'event_id' => $eventId]);
            return $this->errorResponse($e->getMessage(), [], $e->getCode() ?: 500);
        }
    }

    public function destroy($eventId)
    {
        try {
            $this->eventService->deleteEvent($eventId);
            return $this->successResponse("Event", 'Event deleted.');
        } catch (\Throwable $e) {
            Log::error('EventController@destroy Error:', ['error' => $e->getMessage(), 'event_id' => $eventId]);
            return $this->errorResponse($e->getMessage(), [], $e->getCode() ?: 500);
        }
    }
}
