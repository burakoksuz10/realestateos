<?php

namespace Modules\Websites\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Page extends Model
{
    use HasFactory, SoftDeletes, HasTranslations;

    public $translatable = ['title', 'content', 'meta_title', 'meta_description'];

    protected $fillable = [
        'website_id',
        'parent_id',
        'title',
        'slug',
        'content',
        'template',
        'blocks',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'og_image',
        'is_published',
        'published_at',
        'order',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'blocks' => 'array',
            'settings' => 'array',
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function website()
    {
        return $this->belongsTo(Website::class);
    }

    public function parent()
    {
        return $this->belongsTo(Page::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Page::class, 'parent_id')->orderBy('order');
    }

    public function getFullUrlAttribute(): string
    {
        return $this->website->full_url . '/' . $this->slug;
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }
}
