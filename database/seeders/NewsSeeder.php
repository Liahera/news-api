<?php

namespace Database\Seeders;

use App\Models\News;
use App\Models\NewsBlock;
use App\Models\User;
use Database\Factories\NewsFactory;
use Database\Factories\NewsBlockFactory;
use Illuminate\Database\Seeder;

class NewsSeeder extends Seeder
{
    public function run(): void
    {
        if (User::count() === 0) {
            User::factory()->count(10)->create();
        }

        $newsItems = News::factory()->count(50)->create();

        foreach ($newsItems as $news) {
            $blocksCount = random_int(1, 5);
            NewsBlock::factory()
                ->count($blocksCount)
                ->create([
                    'news_id' => $news->id,
                ]);
        }
    }
}
