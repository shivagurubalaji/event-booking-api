<?php

namespace App\Http\Controllers;

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
        $event = $request->user()->events()->create($request->validated());
        return response()->json($event, 201);
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
