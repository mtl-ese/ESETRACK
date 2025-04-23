<?php

namespace Database\Factories;

use App\Models\StoreRequisition;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StoreItem>
 */
class StoreItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'store_requisition_id' => StoreRequisition::factory()->create()->requisition_id,
            'item_name' => $this->faker->word,
            'serial_number' => strtoupper($this->faker->bothify('SN-####')),
        ];
    }
}
