<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Models\Event;
use App\Models\Attendee;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BookingServiceTest extends TestCase
{
    use RefreshDatabase;

    protected BookingService $bookingService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bookingService = new BookingService();
    }

    public function test_book_event_successfully()
    {
        $event = Event::factory()->create(['capacity' => 2]);

        $attendeeData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ];

        $attendee = $this->bookingService->bookEvent($event, $attendeeData);

        $this->assertInstanceOf(Attendee::class, $attendee);
        $this->assertEquals('John Doe', $attendee->name);
        $this->assertDatabaseHas('attendees', [
            'email' => 'john@example.com',
            'event_id' => $event->id,
        ]);
    }

    public function test_duplicate_booking_throws_exception()
    {
        $event = Event::factory()->create(['capacity' => 10]);

        $attendee = Attendee::factory()->create([
            'event_id' => $event->id,
            'email' => 'duplicate@example.com',
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('You have already booked to this event.');

        $this->bookingService->bookEvent($event, [
            'name' => 'Jane Doe',
            'email' => 'duplicate@example.com',
        ]);
    }

    public function test_booking_when_event_is_full_throws_exception()
    {
        $event = Event::factory()->create(['capacity' => 1]);

        Attendee::factory()->create([
            'event_id' => $event->id,
            'email' => 'first@example.com',
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('This event has reached its capacity.');

        $this->bookingService->bookEvent($event, [
            'name' => 'Second Person',
            'email' => 'second@example.com',
        ]);
    }
}
