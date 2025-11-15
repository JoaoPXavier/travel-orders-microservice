<?php

namespace Database\Factories;

use App\Models\TravelOrder;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TravelOrder>
 */
class TravelOrderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TravelOrder::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'order_id' => 'ORDER-' . $this->faker->unique()->numberBetween(1000, 9999),
            'applicant_name' => $this->faker->name(),
            'destination' => $this->faker->city(),
            'departure_date' => $this->faker->dateTimeBetween('+1 week', '+1 month')->format('Y-m-d'),
            'return_date' => $this->faker->dateTimeBetween('+1 month', '+2 months')->format('Y-m-d'),
            'status' => $this->faker->randomElement(['solicitado', 'aprovado', 'cancelado']),
        ];
    }

    /**
     * Indicate that the travel order is requested.
     */
    public function requested(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'solicitado',
        ]);
    }

    /**
     * Indicate that the travel order is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'aprovado',
        ]);
    }

    /**
     * Indicate that the travel order is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelado',
        ]);
    }

    /**
     * Indicate specific destination.
     */
    public function destination(string $destination): static
    {
        return $this->state(fn (array $attributes) => [
            'destination' => $destination,
        ]);
    }
}