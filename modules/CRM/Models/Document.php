<?php

namespace Modules\CRM\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Polimorfik doküman modeli — Lead/Deal/Contact/Listing'e dosya bağlar.
 *
 * KVKK uyumu için:
 *  - SoftDeletes → delete'ten sonra audit trail kayboluyor değil
 *  - LogsActivity → her create/update/delete activity_log'a düşer
 *  - is_confidential default true → kullanıcı bilinçli olarak işaretlemediyse gizli kabul
 *  - file_path "local" diskte (public değil), erişim sadece authorize edilmiş route'tan
 */
class Document extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'documentable_type',
        'documentable_id',
        'office_id',
        'uploaded_by',
        'title',
        'original_name',
        'file_path',
        'mime_type',
        'file_size',
        'category',
        'notes',
        'is_confidential',
    ];

    protected function casts(): array
    {
        return [
            'is_confidential' => 'boolean',
            'file_size'       => 'integer',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'category', 'is_confidential', 'documentable_type', 'documentable_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(\Modules\Core\Models\Office::class);
    }

    public function getFormattedSizeAttribute(): string
    {
        $bytes = (int) $this->file_size;
        if ($bytes < 1024) return $bytes . ' B';
        if ($bytes < 1024 * 1024) return number_format($bytes / 1024, 1) . ' KB';
        return number_format($bytes / (1024 * 1024), 2) . ' MB';
    }

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'contract'  => 'Sözleşme',
            'identity'  => 'Kimlik',
            'deed'      => 'Tapu',
            'valuation' => 'Ekspertiz',
            'photo'     => 'Foto',
            'other'     => 'Diğer',
            default     => ucfirst($this->category ?? '—'),
        };
    }
}
