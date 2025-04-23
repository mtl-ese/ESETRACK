<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\purchaseRequisition>
 */
class purchaseRequisitionFactory extends Factory
{ /**
    * Define the model's default state.
    *
    * @return array<string, mixed>
    */
   public function definition(): array
   {
       return [
           'requisition_id' => strtoupper(Str::random(10)),
           'created_by' => User::factory(),
           'requested_on'=>now(),
           'approved_by'=>fake()->name,
       ];
   }
}
