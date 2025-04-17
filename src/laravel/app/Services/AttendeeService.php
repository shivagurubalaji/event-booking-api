<?php

// app/Services/AttendeeService.php

namespace App\Services;

use App\Models\Event;
use App\Models\Attendee;
use Illuminate\Support\Facades\Log;

class AttendeeService
{
    public function register(Event $event, array $data, $userId)
    {
        if ($event->attendees()->where('email', $data['email'])->exists()) {
            throw new \Exception('This attendee is already registered.', 409);
        }

        if ($event->attendees()->count() >= $event->capacity) {
            throw new \Exception('This event has reached its capacity.', 403);
        }

        return $event->attendees()->create([...$data, 'user_id' => $userId]);
    }

    public function getEventAttendees($eventId)
    {
        return Event::with('attendees')->findOrFail($eventId)->attendees;
    }

    public function getAttendee($attendeeId)
    {
        return Attendee::findOrFail($attendeeId);
    }

    public function updateAttendee($attendeeId, array $data)
    {
        $attendee = Attendee::findOrFail($attendeeId);
        $attendee->update($data);
        return $attendee;
    }

    public function deleteAttendee($attendeeId)
    {
        $attendee = Attendee::findOrFail($attendeeId);
        return $attendee->delete();
    }
}
