<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsArticle extends Model
{
    protected $fillable = [
        'title', 'summary', 'ai_summary', 'url', 'source', 'source_url',
        'image_url', 'category', 'sentiment', 'tags', 'is_featured', 'published_at',
    ];

    protected $casts = [
        'tags' => 'array',
        'is_featured' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function scopeLatest($query)
    {
        return $query->orderBy('published_at', 'desc');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'piyasa' => 'Piyasa',
            'yatirim' => 'Yatırım',
            'konut' => 'Konut',
            'ticari' => 'Ticari',
            'mevzuat' => 'Mevzuat',
            'teknoloji' => 'Teknoloji',
            default => 'Genel',
        };
    }

    public function getCategoryColorAttribute(): string
    {
        return match ($this->category) {
            'piyasa' => 'blue',
            'yatirim' => 'green',
            'konut' => 'orange',
            'ticari' => 'purple',
            'mevzuat' => 'red',
            'teknoloji' => 'cyan',
            default => 'gray',
        };
    }
}
