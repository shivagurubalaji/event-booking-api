<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Models\Event;
use App\Models\User;
use App\Models\Attendee;
use App\Services\AttendeeService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AttendeeServiceTest extends TestCase
{
    use RefreshDatabase;

    protected AttendeeService $attendeeService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->attendeeService = new AttendeeService();
    }

    public function test_register_attendee_successfully()
    {
        $user = User::factory()->create();
        $event = Event::factory()->create(['capacity' => 2]);

        $attendeeData = [
            'name' => 'Test Attendee',
            'email' => 'test@example.com',
        ];

        $attendee = $this->attendeeService->register($event, $attendeeData, $user->id);

        $this->assertInstanceOf(Attendee::class, $attendee);
        $this->assertDatabaseHas('attendees', [
            'email' => 'test@example.com',
            'event_id' => $event->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_prevent_duplicate_attendee_registration()
    {
        $user = User::factory()->create();
        $event = Event::factory()->create(['capacity' => 2]);

        // First registration
        $this->attendeeService->register($event, [
            'name' => 'Test',
            'email' => 'duplicate@example.com',
        ], $user->id);

        // Second registration with same email should fail
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('This attendee is already registered.');

        $this->attendeeService->register($event, [
            'name' => 'Test Again',
            'email' => 'duplicate@example.com',
        ], $user->id);
    }

    public function test_prevent_registration_when_event_is_full()
    {
        $user = User::factory()->create();
        $event = Event::factory()->create(['capacity' => 1]);

        Attendee::factory()->create(['event_id' => $event->id]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('This event has reached its capacity.');

        $this->attendeeService->register($event, [
            'name' => 'Late User',
            'email' => 'late@example.com',
        ], $user);
    }
}
