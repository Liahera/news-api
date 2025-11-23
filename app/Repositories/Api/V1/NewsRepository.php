<?php

namespace App\Repositories\Api\V1;

use App\Filters\Api\V1\NewsFilter;
use App\Models\News;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class NewsRepository
{
    public function __construct(
        private readonly NewsFilter $newsFilter,
    ) {}

    public function getUserNewsIndex(User $user, Request $request): LengthAwarePaginator
    {
        $query = News::query()
            ->where('author_id', $user->id);

        $this->newsFilter->applyForOwner($query, $request);

        return $query
            ->orderByDesc('created_at')
            ->paginate(20);
    }

    public function getPublicIndex(Request $request): LengthAwarePaginator
    {
        $query = News::query();

        $this->newsFilter->applyForPublic($query, $request);

        return $query
            ->orderByDesc('published_at')
            ->paginate(20);
    }

    public function getPublicById(int $id): News
    {
        $news = News::query()
            ->where('id', $id)
            ->where('is_published', true)
            ->with(['blocks', 'author'])
            ->firstOrFail();

        return $news;
    }
}
