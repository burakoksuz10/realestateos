<?php

namespace Modules\RealEstate\Models;

use Illuminate\Database\Eloquent\Model;

class ListingVersion extends Model
{
    protected $fillable = [
        'listing_id',
        'user_id',
        'version_number',
        'data',
        'changes',
        'reason',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'changes' => 'array',
        ];
    }

    /**
     * Get the listing
     */
    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }

    /**
     * Get the user who made the change
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    /**
     * Create a new version from listing
     */
    public static function createFromListing(Listing $listing, ?string $reason = null): self
    {
        $lastVersion = $listing->versions()->latest()->first();
        $versionNumber = $lastVersion ? $lastVersion->version_number + 1 : 1;

        $changes = [];
        if ($lastVersion) {
            $oldData = $lastVersion->data;
            $newData = $listing->toArray();
            
            foreach ($newData as $key => $value) {
                if (isset($oldData[$key]) && $oldData[$key] !== $value) {
                    $changes[$key] = [
                        'old' => $oldData[$key],
                        'new' => $value,
                    ];
                }
            }
        }

        return self::create([
            'listing_id' => $listing->id,
            'user_id' => auth()->id(),
            'version_number' => $versionNumber,
            'data' => $listing->toArray(),
            'changes' => $changes,
            'reason' => $reason,
        ]);
    }

    /**
     * Restore this version to the listing
     */
    public function restore(): Listing
    {
        $listing = $this->listing;
        
        $restoreData = collect($this->data)->except([
            'id', 'created_at', 'updated_at', 'deleted_at'
        ])->toArray();

        $listing->update($restoreData);

        // Create a new version noting the restoration
        self::createFromListing($listing, "Restored from version {$this->version_number}");

        return $listing->fresh();
    }
}
