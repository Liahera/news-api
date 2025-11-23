<?php

namespace Tests\Feature\Api\V1\News;

use App\Models\News;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class NewsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_news_with_blocks(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $payload = [
            'title'             => 'My first news',
            'short_description' => 'Short description',
            'is_published'      => true,
            'blocks'            => [
                [
                    'type'     => 'text',
                    'text'     => 'First block',
                    'position' => 1,
                ],
                [
                    'type'     => 'text_image_right',
                    'text'     => 'Second block',
                    'position' => 2,
                ],
            ],
        ];

        $response = $this->postJson('/api/v1/news/store', $payload);

        $response->assertCreated()
            ->assertJsonPath('title', 'My first news')
            ->assertJsonCount(2, 'blocks');

        $this->assertDatabaseHas('news', [
            'title'      => 'My first news',
            'author_id'  => $user->id,
            'is_published' => 1,
        ]);

        $this->assertDatabaseHas('news_blocks', [
            'text' => 'First block',
        ]);
    }

    public function test_user_can_get_own_news_index_with_filters(): void
    {
        $user = User::factory()->create();

        $otherUser = User::factory()->create();

        Sanctum::actingAs($user);

        News::factory()->count(2)->create([
            'author_id'    => $user->id,
            'title'        => 'Laravel news',
            'short_description' => 'Something about Laravel',
            'is_published' => true,
        ]);

        News::factory()->create([
            'author_id'    => $user->id,
            'title'        => 'Hidden news',
            'is_published' => false,
        ]);

        News::factory()->create([
            'author_id'    => $otherUser->id,
            'title'        => 'Other user news',
            'is_published' => true,
        ]);

        $response = $this->getJson('/api/v1/news/my?q=Laravel&status=true');

        $response->assertOk()
            ->assertJsonPath('total', 2);
    }

    public function test_public_index_shows_only_published_news(): void
    {
        $user = User::factory()->create();

        News::factory()->create([
            'author_id'    => $user->id,
            'title'        => 'Published news',
            'is_published' => true,
        ]);

        News::factory()->create([
            'author_id'    => $user->id,
            'title'        => 'Draft news',
            'is_published' => false,
        ]);

        $response = $this->getJson('/api/v1/public/news');

        $response->assertOk();

        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertSame('Published news', $data[0]['title']);
    }

    public function test_public_show_returns_published_news(): void
    {
        $user = User::factory()->create();

        $news = News::factory()->create([
            'author_id'    => $user->id,
            'title'        => 'Published news',
            'is_published' => true,
        ]);

        $response = $this->getJson('/api/v1/public/news/'.$news->id);

        $response->assertOk()
            ->assertJsonPath('id', $news->id)
            ->assertJsonPath('title', 'Published news');
    }

    public function test_public_show_returns_404_for_unpublished_news(): void
    {
        $user = User::factory()->create();

        $news = News::factory()->create([
            'author_id'    => $user->id,
            'title'        => 'Draft news',
            'is_published' => false,
        ]);

        $this->getJson('/api/v1/public/news/'.$news->id)
            ->assertNotFound();
    }

    public function test_user_cannot_update_foreign_news(): void
    {
        $owner = User::factory()->create();

        $other = User::factory()->create();

        $news = News::factory()->create([
            'author_id' => $owner->id,
            'title'     => 'Owner news',
        ]);

        Sanctum::actingAs($other);

        $response = $this->putJson('/api/v1/news/'.$news->id, [
            'title' => 'Hacked title',
        ]);

        $response->assertForbidden();
    }

    public function test_user_can_change_status_of_own_news(): void
    {
        $user = User::factory()->create();

        $news = News::factory()->create([
            'author_id'    => $user->id,
            'title'        => 'My news',
            'is_published' => false,
        ]);

        Sanctum::actingAs($user);

        $response = $this->patchJson('/api/v1/news/'.$news->id.'/status', [
            'is_published' => true,
        ]);

        $response->assertOk()
            ->assertJsonPath('is_published', true);

        $this->assertDatabaseHas('news', [
            'id'           => $news->id,
            'is_published' => true,
        ]);
    }
}
