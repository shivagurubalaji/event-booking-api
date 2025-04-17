<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{

    protected $model = Event::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startTime = Carbon::now()->addDays($this->faker->numberBetween(1, 10))->setTime(10, 0);
        $endTime = (clone $startTime)->addHours(2);

        return [
            'user_id' => User::factory(), // Assumes you have a UserFactory
            'event_name' => $this->faker->sentence(3),
            'event_description' => $this->faker->paragraph,
            'location' => $this->faker->city,
            'start_time' => $startTime->toDateTimeString(),
            'end_time' => $endTime->toDateTimeString(),
            'capacity' => $this->faker->numberBetween(50, 200),
            'status' => $this->faker->randomElement(['upcoming', 'active', 'cancelled', 'completed']),
        ];
    }
}
