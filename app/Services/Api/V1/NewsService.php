<?php

namespace App\Services\Api\V1;

use App\Models\News;
use App\Models\User;
use App\Repositories\Api\V1\NewsRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class NewsService
{
    public function __construct(
        private readonly NewsRepository $newsRepository,
    ) {}

    /**
     * Get authenticated user's news list with filters & search.
     */
    public function getUserNewsIndex(User $user, Request $request): LengthAwarePaginator
    {
        return $this->newsRepository->getUserNewsIndex($user, $request);
    }

    /**
     * Public news index with filters & search.
     */
    public function getPublicIndex(Request $request): LengthAwarePaginator
    {
        return $this->newsRepository->getPublicIndex($request);
    }

    /**
     * Get single public news.
     */
    public function getPublicNews(int $id): News
    {
        return $this->newsRepository->getPublicById($id);
    }

    /**
     * Show a single news item with relations (for owner context).
     */
    public function show(News $news): News
    {
        return $news->load(['blocks', 'author']);
    }

    /**
     * Create a news item with optional blocks.
     */
    public function create(User $user, array $data): News
    {
        $image = $data['image'] ?? null;
        if ($image !== null) {
            $data['image_path'] = $image->store('news', 'public');
        }
        unset($data['image']);

        $data['author_id'] = $user->id;

        if (!empty($data['is_published']) && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        $blocks = $data['blocks'] ?? [];
        unset($data['blocks']);

        $news = News::create($data);

        if (!empty($blocks)) {
            foreach ($blocks as $index => $blockData) {
                $news->blocks()->create([
                    'type'     => $blockData['type'],
                    'text'     => $blockData['text'] ?? null,
                    'position' => $blockData['position'] ?? $index,
                ]);
            }
        }

        return $news->load(['blocks', 'author']);
    }

    /**
     * Update a news item and its blocks.
     */
    public function update(News $news, array $data): News
    {
        $image = $data['image'] ?? null;
        if ($image !== null) {
            $data['image_path'] = $image->store('news', 'public');
        }
        unset($data['image']);

        if (isset($data['is_published']) && $data['is_published'] && $news->published_at === null) {
            $data['published_at'] = now();
        }

        $blocks = $data['blocks'] ?? null;
        unset($data['blocks']);

        $news->update($data);

        if ($blocks !== null) {
            $news->blocks()->delete();

            foreach ($blocks as $index => $blockData) {
                $news->blocks()->create([
                    'type'     => $blockData['type'],
                    'text'     => $blockData['text'] ?? null,
                    'position' => $blockData['position'] ?? $index,
                ]);
            }
        }

        return $news->load(['blocks', 'author']);
    }

    /**
     * Change the publication status of a news item (hide/show).
     */
    public function changeStatus(News $news, bool $isPublished): News
    {
        $news->is_published = $isPublished;

        if ($isPublished && $news->published_at === null) {
            $news->published_at = now();
        }

        $news->save();

        return $news;
    }
}
