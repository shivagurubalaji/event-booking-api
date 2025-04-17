<?php

namespace App\Services;

use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class EventService
{
    public function listEvents(array $filters, int $perPage = 10)
    {
        $query = Event::query();

        if (!empty($filters['location'])) {
            $query->where('location', $filters['location']);
        }

        return $query->paginate($perPage);
    }

    public function createEvent(array $data, $user)
    {
        $data['start_time'] = Carbon::parse($data['start_time'])->toDateTimeString();
        $data['end_time'] = Carbon::parse($data['end_time'])->toDateTimeString();

        if (Event::hasTimeConflict($data['location'], $data['start_time'], $data['end_time'])) {
            throw new \Exception('An event already exists at this location and time.', 422);
        }

        return $user->events()->create($data);
    }

    public function getEvent($eventId)
    {
        return Event::find($eventId);
    }

    public function updateEvent($eventId, array $data)
    {
        $event = Event::find($eventId);

        if (!$event) {
            throw new \Exception('Event not found.', 404);
        }

        $data['start_time'] = Carbon::parse($data['start_time'])->toDateTimeString();
        $data['end_time'] = Carbon::parse($data['end_time'])->toDateTimeString();

        if (Event::hasTimeConflict($data['location'], $data['start_time'], $data['end_time'], $eventId)) {
            throw new \Exception('Another event already exists at this location and time.', 422);
        }

        $event->update($data);
        return $event;
    }

    public function deleteEvent($eventId)
    {
        $event = Event::find($eventId);

        if (!$event) {
            throw new \Exception('Event not found.', 404);
        }

        $event->delete();
        return true;
    }
}
