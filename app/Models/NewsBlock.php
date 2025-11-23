<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewsBlock extends Model
{
    use HasFactory;

    protected $fillable = [
        'news_id',
        'type',
        'text',
        'image_path',
        'position',
    ];

    /**
     * Get the news this block belongs to.
     */
    public function news()
    {
        return $this->belongsTo(News::class);
    }
}
