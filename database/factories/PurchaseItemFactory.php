<?php

namespace Database\Factories;

use App\Models\PurchaseRequisition;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PurchaseItem>
 */
class PurchaseItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'purchase_requisition_id' => PurchaseRequisition::factory()->create()->requisition_id,
            'item_description' => $this->faker->word,
            'quantity' => $this->faker->numberBetween(1, 20),
        ];
    }
}
