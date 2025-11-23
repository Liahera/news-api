<?php

namespace App\Filters\Api\V1;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class NewsFilter
{
    public function applyForOwner(Builder $query, Request $request): Builder
    {
        return $this->applyCommon($query, $request)
            ->when($request->filled('status'), static function (Builder $q) use ($request) {
                $q->where('is_published', filter_var($request->status, FILTER_VALIDATE_BOOLEAN));
            });
    }

    public function applyForPublic(Builder $query, Request $request): Builder
    {
        return $this->applyCommon($query, $request)
            ->where('is_published', true);
    }

    private function applyCommon(Builder $query, Request $request): Builder
    {
        return $query
            ->when($request->q, static function (Builder $q, string $search) {
                $q->where(static function (Builder $qq) use ($search) {
                    $qq->where('title', 'like', "%{$search}%")
                        ->orWhere('short_description', 'like', "%{$search}%");
                });
            })
            ->when($request->from, static function (Builder $q, string $from) {
                $q->whereDate('created_at', '>=', $from);
            })
            ->when($request->to, static function (Builder $q, string $to) {
                $q->whereDate('created_at', '<=', $to);
            });
    }
}
