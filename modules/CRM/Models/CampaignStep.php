<?php

namespace Modules\CRM\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignStep extends Model
{
    use HasFactory;

    protected $table = 'drip_steps';

    protected $fillable = [
        'campaign_id',
        'order',
        'type',
        'config',
        'label',
    ];

    protected $casts = [
        'config' => 'array',
        'order' => 'integer',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    /**
     * Bekleme süresini saniye olarak döner (wait step için).
     */
    public function waitSeconds(): int
    {
        $cfg = $this->config ?? [];
        $days    = (int) ($cfg['days']    ?? 0);
        $hours   = (int) ($cfg['hours']   ?? 0);
        $minutes = (int) ($cfg['minutes'] ?? 0);

        return $days * 86400 + $hours * 3600 + $minutes * 60;
    }

    /**
     * Sıradaki step'i bul (basit linear akış için).
     */
    public function nextStep(): ?CampaignStep
    {
        return self::where('campaign_id', $this->campaign_id)
            ->where('order', '>', $this->order)
            ->orderBy('order')
            ->first();
    }
}
