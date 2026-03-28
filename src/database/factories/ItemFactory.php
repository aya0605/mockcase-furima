<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\User;
use App\Models\Condition;
use Illuminate\Database\Eloquent\Factories\Factory;

class ItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->word,
            'description' => $this->faker->sentence,
            'image_url' => 'dummy.jpg',
            'price' => 1000,
            'condition_id' => Condition::factory()->create()->id, 
            'seller_id' => User::factory()->create()->id, 
            'brand' => $this->faker->word,
        ];
    }
}
