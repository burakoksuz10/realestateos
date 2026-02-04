<?php

namespace Modules\CRM\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pipeline extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'office_id',
        'name',
        'type', // lead, deal
        'description',
        'is_default',
        'is_active',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'settings' => 'array',
        ];
    }

    /**
     * Get the office
     */
    public function office()
    {
        return $this->belongsTo(\Modules\Core\Models\Office::class);
    }

    /**
     * Get stages
     */
    public function stages()
    {
        return $this->hasMany(PipelineStage::class)->orderBy('order');
    }

    /**
     * Get leads in this pipeline
     */
    public function leads()
    {
        return $this->hasMany(Lead::class);
    }

    /**
     * Get deals in this pipeline
     */
    public function deals()
    {
        return $this->hasMany(Deal::class);
    }

    /**
     * Get first stage
     */
    public function getFirstStageAttribute()
    {
        return $this->stages()->orderBy('order')->first();
    }

    /**
     * Scope for active pipelines
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for default pipeline
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope by type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }
}
