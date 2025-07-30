<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word,
            'description' => $this->faker->paragraph,
            'category' => $this->faker->randomElement(['Electronics', 'Books', 'Clothing', 'Home', 'Toys']),
            'price' => $this->faker->randomFloat(2, 10, 1000),
        ];
    }
}
