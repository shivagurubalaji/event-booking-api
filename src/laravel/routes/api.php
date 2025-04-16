<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventController;
use App\Http\Controllers\AttendeeController;
use App\Http\Controllers\BookingController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('events', [EventController::class, 'index']);
    Route::post('events', [EventController::class, 'store']);
    Route::apiResource('events.attendees', AttendeeController::class)->except(['store']);
    Route::post('events/{event}/book', [BookingController::class, 'store']);
});

Route::post('/events/{event}/attendees', [AttendeeController::class, 'store']);

Route::get('/ping', function () {
    return response()->json(['message' => 'pong']);
});

