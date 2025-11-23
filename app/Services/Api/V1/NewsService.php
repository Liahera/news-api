<?php

namespace App\Services\Api\V1;

use App\Models\News;
use App\Models\NewsBlock;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;

class NewsService
{
    public function create(User $author, array $data): News
    {
        $data['author_id'] = $author->id;

        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            $data['image_path'] = $data['image']->store('news', 'public');
        }

        if (!empty($data['is_published']) && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        $news = News::create(Arr::except($data, ['blocks', 'image']));

        if (!empty($data['blocks']) && is_array($data['blocks'])) {
            $this->syncBlocks($news, $data['blocks'], false);
        }

        return $news->load('blocks');
    }

    public function update(News $news, array $data): News
    {
        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            $data['image_path'] = $data['image']->store('news', 'public');
        }

        if (isset($data['is_published']) && $data['is_published'] && ! $news->published_at) {
            $data['published_at'] = now();
        }

        $news->update(Arr::except($data, ['blocks', 'image']));

        if (array_key_exists('blocks', $data)) {
            $this->syncBlocks($news, $data['blocks'] ?? []);
        }

        return $news->load('blocks');
    }

    public function changeStatus(News $news, bool $isPublished): News
    {
        $news->is_published = $isPublished;

        if ($isPublished && ! $news->published_at) {
            $news->published_at = now();
        }

        $news->save();

        return $news;
    }

    private function syncBlocks(News $news, array $blocks, bool $deleteExisting = true): void
    {
        if ($deleteExisting) {
            $news->blocks()->delete();
        }

        foreach ($blocks as $index => $blockData) {
            NewsBlock::create([
                'news_id'  => $news->id,
                'type'     => $blockData['type'] ?? 'text',
                'text'     => $blockData['text'] ?? null,
                'position' => $blockData['position'] ?? $index,
            ]);
        }
    }
}
