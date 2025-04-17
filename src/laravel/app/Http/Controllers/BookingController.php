<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Services\BookingService;
use App\Traits\StandardAPIResponse;
use Illuminate\Support\Facades\Log;

class BookingController extends Controller
{
    use StandardAPIResponse;

    protected BookingService $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    public function store(Request $request, Event $event)
    {
        try {
            $attendee = $this->bookingService->bookEvent($event, $request->only('name', 'email'));

            return $this->successResponse($attendee, 'Attendee registered successfully.', 201);
        } catch (\Exception $e) {
            Log::error('BookingController@store Error:', [
                'error' => $e->getMessage(),
                'event_id' => $event->id,
                'request' => $request->all(),
            ]);

            return $this->errorResponse(
                $e->getMessage(),
                ['error' => [$e->getMessage()]],
                $e->getCode() ?: 500
            );
        }
    }
}
