<?php

namespace Database\Factories;

use App\Models\News;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\News>
 */
class NewsFactory extends Factory
{
    protected $model = News::class;

    public function definition(): array
    {
        $title = $this->faker->sentence(4);

        $isPublished = $this->faker->boolean(70);
        $publishedAt = $isPublished
            ? $this->faker->dateTimeBetween('-1 year', 'now')
            : null;

        return [
            'author_id'         => User::inRandomOrder()->value('id') ?? User::factory(),
            'title'             => $title,
            'slug'              => Str::slug($title) . '-' . Str::random(6),
            'image_path'        => null,
            'short_description' => $this->faker->sentence(10),
            'is_published'      => $isPublished,
            'published_at'      => $publishedAt,
        ];
    }
}
