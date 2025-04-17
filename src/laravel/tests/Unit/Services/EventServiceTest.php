<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Models\User;
use App\Models\Event;
use App\Services\EventService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class EventServiceTest extends TestCase
{
    use RefreshDatabase;

    protected EventService $eventService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->eventService = new EventService();
    }

    public function test_list_events_with_location_filter()
    {
        Event::factory()->create(['location' => 'NY']);
        Event::factory()->create(['location' => 'LA']);

        $results = $this->eventService->listEvents(['location' => 'NY']);

        $this->assertCount(1, $results);
        $this->assertEquals('NY', $results->first()->location);
    }

    public function test_create_event_successfully()
    {
        $user = User::factory()->create();
        $data = [
            'event_name' => 'Tech Conference',
            'location' => 'New York',
            'start_time' => Carbon::now()->addDays(1)->toDateTimeString(),
            'end_time' => Carbon::now()->addDays(1)->addHours(2)->toDateTimeString(),
            'capacity' => 100,
        ];

        $event = $this->eventService->createEvent($data, $user);

        $this->assertInstanceOf(Event::class, $event);
        $this->assertEquals('Tech Conference', $event->event_name);
        $this->assertDatabaseHas('events', ['event_name' => 'Tech Conference']);
    }

    public function test_event_time_conflict_prevention_on_create()
    {
        $user = User::factory()->create();

        $startTime = Carbon::now()->addDays(2);
        $endTime = Carbon::now()->addDays(2)->addHours(3);

        Event::factory()->create([
            'location' => 'San Francisco',
            'start_time' => $startTime,
            'end_time' => $endTime,
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('An event already exists at this location and time.');

        $this->eventService->createEvent([
            'title' => 'Conflicting Event',
            'location' => 'San Francisco',
            'start_time' => $startTime->toDateTimeString(),
            'end_time' => $endTime->toDateTimeString(),
            'capacity' => 50,
        ], $user);
    }

    public function test_get_event_successfully()
    {
        $event = Event::factory()->create();
        $result = $this->eventService->getEvent($event->id);

        $this->assertInstanceOf(Event::class, $result);
        $this->assertEquals($event->id, $result->id);
    }

    public function test_update_event_successfully()
    {
        $event = Event::factory()->create([
            'event_name' => 'Old Title',
            'location' => 'Chicago',
            'start_time' => Carbon::now()->addDays(5),
            'end_time' => Carbon::now()->addDays(5)->addHour(),
        ]);

        $data = [
            'event_name' => 'Updated Event Title',
            'location' => 'Chicago',
            'start_time' => Carbon::now()->addDays(6)->toDateTimeString(),
            'end_time' => Carbon::now()->addDays(6)->addHours(2)->toDateTimeString(),
            'capacity' => 200,
        ];

        $updatedEvent = $this->eventService->updateEvent($event->id, $data);

        $this->assertEquals('Updated Event Title', $updatedEvent->event_name);
        $this->assertDatabaseHas('events', ['event_name' => 'Updated Event Title']);
    }

    public function test_update_event_with_conflict_throws_exception()
    {
        $existing = Event::factory()->create([
            'location' => 'LA',
            'start_time' => Carbon::now()->addDays(3),
            'end_time' => Carbon::now()->addDays(3)->addHours(2),
        ]);

        $eventToUpdate = Event::factory()->create([
            'location' => 'LA',
            'start_time' => Carbon::now()->addDays(4),
            'end_time' => Carbon::now()->addDays(4)->addHours(2),
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Another event already exists at this location and time.');

        $this->eventService->updateEvent($eventToUpdate->id, [
            'title' => 'Updated Conflict',
            'location' => 'LA',
            'start_time' => $existing->start_time,
            'end_time' => $existing->end_time,
            'capacity' => 100,
        ]);
    }

    public function test_delete_event_successfully()
    {
        $event = Event::factory()->create();
        $result = $this->eventService->deleteEvent($event->id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('events', ['id' => $event->id]);
    }

    public function test_delete_event_that_does_not_exist_throws_exception()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Event not found.');

        $this->eventService->deleteEvent(9999); // non-existent ID
    }
}
