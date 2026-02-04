<?php

namespace Modules\CRM\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Contact extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'office_id',
        'assigned_to',
        'type', // individual, company
        'status', // active, inactive, blacklisted
        
        // Personal Info
        'first_name',
        'last_name',
        'email',
        'phone',
        'phone_secondary',
        'whatsapp',
        
        // Company Info (if type is company)
        'company_name',
        'company_title',
        'tax_number',
        'tax_office',
        
        // Address
        'address',
        'city',
        'district',
        'postal_code',
        'country',
        
        // Preferences
        'preferred_contact_method', // phone, email, whatsapp, sms
        'preferred_contact_time',
        'language',
        
        // Property Preferences
        'property_preferences',
        'budget_min',
        'budget_max',
        'budget_currency',
        'preferred_locations',
        
        // Source
        'source', // website, referral, portal, social, walk_in, call
        'source_detail',
        'referral_contact_id',
        
        // KVKK
        'kvkk_consent',
        'kvkk_consent_date',
        'marketing_consent',
        'marketing_consent_date',
        
        // Meta
        'tags',
        'notes',
        'custom_fields',
        'last_contact_at',
    ];

    protected function casts(): array
    {
        return [
            'property_preferences' => 'array',
            'preferred_locations' => 'array',
            'tags' => 'array',
            'custom_fields' => 'array',
            'budget_min' => 'decimal:2',
            'budget_max' => 'decimal:2',
            'kvkk_consent' => 'boolean',
            'kvkk_consent_date' => 'datetime',
            'marketing_consent' => 'boolean',
            'marketing_consent_date' => 'datetime',
            'last_contact_at' => 'datetime',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['first_name', 'last_name', 'email', 'phone', 'status', 'assigned_to'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Get the office
     */
    public function office()
    {
        return $this->belongsTo(\Modules\Core\Models\Office::class);
    }

    /**
     * Get the assigned user
     */
    public function assignedTo()
    {
        return $this->belongsTo(\App\Models\User::class, 'assigned_to');
    }

    /**
     * Get referral contact
     */
    public function referralContact()
    {
        return $this->belongsTo(Contact::class, 'referral_contact_id');
    }

    /**
     * Get leads for this contact
     */
    public function leads()
    {
        return $this->hasMany(Lead::class);
    }

    /**
     * Get deals for this contact
     */
    public function deals()
    {
        return $this->hasMany(Deal::class);
    }

    /**
     * Get activities for this contact
     */
    public function activities()
    {
        return $this->hasMany(Activity::class);
    }

    /**
     * Get tasks for this contact
     */
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Get full name
     */
    public function getFullNameAttribute(): string
    {
        if ($this->type === 'company') {
            return $this->company_name ?? "{$this->first_name} {$this->last_name}";
        }
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Get display name
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->type === 'company' && $this->company_name) {
            return $this->company_name;
        }
        return $this->full_name;
    }

    /**
     * Get initials
     */
    public function getInitialsAttribute(): string
    {
        if ($this->type === 'company' && $this->company_name) {
            return strtoupper(substr($this->company_name, 0, 2));
        }
        return strtoupper(substr($this->first_name, 0, 1) . substr($this->last_name, 0, 1));
    }

    /**
     * Get budget range formatted
     */
    public function getBudgetRangeAttribute(): ?string
    {
        if (!$this->budget_min && !$this->budget_max) {
            return null;
        }

        $symbol = match($this->budget_currency) {
            'TRY' => '₺',
            'USD' => '$',
            'EUR' => '€',
            default => $this->budget_currency ?? '₺',
        };

        $min = $this->budget_min ? $symbol . number_format($this->budget_min, 0, ',', '.') : '';
        $max = $this->budget_max ? $symbol . number_format($this->budget_max, 0, ',', '.') : '';

        if ($min && $max) {
            return "{$min} - {$max}";
        }

        return $min ?: $max;
    }

    /**
     * Scope for active contacts
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for search
     */
    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('first_name', 'like', "%{$term}%")
              ->orWhere('last_name', 'like', "%{$term}%")
              ->orWhere('email', 'like', "%{$term}%")
              ->orWhere('phone', 'like', "%{$term}%")
              ->orWhere('company_name', 'like', "%{$term}%");
        });
    }

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($contact) {
            if (!$contact->office_id && auth()->check()) {
                $contact->office_id = auth()->user()->office_id;
            }
        });
    }
}
