<?php

namespace Modules\VoiceAgent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Models\Office;

class VoiceAgentConfig extends Model
{
    protected $table = 'voice_agent_configs';

    public const MODE_LISTING_OWNER_FIRST = 'listing_owner_first';
    public const MODE_SECRETARY_ONLY      = 'secretary_only';
    public const MODE_LISTING_OWNER_ONLY  = 'listing_owner_only';
    public const MODE_CALLBACK_ONLY       = 'callback_only';

    public const MODES = [
        self::MODE_LISTING_OWNER_FIRST,
        self::MODE_SECRETARY_ONLY,
        self::MODE_LISTING_OWNER_ONLY,
        self::MODE_CALLBACK_ONLY,
    ];

    protected $fillable = [
        'office_id',
        'is_active',
        'elevenlabs_agent_id',
        'default_voice_id',
        'routing_mode',
        'secretary_phone',
        'default_agent_phone',
        'ring_timeout_seconds',
        'business_hours_start',
        'business_hours_end',
        'weekend_active',
        'timezone',
        'system_prompt',
        'greeting_template',
        'language',
        'telegram_office_channel',
        'settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'weekend_active' => 'boolean',
        'settings' => 'array',
        'ring_timeout_seconds' => 'integer',
        'business_hours_start' => 'datetime:H:i',
        'business_hours_end'   => 'datetime:H:i',
    ];

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    public function isWithinBusinessHours(?\DateTimeInterface $when = null): bool
    {
        $when = $when
            ? \Carbon\Carbon::instance($when)->setTimezone($this->timezone ?: 'Europe/Istanbul')
            : now($this->timezone ?: 'Europe/Istanbul');

        // Hafta sonu kapalıysa
        if ($when->isWeekend() && !$this->weekend_active) {
            return false;
        }

        $start = \Carbon\Carbon::parse($this->business_hours_start ?: '09:00', $this->timezone);
        $end   = \Carbon\Carbon::parse($this->business_hours_end   ?: '19:00', $this->timezone);

        $minutes = (int) $when->format('H') * 60 + (int) $when->format('i');
        $startMin = (int) $start->format('H') * 60 + (int) $start->format('i');
        $endMin   = (int) $end->format('H')   * 60 + (int) $end->format('i');

        return $minutes >= $startMin && $minutes <= $endMin;
    }
}
