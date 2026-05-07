<?php

namespace Modules\Advertising\Models;

use Illuminate\Database\Eloquent\Model;

class CampaignMetric extends Model
{
    protected $fillable = [
        'campaign_id', 'tarih', 'harcama', 'gosterim', 'tiklama', 'erisme', 'lead', 'mesaj', 'donusum',
    ];

    protected $casts = ['tarih' => 'date', 'harcama' => 'decimal:2'];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }
}
