<?php

namespace Database\Factories;

use App\Models\Reservation;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Office;

class ReservationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Reservation::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'office_id' => Office::factory(),
            'price' => $this->faker->numberBetween(10_000, 20_000),
            'status' => Reservation::STATUS_ACTIVE,
            'start_date' => now()->addDay()->format('Y-m-d'),
            'end_date' => now()->addDays(5)->format('Y-m-d'),            
        ];
    }
}
