<?php

namespace Modules\RealEstate\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\Translatable\HasTranslations;

class Listing extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia, LogsActivity, HasTranslations;

    public $translatable = ['title', 'description', 'features_text'];

    protected $fillable = [
        'office_id',
        'agent_id',
        'project_id',
        'reference_no',
        'title',
        'slug',
        'description',
        'type',
        'category',
        'status',
        'listing_type', // sale, rent, daily_rent
        'price',
        'price_currency',
        'price_per_sqm',
        'original_price',
        'is_negotiable',
        'commission_rate',
        'commission_type', // percentage, fixed
        
        // Location
        'country',
        'city',
        'district',
        'neighborhood',
        'address',
        'postal_code',
        'latitude',
        'longitude',
        'location_description',
        
        // Property Details
        'gross_sqm',
        'net_sqm',
        'land_sqm',
        'room_count',
        'living_room_count',
        'bathroom_count',
        'floor_number',
        'total_floors',
        'building_age',
        'heating_type',
        'fuel_type',
        'facade',
        'is_furnished',
        'furniture_status',
        
        // Features
        'features',
        'features_text',
        'amenities',
        'nearby_places',
        
        // Documents
        'deed_status',
        'deed_type',
        'zoning_status',
        'usage_status',
        'is_in_site',
        'site_name',
        'dues_amount',
        
        // Meta
        'view_count',
        'favorite_count',
        'inquiry_count',
        'quality_score',
        'seo_title',
        'seo_description',
        'seo_keywords',
        
        // Dates
        'available_from',
        'published_at',
        'expires_at',
        'sold_at',
        'rented_at',
        
        // Authorization
        'authorization_type', // exclusive, open
        'authorization_start',
        'authorization_end',
        
        // Portal sync
        'portal_sync_enabled',
        'portal_ids',
        'last_synced_at',
        
        // AI Generated
        'ai_description',
        'ai_valuation',
        'ai_suggestions',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'price_per_sqm' => 'decimal:2',
            'original_price' => 'decimal:2',
            'commission_rate' => 'decimal:2',
            'dues_amount' => 'decimal:2',
            'gross_sqm' => 'decimal:2',
            'net_sqm' => 'decimal:2',
            'land_sqm' => 'decimal:2',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'is_negotiable' => 'boolean',
            'is_furnished' => 'boolean',
            'is_in_site' => 'boolean',
            'portal_sync_enabled' => 'boolean',
            'features' => 'array',
            'amenities' => 'array',
            'nearby_places' => 'array',
            'portal_ids' => 'array',
            'ai_valuation' => 'array',
            'ai_suggestions' => 'array',
            'available_from' => 'date',
            'published_at' => 'datetime',
            'expires_at' => 'datetime',
            'sold_at' => 'datetime',
            'rented_at' => 'datetime',
            'authorization_start' => 'date',
            'authorization_end' => 'date',
            'last_synced_at' => 'datetime',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'status', 'price', 'agent_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Register media collections
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('photos')
            ->useDisk('public')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);

        $this->addMediaCollection('videos')
            ->useDisk('public')
            ->acceptsMimeTypes(['video/mp4', 'video/webm']);

        $this->addMediaCollection('floor_plans')
            ->useDisk('public')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'application/pdf']);

        $this->addMediaCollection('documents')
            ->useDisk('private')
            ->acceptsMimeTypes(['application/pdf', 'image/jpeg', 'image/png']);

        $this->addMediaCollection('virtual_tour')
            ->useDisk('public');
    }

    /**
     * Get the office that owns the listing
     */
    public function office()
    {
        return $this->belongsTo(\Modules\Core\Models\Office::class);
    }

    /**
     * Get the agent that owns the listing
     */
    public function agent()
    {
        return $this->belongsTo(\App\Models\User::class, 'agent_id');
    }

    /**
     * Get the project if listing belongs to one
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get listing versions (history)
     */
    public function versions()
    {
        return $this->hasMany(ListingVersion::class);
    }

    /**
     * Get portal sync logs
     */
    public function portalSyncLogs()
    {
        return $this->hasMany(PortalSyncLog::class);
    }

    /**
     * Get inquiries for this listing
     */
    public function inquiries()
    {
        return $this->hasMany(\Modules\CRM\Models\Lead::class, 'listing_id');
    }

    /**
     * Get showings for this listing
     */
    public function showings()
    {
        return $this->hasMany(\Modules\CRM\Models\Activity::class, 'listing_id')
            ->where('type', 'showing');
    }

    /**
     * Get formatted price
     */
    public function getFormattedPriceAttribute(): string
    {
        $symbol = match($this->price_currency) {
            'TRY' => '₺',
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            default => $this->price_currency,
        };

        return $symbol . number_format($this->price, 0, ',', '.');
    }

    /**
     * Get room info
     */
    public function getRoomInfoAttribute(): string
    {
        if ($this->room_count && $this->living_room_count) {
            return "{$this->room_count}+{$this->living_room_count}";
        }
        return $this->room_count ? "{$this->room_count} Oda" : '-';
    }

    /**
     * Get full location
     */
    public function getFullLocationAttribute(): string
    {
        return implode(', ', array_filter([
            $this->neighborhood,
            $this->district,
            $this->city,
        ]));
    }

    /**
     * Calculate quality score
     */
    public function calculateQualityScore(): int
    {
        $score = 0;
        
        // Photos (max 30 points)
        $photoCount = $this->getMedia('photos')->count();
        $score += min($photoCount * 3, 30);
        
        // Description (max 20 points)
        $descLength = strlen($this->description ?? '');
        if ($descLength > 500) $score += 20;
        elseif ($descLength > 200) $score += 10;
        elseif ($descLength > 50) $score += 5;
        
        // Features (max 15 points)
        $featureCount = count($this->features ?? []);
        $score += min($featureCount, 15);
        
        // Location data (max 15 points)
        if ($this->latitude && $this->longitude) $score += 10;
        if ($this->neighborhood) $score += 5;
        
        // Floor plan (10 points)
        if ($this->getMedia('floor_plans')->count() > 0) $score += 10;
        
        // Virtual tour (10 points)
        if ($this->getMedia('virtual_tour')->count() > 0) $score += 10;
        
        return min($score, 100);
    }

    /**
     * Scope for active listings
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for sale listings
     */
    public function scopeForSale($query)
    {
        return $query->where('listing_type', 'sale');
    }

    /**
     * Scope for rent listings
     */
    public function scopeForRent($query)
    {
        return $query->where('listing_type', 'rent');
    }

    /**
     * Scope for location
     */
    public function scopeInLocation($query, $city, $district = null)
    {
        $query->where('city', $city);
        
        if ($district) {
            $query->where('district', $district);
        }
        
        return $query;
    }

    /**
     * Scope for price range
     */
    public function scopePriceRange($query, $min = null, $max = null)
    {
        if ($min) {
            $query->where('price', '>=', $min);
        }
        
        if ($max) {
            $query->where('price', '<=', $max);
        }
        
        return $query;
    }

    /**
     * Search scope
     */
    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('title', 'like', "%{$term}%")
              ->orWhere('reference_no', 'like', "%{$term}%")
              ->orWhere('description', 'like', "%{$term}%")
              ->orWhere('address', 'like', "%{$term}%");
        });
    }

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($listing) {
            if (!$listing->reference_no) {
                $listing->reference_no = 'RE-' . strtoupper(uniqid());
            }
            if (!$listing->slug) {
                $listing->slug = \Str::slug($listing->title) . '-' . $listing->reference_no;
            }
        });

        static::saving(function ($listing) {
            $listing->quality_score = $listing->calculateQualityScore();
            
            if ($listing->gross_sqm && $listing->price) {
                $listing->price_per_sqm = $listing->price / $listing->gross_sqm;
            }
        });
    }
}
