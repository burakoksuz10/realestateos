<?php

namespace Modules\Telegram\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TelegramUser extends Model
{
    protected $table = 'telegram_users';

    protected $fillable = [
        'user_id',
        'telegram_chat_id',
        'telegram_user_id',
        'telegram_username',
        'first_name',
        'last_name',
        'language_code',
        'pairing_code',
        'pairing_expires_at',
        'linked_at',
        'is_active',
        'preferences',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'pairing_expires_at' => 'datetime',
        'linked_at' => 'datetime',
        'preferences' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
