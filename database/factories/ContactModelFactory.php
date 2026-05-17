<?php

declare(strict_types=1);

namespace Database\Factories;

use Infrastructure\Persistence\Eloquent\Models\ContactModel;
use Illuminate\Database\Eloquent\Factories\Factory;

final class ContactModelFactory extends Factory
{
    protected $model = ContactModel::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->numerify('1########0'), // 10 digits
            'score' => 0,
            'status' => 'pending',
            'processed_at' => null,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'score' => $this->faker->numberBetween(10, 60),
            'processed_at' => now(),
        ]);
    }

    public function processing(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'processing',
        ]);
    }
}
