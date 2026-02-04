<?php

namespace Modules\Websites\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'form_id',
        'lead_id',
        'data',
        'ip_address',
        'user_agent',
        'referrer',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_content',
        'utm_term',
        'page_url',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
        ];
    }

    public function form()
    {
        return $this->belongsTo(Form::class);
    }

    public function lead()
    {
        return $this->belongsTo(\Modules\CRM\Models\Lead::class);
    }
}
