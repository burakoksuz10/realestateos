<?php

namespace Modules\Websites\Models;

use Illuminate\Database\Eloquent\Model;

class VisitorTracking extends Model
{
    protected $table = 'visitor_tracking';

    protected $fillable = [
        'website_id',
        'visitor_id',
        'session_id',
        'contact_id',
        'lead_id',
        'event_type',
        'event_data',
        'page_url',
        'referrer',
        'ip_address',
        'user_agent',
        'device_type',
        'browser',
        'os',
        'country',
        'city',
        'utm_source',
        'utm_medium',
        'utm_campaign',
    ];

    protected function casts(): array
    {
        return [
            'event_data' => 'array',
        ];
    }

    public function website()
    {
        return $this->belongsTo(Website::class);
    }

    public function contact()
    {
        return $this->belongsTo(\Modules\CRM\Models\Contact::class);
    }

    public function lead()
    {
        return $this->belongsTo(\Modules\CRM\Models\Lead::class);
    }

    public function scopePageViews($query)
    {
        return $query->where('event_type', 'page_view');
    }

    public function scopeListingViews($query)
    {
        return $query->where('event_type', 'listing_view');
    }

    public function scopeFavorites($query)
    {
        return $query->where('event_type', 'favorite');
    }
}
