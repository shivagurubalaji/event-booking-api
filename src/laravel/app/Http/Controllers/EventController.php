<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Requests\EventRequest;
use App\Models\Event;

class EventController extends Controller
{
    public function index()
    {
        return response()->json(Event::with('attendees')->get());
    }

    public function store(EventRequest $request)
    {
        // Support for ISO 8601 datetime
        $start_time = Carbon::parse($request->start_time)->toDateTimeString();
        $end_time = Carbon::parse($request->end_time)->toDateTimeString();

        $validatedData = $request->validated();
        $validatedData['start_time'] = $start_time;
        $validatedData['end_time'] = $end_time;

        $event = $request->user()->events()->create($validatedData);
        return response()->json($event, 201);
        //$event = $request->user()->events()->create($request->validated());
        //return response()->json($event, 201);
    }

    public function show(Event $event)
    {
        return response()->json($event->load('attendees'));
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
