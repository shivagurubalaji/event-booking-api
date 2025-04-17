<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Event;
use App\Models\Attendee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendeeControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user, 'sanctum');
    }

    public function test_can_register_attendee()
    {
        $event = Event::factory()->create(['capacity' => 10, 'user_id' => $this->user->id]);

        $response = $this->postJson("/api/events/{$event->id}/attendees", [
            'name' => 'Shiva Guru',
            'email' => 'shiva@events.com',
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('attendees', ['email' => 'shiva@events.com']);
    }

    public function test_duplicate_attendee_registration_fails()
    {
        $event = Event::factory()->create(['capacity' => 10, 'user_id' => $this->user->id]);
        Attendee::factory()->create(['event_id' => $event->id, 'email' => 'shiva@events.com']);

        $response = $this->postJson("/api/events/{$event->id}/attendees", [
            'name' => 'Shiva Guru',
            'email' => 'shiva@events.com',
        ]);

        $response->assertStatus(409);
    }

    public function test_event_full_registration_fails()
    {
        $event = Event::factory()->create(['capacity' => 1, 'user_id' => $this->user->id]);
        Attendee::factory()->create(['event_id' => $event->id]);

        $response = $this->postJson("/api/events/{$event->id}/attendees", [
            'name' => 'Shiva Guru',
            'email' => 'shiva@events.com',
        ]);

        $response->assertStatus(403);
    }

    public function test_can_list_attendees()
    {
        $event = Event::factory()->create(['user_id' => $this->user->id]);
        Attendee::factory()->count(2)->create(['event_id' => $event->id]);

        $response = $this->getJson("/api/events/{$event->id}/attendees");
        $response->assertStatus(200)
                 ->assertJsonStructure(['data']);
    }

    public function test_can_view_single_attendee()
    {
        $event = Event::factory()->create(['user_id' => $this->user->id]);
        $attendee = Attendee::factory()->create();

        $response = $this->getJson("/api/events/{$event->id}/attendees/{$attendee->id}");

        $response->assertOk()
                 ->assertJsonFragment(['email' => $attendee->email]);
    }

    public function test_can_update_attendee()
    {
        $attendee = Attendee::factory()->create();

        $response = $this->putJson("/api/events/{$attendee->event_id}/attendees/{$attendee->id}", [
            'name' => 'Updated Name',
            'email' => 'updated@events.com',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('attendees', ['email' => 'updated@events.com']);
    }

    public function test_can_delete_attendee()
    {
        $event = Event::factory()->create(['user_id' => $this->user->id]);
        $attendee = Attendee::factory()->create();

        $response = $this->deleteJson("/api/events/{$event->id}/attendees/{$attendee->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('attendees', ['id' => $attendee->id]);
    }
}
