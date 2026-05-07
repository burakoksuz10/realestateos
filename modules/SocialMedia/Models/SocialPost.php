<?php

namespace Modules\SocialMedia\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class SocialPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'platform',
        'content_type',
        'caption',
        'media_url',
        'media_type',
        'status',
        'scheduled_at',
        'published_at',
        'publish_payload',
        'meta_response',
        'error_message',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'published_at' => 'datetime',
        'publish_payload' => 'array',
        'meta_response' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
