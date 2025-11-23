<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class News extends Model
{
    use HasFactory;

    protected $fillable = [
        'author_id',
        'title',
        'slug',
        'image_path',
        'short_description',
        'is_published',
        'published_at',
    ];

    protected $casts = [
        'is_published' => 'bool',
        'published_at' => 'datetime',
    ];

    /**
     * Get the author (user) of the news.
     */
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Get content blocks for the news.
     */
    public function blocks()
    {
        return $this->hasMany(NewsBlock::class);
    }

    /**
     * Auto-generate slug on creation if not provided.
     */
    protected static function booted(): void
    {
        static::creating(static function (News $news): void {
            if (empty($news->slug)) {
                $news->slug = Str::slug($news->title) . '-' . Str::random(6);
            }
        });
    }
}
