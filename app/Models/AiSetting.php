<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiSetting extends Model
{
    protected $fillable = [
        'office_id',
        'openai_key',
        'openai_organization',
        'preferred_model',
        'features_enabled',
        'custom_prompts',
    ];

    protected $casts = [
        'openai_key' => 'encrypted',
        'features_enabled' => 'array',
        'custom_prompts' => 'array',
    ];

    protected $hidden = [
        'openai_key', // never serialise the raw key
    ];

    public function office(): BelongsTo
    {
        return $this->belongsTo(\Modules\Core\Models\Office::class);
    }

    /**
     * Resolve the OpenAI key for a given office (null when not overridden).
     */
    public static function keyFor(int $officeId): ?string
    {
        $row = static::where('office_id', $officeId)->first();
        return $row?->openai_key ?: null;
    }

    public function isFeatureEnabled(string $name): bool
    {
        $features = $this->features_enabled ?? [];
        return $features[$name] ?? true;
    }

    public function maskedKey(): ?string
    {
        $key = $this->openai_key;
        if (!$key) return null;
        return substr($key, 0, 7) . '...' . substr($key, -4);
    }
}
