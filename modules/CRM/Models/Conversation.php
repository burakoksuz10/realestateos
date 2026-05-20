<?php

namespace Modules\CRM\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Conversation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'office_id',
        'contact_id',
        'lead_id',
        'assigned_to',
        'channel',
        'channel_thread_id',
        'subject',
        'status',
        'unread_count',
        'last_message_at',
        'last_message_preview',
        'last_message_direction',
        'meta',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'meta' => 'array',
        'unread_count' => 'integer',
    ];

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at');
    }

    public function latestMessage(): HasMany
    {
        return $this->hasMany(Message::class)->latest('created_at')->limit(1);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(\Modules\Core\Models\Office::class);
    }

    public function markAsRead(): void
    {
        if ($this->unread_count > 0) {
            $this->update(['unread_count' => 0]);
        }
        $this->messages()
            ->where('direction', 'in')
            ->whereNull('read_at')
            ->update(['read_at' => now(), 'status' => 'read']);
    }

    public function touchLastMessage(Message $message): void
    {
        $preview = $message->body
            ?: ($message->attachments ? '[ek]' : '');

        $this->update([
            'last_message_at' => $message->created_at ?? now(),
            'last_message_preview' => mb_substr((string) $preview, 0, 400),
            'last_message_direction' => $message->direction,
            'unread_count' => $message->direction === 'in'
                ? $this->unread_count + 1
                : $this->unread_count,
        ]);
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeForChannel($query, string $channel)
    {
        return $query->where('channel', $channel);
    }
}
