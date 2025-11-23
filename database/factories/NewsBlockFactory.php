<?php

namespace Database\Factories;

use App\Models\News;
use App\Models\NewsBlock;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\NewsBlock>
 */
class NewsBlockFactory extends Factory
{
    protected $model = NewsBlock::class;

    public function definition(): array
    {
        $types = ['text', 'image', 'text_image_left', 'text_image_right'];
        $type  = $this->faker->randomElement($types);

        return [
            'news_id'    => News::inRandomOrder()->value('id') ?? News::factory(),
            'type'       => $type,
            'text'       => in_array($type, ['text', 'text_image_left', 'text_image_right'], true)
                ? $this->faker->paragraph()
                : null,
            'image_path' => in_array($type, ['image', 'text_image_left', 'text_image_right'], true)
                ? "images/example.jpg"
                : null,
            'position'   => $this->faker->numberBetween(1, 5),
        ];
    }
}
