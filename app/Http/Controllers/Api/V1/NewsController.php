<?php

namespace App\Http\Controllers\Api\V1;

use App\Filters\Api\V1\NewsFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\News\StoreNewsRequest;
use App\Http\Requests\Api\V1\News\UpdateNewsRequest;
use App\Models\News;
use App\Services\Api\V1\NewsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    public function __construct(
        private readonly NewsService $newsService,
        private readonly NewsFilter $newsFilter,
    ) {}

    /**
     * Get authenticated user's news list with basic search and filters.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = News::query()
            ->where('author_id', $user->id);

        $this->newsFilter->applyForOwner($query, $request);

        $news = $query->orderByDesc('created_at')->paginate(20);

        return response()->json($news);
    }

    /**
     * Store a newly created news item.
     */
    public function store(StoreNewsRequest $request): JsonResponse
    {
        $news = $this->newsService->create($request->user(), $request->validated());

        return response()->json($news, 201);
    }

    /**
     * Show a specific news item belonging to the authenticated user.
     */
    public function show(News $news, Request $request): JsonResponse
    {
        $this->authorize('view', $news);

        $news->load('blocks');

        return response()->json($news);
    }

    /**
     * Update an existing news item.
     */
    public function update(UpdateNewsRequest $request, News $news): JsonResponse
    {
        $this->authorize('update', $news);

        $updated = $this->newsService->update($news, $request->validated());

        return response()->json($updated);
    }

    /**
     * Change the publication status of a news item (hide/show).
     */
    public function changeStatus(Request $request, News $news): JsonResponse
    {
        $this->authorize('update', $news);

        $data = $request->validate([
            'is_published' => ['required', 'boolean'],
        ]);

        $updated = $this->newsService->changeStatus($news, $data['is_published']);

        return response()->json($updated);
    }

    /**
     * Public index: list all published news with search & filters.
     */
    public function publicIndex(Request $request): JsonResponse
    {
        $query = News::query();

        $this->newsFilter->applyForPublic($query, $request);

        $news = $query->orderByDesc('published_at')->paginate(20);

        return response()->json($news);
    }

    /**
     * Public show: single published news.
     */
    public function publicShow(int $id): JsonResponse
    {
        $news = News::query()
            ->where('id', $id)
            ->where('is_published', true)
            ->with(['blocks', 'author'])
            ->firstOrFail();

        return response()->json($news);
    }
}
