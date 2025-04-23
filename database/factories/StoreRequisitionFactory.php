<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StoreRequisition>
 */
class StoreRequisitionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'requisition_id' => strtoupper(Str::random(8)),
            'client_name' => fake()->company,
            'requested_on' => fake()->date(),
            'created_by' => User::factory()->create()->id,
            'approved_by'=>fake()->name,
        ];
    }
}
