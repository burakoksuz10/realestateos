<?php

namespace Modules\CRM\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PipelineStage extends Model
{
    use HasFactory;

    protected $fillable = [
        'pipeline_id',
        'name',
        'color',
        'order',
        'probability',
        'is_won_stage',
        'is_lost_stage',
        'auto_actions',
        'required_fields',
    ];

    protected function casts(): array
    {
        return [
            'probability' => 'integer',
            'is_won_stage' => 'boolean',
            'is_lost_stage' => 'boolean',
            'auto_actions' => 'array',
            'required_fields' => 'array',
        ];
    }

    /**
     * Get the pipeline
     */
    public function pipeline()
    {
        return $this->belongsTo(Pipeline::class);
    }

    /**
     * Get leads in this stage
     */
    public function leads()
    {
        return $this->hasMany(Lead::class, 'stage_id');
    }

    /**
     * Get deals in this stage
     */
    public function deals()
    {
        return $this->hasMany(Deal::class, 'stage_id');
    }

    /**
     * Get next stage
     */
    public function getNextStageAttribute()
    {
        return PipelineStage::where('pipeline_id', $this->pipeline_id)
            ->where('order', '>', $this->order)
            ->orderBy('order')
            ->first();
    }

    /**
     * Get previous stage
     */
    public function getPreviousStageAttribute()
    {
        return PipelineStage::where('pipeline_id', $this->pipeline_id)
            ->where('order', '<', $this->order)
            ->orderBy('order', 'desc')
            ->first();
    }
}
