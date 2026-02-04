<?php

namespace Modules\RealEstate\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

class Project extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia, HasTranslations;

    public $translatable = ['name', 'description', 'slogan'];

    protected $fillable = [
        'office_id',
        'developer_id',
        'name',
        'slug',
        'slogan',
        'description',
        'status', // planning, under_construction, completed, selling
        'type', // residential, commercial, mixed
        
        // Location
        'country',
        'city',
        'district',
        'neighborhood',
        'address',
        'latitude',
        'longitude',
        
        // Project Details
        'total_units',
        'available_units',
        'sold_units',
        'total_blocks',
        'total_floors',
        'land_area',
        'construction_area',
        
        // Pricing
        'min_price',
        'max_price',
        'price_currency',
        'payment_plans',
        
        // Features
        'features',
        'amenities',
        'unit_types',
        
        // Dates
        'start_date',
        'estimated_completion',
        'actual_completion',
        'sales_start_date',
        
        // Meta
        'website_url',
        'brochure_url',
        'video_url',
        'virtual_tour_url',
        'seo_title',
        'seo_description',
        
        'is_featured',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'min_price' => 'decimal:2',
            'max_price' => 'decimal:2',
            'land_area' => 'decimal:2',
            'construction_area' => 'decimal:2',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'features' => 'array',
            'amenities' => 'array',
            'unit_types' => 'array',
            'payment_plans' => 'array',
            'start_date' => 'date',
            'estimated_completion' => 'date',
            'actual_completion' => 'date',
            'sales_start_date' => 'date',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Register media collections
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')
            ->singleFile()
            ->useDisk('public');

        $this->addMediaCollection('gallery')
            ->useDisk('public');

        $this->addMediaCollection('floor_plans')
            ->useDisk('public');

        $this->addMediaCollection('documents')
            ->useDisk('private');

        $this->addMediaCollection('renders')
            ->useDisk('public');
    }

    /**
     * Get the office that owns the project
     */
    public function office()
    {
        return $this->belongsTo(\Modules\Core\Models\Office::class);
    }

    /**
     * Get the developer
     */
    public function developer()
    {
        return $this->belongsTo(\Modules\CRM\Models\Contact::class, 'developer_id');
    }

    /**
     * Get all listings in this project
     */
    public function listings()
    {
        return $this->hasMany(Listing::class);
    }

    /**
     * Get available listings
     */
    public function availableListings()
    {
        return $this->listings()->where('status', 'active');
    }

    /**
     * Get price range formatted
     */
    public function getPriceRangeAttribute(): string
    {
        $symbol = match($this->price_currency) {
            'TRY' => '₺',
            'USD' => '$',
            'EUR' => '€',
            default => $this->price_currency,
        };

        $min = number_format($this->min_price, 0, ',', '.');
        $max = number_format($this->max_price, 0, ',', '.');

        return "{$symbol}{$min} - {$symbol}{$max}";
    }

    /**
     * Get completion percentage
     */
    public function getCompletionPercentageAttribute(): int
    {
        if ($this->status === 'completed') {
            return 100;
        }

        if ($this->status === 'planning') {
            return 0;
        }

        if (!$this->start_date || !$this->estimated_completion) {
            return 0;
        }

        $total = $this->start_date->diffInDays($this->estimated_completion);
        $elapsed = $this->start_date->diffInDays(now());

        return min(100, max(0, round(($elapsed / $total) * 100)));
    }

    /**
     * Get sales percentage
     */
    public function getSalesPercentageAttribute(): int
    {
        if ($this->total_units === 0) {
            return 0;
        }

        return round(($this->sold_units / $this->total_units) * 100);
    }

    /**
     * Scope for active projects
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for featured projects
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($project) {
            if (!$project->slug) {
                $project->slug = \Str::slug($project->name);
            }
        });
    }
}
