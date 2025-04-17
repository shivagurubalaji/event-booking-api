<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class EventControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function authenticate()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');
        return $user;
    }

    public function test_can_list_events()
    {
        Event::factory()->count(3)->create();

        $response = $this->getJson('/api/events');

        $response->assertStatus(200)
                 ->assertJsonStructure(['data']);
    }

    public function test_can_create_event()
    {
        $user = $this->authenticate();

        $data = [
            'event_name' => 'Test Event',
            'event_description' => 'Testing event creation.',
            'location' => 'Test Hall',
            'start_time' => Carbon::now()->addHour()->toIso8601String(),
            'end_time' => Carbon::now()->addHours(2)->toIso8601String(),
            'capacity' => 100,
            'status' => 'active'
        ];

        $response = $this->postJson('/api/events', $data);

        $response->assertStatus(201)
                 ->assertJsonFragment(['event_name' => 'Test Event']);
    }

    public function test_show_event_successfully()
    {
        $event = Event::factory()->create();

        $response = $this->getJson("/api/events/{$event->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['id' => $event->id]);
    }

    public function test_update_event()
    {
        $user = $this->authenticate();
        $event = Event::factory()->for($user)->create();

        $newData = [
            'event_name' => 'Updated Name',
            'event_description' => $event->event_description,
            'location' => $event->location,
            'start_time' => Carbon::now()->addHours(3)->toIso8601String(),
            'end_time' => Carbon::now()->addHours(4)->toIso8601String(),
            'capacity' => $event->capacity,
            'status' => $event->status,
        ];

        $response = $this->putJson("/api/events/{$event->id}", $newData);

        $response->assertStatus(200);
    }

    public function test_delete_event()
    {
        $user = $this->authenticate();
        $event = Event::factory()->for($user)->create();

        $response = $this->deleteJson("/api/events/{$event->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['message' => 'Event deleted.']);
    }
}
