<?php

namespace Database\Factories;

use App\Models\BlogPost;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class BlogPostFactory extends Factory
{
    protected $model = BlogPost::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence,
            'body' => $this->faker->paragraphs(4, true),
            'tags' => implode(',', $this->faker->words(3)),
            'published_at' => $this->faker->dateTimeBetween('-1 years', 'now'),
        ];
    }
}
