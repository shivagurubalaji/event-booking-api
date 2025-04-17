<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAttendeeRequest;
use App\Models\Event;
use App\Services\AttendeeService;
use App\Traits\StandardAPIResponse;
use Illuminate\Support\Facades\Log;

class AttendeeController extends Controller
{
    use StandardAPIResponse;

    protected AttendeeService $attendeeService;

    public function __construct(AttendeeService $attendeeService)
    {
        $this->attendeeService = $attendeeService;
    }

    public function store(StoreAttendeeRequest $request, Event $event)
    {
        try {
            $attendee = $this->attendeeService->register($event, $request->validated(), $request->user()->id);
            return $this->successResponse($attendee, 'Attendee registered successfully.', 201);
        } catch (\Throwable $e) {
            return $this->handleException($e, 'Attendee registration failed');
        }
    }

    public function index($eventId)
    {
        try {
            $attendees = $this->attendeeService->getEventAttendees($eventId);
            return $this->successResponse($attendees, 'Attendees list loaded.', 200);
        } catch (\Throwable $e) {
            return $this->handleException($e, 'Fetching attendees failed');
        }
    }

    public function show($eventId, $attendeeId)
    {
        try {
            $attendee = $this->attendeeService->getAttendee($attendeeId);
            return $this->successResponse($attendee, 'Attendee details loaded.', 200);
        } catch (\Throwable $e) {
            return $this->handleException($e, 'Fetching attendee failed');
        }
    }

    public function update(StoreAttendeeRequest $request, $eventId, $attendeeId)
    {
        try {
            $attendee = $this->attendeeService->updateAttendee($attendeeId, $request->validated());
            return $this->successResponse($attendee, 'Attendee updated successfully.', 200);
        } catch (\Throwable $e) {
            return $this->handleException($e, 'Updating attendee failed');
        }
    }

    public function destroy($eventId, $attendeeId)
    {
        try {
            $this->attendeeService->deleteAttendee($attendeeId);
            return $this->successResponse(null, 'Attendee deleted.', 200);
        } catch (\Throwable $e) {
            return $this->handleException($e, 'Deleting attendee failed');
        }
    }

    private function handleException(\Throwable $e, string $logMessage)
    {
        Log::error($logMessage, ['error' => $e->getMessage()]);
        $status = in_array($e->getCode(), [403, 404, 409]) ? $e->getCode() : 500;

        return $this->errorResponse(
            'Something went wrong.',
            ['error' => [$e->getMessage()]],
            $status
        );
    }
}
