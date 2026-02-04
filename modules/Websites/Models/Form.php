<?php

namespace Modules\Websites\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Form extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'website_id',
        'name',
        'slug',
        'description',
        'fields',
        'settings',
        'success_message',
        'redirect_url',
        'notification_emails',
        'create_lead',
        'assign_to',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'fields' => 'array',
            'settings' => 'array',
            'notification_emails' => 'array',
            'create_lead' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function website()
    {
        return $this->belongsTo(Website::class);
    }

    public function submissions()
    {
        return $this->hasMany(FormSubmission::class);
    }

    public function assignTo()
    {
        return $this->belongsTo(\App\Models\User::class, 'assign_to');
    }
}
