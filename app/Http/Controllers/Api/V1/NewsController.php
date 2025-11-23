<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\News\ChangeNewsStatusRequest;
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
    ) {}

    /**
     * Get authenticated user's news list with basic search and filters.
     */
    public function index(Request $request): JsonResponse
    {
        $news = $this->newsService->getUserNewsIndex(
            $request->user(),
            $request
        );

        return response()->json($news);
    }

    /**
     * Store a newly created news item.
     */
    public function store(StoreNewsRequest $request): JsonResponse
    {
        $news = $this->newsService->create(
            $request->user(),
            $request->validated()
        );

        return response()->json($news, 201);
    }

    /**
     * Show a specific news item belonging to the authenticated user.
     */
    public function show(News $news, Request $request): JsonResponse
    {
        $this->authorize('view', $news);

        $news = $this->newsService->show($news);

        return response()->json($news);
    }

    /**
     * Update an existing news item.
     */
    public function update(UpdateNewsRequest $request, News $news): JsonResponse
    {
        $this->authorize('update', $news);

        $updated = $this->newsService->update(
            $news,
            $request->validated()
        );

        return response()->json($updated);
    }

    /**
     * Change the publication status of a news item (hide/show).
     */
    public function changeStatus(ChangeNewsStatusRequest $request, News $news): JsonResponse
    {
        $this->authorize('update', $news);

        $updated = $this->newsService->changeStatus(
            $news,
            $request->validated()['is_published']
        );

        return response()->json($updated);
    }

    /**
     * Public index: list all published news with search & filters.
     */
    public function publicIndex(Request $request): JsonResponse
    {
        $news = $this->newsService->getPublicIndex($request);

        return response()->json($news);
    }

    /**
     * Public show: single published news.
     */
    public function publicShow(int $id): JsonResponse
    {
        $news = $this->newsService->getPublicNews($id);

        return response()->json($news);
    }
}
