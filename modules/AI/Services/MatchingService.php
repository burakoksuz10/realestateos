<?php

namespace Modules\AI\Services;

use Modules\RealEstate\Models\Listing;
use Modules\CRM\Models\Lead;
use Modules\CRM\Models\Contact;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MatchingService
{
    protected AIService $ai;

    public function __construct(AIService $ai)
    {
        $this->ai = $ai;
    }

    /**
     * Find matching listings for a lead
     */
    public function findMatchingListings(Lead $lead, int $limit = 20): Collection
    {
        $query = Listing::active();

        // Apply hard filters
        $this->applyHardFilters($query, $lead);

        // Get candidates
        $candidates = $query->take($limit * 2)->get();

        // Score and rank
        $scored = $candidates->map(function ($listing) use ($lead) {
            $listing->match_score = $this->calculateMatchScore($listing, $lead);
            $listing->match_reasons = $this->getMatchReasons($listing, $lead);
            return $listing;
        });

        // Sort by score and return top matches
        return $scored->sortByDesc('match_score')->take($limit)->values();
    }

    /**
     * Find matching leads for a listing
     */
    public function findMatchingLeads(Listing $listing, int $limit = 20): Collection
    {
        $query = Lead::whereIn('status', ['new', 'contacted', 'qualified'])
            ->whereNotNull('contact_id');

        // Filter by interest type
        if ($listing->listing_type === 'sale') {
            $query->where('interest_type', 'buy');
        } elseif ($listing->listing_type === 'rent') {
            $query->where('interest_type', 'rent');
        }

        // Filter by budget
        $query->where(function ($q) use ($listing) {
            $q->whereNull('budget_max')
              ->orWhere('budget_max', '>=', $listing->price * 0.9);
        });

        $candidates = $query->take($limit * 2)->get();

        // Score and rank
        $scored = $candidates->map(function ($lead) use ($listing) {
            $lead->match_score = $this->calculateLeadMatchScore($listing, $lead);
            return $lead;
        });

        return $scored->sortByDesc('match_score')->take($limit)->values();
    }

    /**
     * Find similar listings
     */
    public function findSimilarListings(Listing $listing, int $limit = 10): Collection
    {
        return Listing::where('id', '!=', $listing->id)
            ->where('status', 'active')
            ->where('city', $listing->city)
            ->where('type', $listing->type)
            ->where('listing_type', $listing->listing_type)
            ->whereBetween('price', [
                $listing->price * 0.7,
                $listing->price * 1.3
            ])
            ->when($listing->gross_sqm, function ($q) use ($listing) {
                $q->whereBetween('gross_sqm', [
                    $listing->gross_sqm * 0.7,
                    $listing->gross_sqm * 1.3
                ]);
            })
            ->orderByRaw('ABS(price - ?) ASC', [$listing->price])
            ->take($limit)
            ->get();
    }

    /**
     * Semantic search for listings
     */
    public function semanticSearch(string $query, int $limit = 20): Collection
    {
        // Extract search intent
        $intent = $this->extractSearchIntent($query);

        $dbQuery = Listing::active();

        // Apply extracted filters
        if ($intent['listing_type']) {
            $dbQuery->where('listing_type', $intent['listing_type']);
        }

        if ($intent['property_type']) {
            $dbQuery->where('type', $intent['property_type']);
        }

        if ($intent['city']) {
            $dbQuery->where('city', 'like', "%{$intent['city']}%");
        }

        if ($intent['district']) {
            $dbQuery->where('district', 'like', "%{$intent['district']}%");
        }

        if ($intent['min_rooms']) {
            $dbQuery->where('room_count', '>=', $intent['min_rooms']);
        }

        if ($intent['budget_max']) {
            $dbQuery->where('price', '<=', $intent['budget_max']);
        }

        // Apply feature filters
        if (!empty($intent['features'])) {
            foreach ($intent['features'] as $feature) {
                $dbQuery->where(function ($q) use ($feature) {
                    $q->whereJsonContains('features', $feature)
                      ->orWhere('description', 'like', "%{$feature}%");
                });
            }
        }

        return $dbQuery->orderBy('quality_score', 'desc')
            ->take($limit)
            ->get();
    }

    /**
     * Extract search intent from natural language query
     */
    protected function extractSearchIntent(string $query): array
    {
        $messages = [
            [
                'role' => 'system',
                'content' => 'Extract real estate search criteria from the query. Return JSON with: listing_type (sale/rent/null), property_type (apartment/villa/office/etc or null), city (string or null), district (string or null), min_rooms (int or null), budget_max (number or null), features (array of strings like "deniz manzarası", "havuz", etc).'
            ],
            [
                'role' => 'user',
                'content' => $query
            ]
        ];

        return $this->ai->chatJson($messages) ?? [
            'listing_type' => null,
            'property_type' => null,
            'city' => null,
            'district' => null,
            'min_rooms' => null,
            'budget_max' => null,
            'features' => [],
        ];
    }

    /**
     * Apply hard filters to query
     */
    protected function applyHardFilters($query, Lead $lead): void
    {
        // Interest type
        if ($lead->interest_type === 'buy') {
            $query->forSale();
        } elseif ($lead->interest_type === 'rent') {
            $query->forRent();
        }

        // Property type
        if ($lead->property_type) {
            $query->where('type', $lead->property_type);
        }

        // Budget (with 20% flexibility)
        if ($lead->budget_max) {
            $query->where('price', '<=', $lead->budget_max * 1.2);
        }

        // Locations
        if (!empty($lead->preferred_locations)) {
            $query->where(function ($q) use ($lead) {
                foreach ($lead->preferred_locations as $location) {
                    $q->orWhere('city', 'like', "%{$location}%")
                      ->orWhere('district', 'like', "%{$location}%")
                      ->orWhere('neighborhood', 'like', "%{$location}%");
                }
            });
        }
    }

    /**
     * Calculate match score between listing and lead
     */
    protected function calculateMatchScore(Listing $listing, Lead $lead): int
    {
        $score = 0;
        $maxScore = 100;

        // Budget match (30 points)
        if ($lead->budget_min || $lead->budget_max) {
            $budgetScore = 0;
            if ($lead->budget_min && $lead->budget_max) {
                if ($listing->price >= $lead->budget_min && $listing->price <= $lead->budget_max) {
                    $budgetScore = 30;
                } elseif ($listing->price <= $lead->budget_max * 1.1) {
                    $budgetScore = 20;
                } elseif ($listing->price <= $lead->budget_max * 1.2) {
                    $budgetScore = 10;
                }
            } elseif ($lead->budget_max && $listing->price <= $lead->budget_max) {
                $budgetScore = 25;
            }
            $score += $budgetScore;
        } else {
            $score += 15; // Neutral if no budget specified
        }

        // Location match (25 points)
        if (!empty($lead->preferred_locations)) {
            foreach ($lead->preferred_locations as $location) {
                if (stripos($listing->city, $location) !== false) {
                    $score += 15;
                    break;
                }
                if (stripos($listing->district, $location) !== false) {
                    $score += 25;
                    break;
                }
                if (stripos($listing->neighborhood, $location) !== false) {
                    $score += 25;
                    break;
                }
            }
        } else {
            $score += 12; // Neutral
        }

        // Room match (15 points)
        if ($lead->room_requirement) {
            if ($listing->room_count >= $lead->room_requirement) {
                $score += 15;
            } elseif ($listing->room_count >= $lead->room_requirement - 1) {
                $score += 8;
            }
        } else {
            $score += 7;
        }

        // Size match (15 points)
        if ($lead->size_min || $lead->size_max) {
            $sizeMatch = true;
            if ($lead->size_min && $listing->gross_sqm < $lead->size_min * 0.9) {
                $sizeMatch = false;
            }
            if ($lead->size_max && $listing->gross_sqm > $lead->size_max * 1.1) {
                $sizeMatch = false;
            }
            $score += $sizeMatch ? 15 : 5;
        } else {
            $score += 7;
        }

        // Quality bonus (15 points)
        $score += ($listing->quality_score / 100) * 15;

        return min($maxScore, $score);
    }

    /**
     * Calculate match score for lead (reverse matching)
     */
    protected function calculateLeadMatchScore(Listing $listing, Lead $lead): int
    {
        return $this->calculateMatchScore($listing, $lead);
    }

    /**
     * Get match reasons
     */
    protected function getMatchReasons(Listing $listing, Lead $lead): array
    {
        $reasons = [];

        // Budget
        if ($lead->budget_max && $listing->price <= $lead->budget_max) {
            $reasons[] = 'Bütçeye uygun';
        }

        // Location
        if (!empty($lead->preferred_locations)) {
            foreach ($lead->preferred_locations as $location) {
                if (stripos($listing->district, $location) !== false || 
                    stripos($listing->city, $location) !== false) {
                    $reasons[] = "İstenen lokasyonda ({$location})";
                    break;
                }
            }
        }

        // Rooms
        if ($lead->room_requirement && $listing->room_count >= $lead->room_requirement) {
            $reasons[] = 'Oda sayısı uygun';
        }

        // Size
        if ($lead->size_min && $listing->gross_sqm >= $lead->size_min) {
            $reasons[] = 'Metrekare uygun';
        }

        // Quality
        if ($listing->quality_score >= 70) {
            $reasons[] = 'Yüksek kaliteli ilan';
        }

        return $reasons;
    }

    /**
     * Generate weekly digest for leads
     */
    public function generateWeeklyDigest(Lead $lead): array
    {
        $newListings = $this->findMatchingListings($lead, 10)
            ->filter(function ($listing) {
                return $listing->created_at >= now()->subWeek();
            });

        $priceDrops = Listing::active()
            ->where('original_price', '>', DB::raw('price'))
            ->where('updated_at', '>=', now()->subWeek())
            ->take(5)
            ->get();

        return [
            'new_matches' => $newListings,
            'price_drops' => $priceDrops,
            'market_update' => $this->getMarketUpdate($lead),
        ];
    }

    /**
     * Get market update for lead's preferences
     */
    protected function getMarketUpdate(Lead $lead): array
    {
        $locations = $lead->preferred_locations ?? [];
        
        if (empty($locations)) {
            return [];
        }

        $location = $locations[0];
        
        $stats = Listing::active()
            ->where(function ($q) use ($location) {
                $q->where('city', 'like', "%{$location}%")
                  ->orWhere('district', 'like', "%{$location}%");
            })
            ->selectRaw('
                COUNT(*) as total_listings,
                AVG(price) as avg_price,
                MIN(price) as min_price,
                MAX(price) as max_price
            ')
            ->first();

        return [
            'location' => $location,
            'total_listings' => $stats->total_listings ?? 0,
            'avg_price' => round($stats->avg_price ?? 0, -3),
            'price_range' => [
                'min' => round($stats->min_price ?? 0, -3),
                'max' => round($stats->max_price ?? 0, -3),
            ],
        ];
    }
}
