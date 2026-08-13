<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'name' => Str::title($name),
            'slug' => Str::slug($name).'-'.fake()->unique()->numerify('####'),
            'sku' => strtoupper(fake()->unique()->bothify('SKU-####??')),
            'description' => fake()->paragraph(),
            'price' => fake()->randomFloat(2, 49, 4999),
            'stock' => fake()->numberBetween(0, 100),
            'image_path' => null,
            'is_active' => true,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Product $product) {
            $product->categories()->sync(
                Category::factory()->count(fake()->numberBetween(1, 2))->create()->pluck('id')->all()
            );
        });
    }
}
