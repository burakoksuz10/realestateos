<?php

namespace Modules\AI\Services;

use Modules\RealEstate\Models\Listing;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class ValuationService
{
    protected AIService $ai;

    public function __construct(AIService $ai)
    {
        $this->ai = $ai;
    }

    /**
     * Generate property valuation
     */
    public function valuate(Listing $listing): array
    {
        // Get comparable properties
        $comparables = $this->getComparables($listing);
        
        // Calculate base valuation from comparables
        $baseValuation = $this->calculateBaseValuation($listing, $comparables);
        
        // Get market trends
        $trends = $this->getMarketTrends($listing->city, $listing->district);
        
        // Calculate ROI metrics
        $roi = $this->calculateROI($listing, $baseValuation);
        
        // Get AI analysis
        $aiAnalysis = $this->getAIAnalysis($listing, $comparables, $trends);
        
        return [
            'estimated_value' => $baseValuation['estimated_value'],
            'price_range' => [
                'min' => $baseValuation['min_value'],
                'max' => $baseValuation['max_value'],
            ],
            'confidence_score' => $baseValuation['confidence'],
            'price_per_sqm' => $baseValuation['price_per_sqm'],
            'comparables' => $comparables->map(fn($c) => [
                'id' => $c->id,
                'title' => $c->title,
                'price' => $c->price,
                'price_per_sqm' => $c->price_per_sqm,
                'gross_sqm' => $c->gross_sqm,
                'distance_km' => $c->distance ?? null,
                'sold_at' => $c->sold_at,
            ]),
            'market_trends' => $trends,
            'roi_analysis' => $roi,
            'ai_analysis' => $aiAnalysis,
            'sale_probability' => $this->calculateSaleProbability($listing, $baseValuation),
            'estimated_sale_time' => $this->estimateSaleTime($listing, $baseValuation),
            'price_recommendations' => $this->getPriceRecommendations($listing, $baseValuation),
            'quality_factors' => $this->analyzeQualityFactors($listing),
            'generated_at' => now()->toISOString(),
        ];
    }

    /**
     * Get comparable properties
     */
    protected function getComparables(Listing $listing)
    {
        $config = config('reos.valuation');
        
        $query = Listing::where('id', '!=', $listing->id)
            ->where('city', $listing->city)
            ->where('type', $listing->type)
            ->where('listing_type', $listing->listing_type)
            ->where('status', 'sold')
            ->where('sold_at', '>=', now()->subMonths($config['comparable_time_months']));

        // Filter by size range (±30%)
        if ($listing->gross_sqm) {
            $minSize = $listing->gross_sqm * 0.7;
            $maxSize = $listing->gross_sqm * 1.3;
            $query->whereBetween('gross_sqm', [$minSize, $maxSize]);
        }

        // Filter by room count (±1)
        if ($listing->room_count) {
            $query->whereBetween('room_count', [
                max(1, $listing->room_count - 1),
                $listing->room_count + 1
            ]);
        }

        // Prefer same district
        $query->orderByRaw("CASE WHEN district = ? THEN 0 ELSE 1 END", [$listing->district]);
        
        // Order by recency
        $query->orderBy('sold_at', 'desc');

        return $query->take($config['max_comparables'])->get();
    }

    /**
     * Calculate base valuation from comparables
     */
    protected function calculateBaseValuation(Listing $listing, $comparables): array
    {
        if ($comparables->isEmpty()) {
            return [
                'estimated_value' => $listing->price,
                'min_value' => $listing->price * 0.9,
                'max_value' => $listing->price * 1.1,
                'price_per_sqm' => $listing->price_per_sqm,
                'confidence' => 0.3,
            ];
        }

        $factors = config('reos.valuation.factors');
        $margin = config('reos.valuation.price_range_margin');

        // Calculate weighted average price per sqm
        $totalWeight = 0;
        $weightedSum = 0;

        foreach ($comparables as $comp) {
            $weight = 1;
            
            // Weight by recency
            $monthsAgo = $comp->sold_at->diffInMonths(now());
            $weight *= (1 - ($monthsAgo / 12) * 0.3);
            
            // Weight by size similarity
            if ($listing->gross_sqm && $comp->gross_sqm) {
                $sizeDiff = abs($listing->gross_sqm - $comp->gross_sqm) / $listing->gross_sqm;
                $weight *= (1 - $sizeDiff * 0.5);
            }
            
            // Weight by location (same district = higher weight)
            if ($comp->district === $listing->district) {
                $weight *= 1.2;
            }

            $weightedSum += $comp->price_per_sqm * $weight;
            $totalWeight += $weight;
        }

        $avgPricePerSqm = $weightedSum / $totalWeight;
        $estimatedValue = $avgPricePerSqm * ($listing->gross_sqm ?? $listing->net_sqm ?? 100);

        // Apply adjustments based on property features
        $adjustment = 1;
        
        // Age adjustment
        if ($listing->building_age !== null) {
            if ($listing->building_age <= 2) $adjustment *= 1.05;
            elseif ($listing->building_age <= 5) $adjustment *= 1.02;
            elseif ($listing->building_age >= 20) $adjustment *= 0.95;
        }

        // Floor adjustment
        if ($listing->floor_number !== null && $listing->total_floors !== null) {
            if ($listing->floor_number >= $listing->total_floors - 1) $adjustment *= 1.03;
            elseif ($listing->floor_number <= 1) $adjustment *= 0.98;
        }

        // Furnished adjustment
        if ($listing->is_furnished) {
            $adjustment *= 1.02;
        }

        $estimatedValue *= $adjustment;

        // Calculate confidence based on number of comparables
        $confidence = min(0.95, 0.5 + ($comparables->count() / 20));

        return [
            'estimated_value' => round($estimatedValue, -3),
            'min_value' => round($estimatedValue * (1 - $margin), -3),
            'max_value' => round($estimatedValue * (1 + $margin), -3),
            'price_per_sqm' => round($avgPricePerSqm, 2),
            'confidence' => round($confidence, 2),
        ];
    }

    /**
     * Get market trends for location
     */
    protected function getMarketTrends(string $city, ?string $district): array
    {
        $cacheKey = "market_trends_{$city}_{$district}";
        
        return Cache::remember($cacheKey, 3600, function () use ($city, $district) {
            $query = Listing::where('city', $city)
                ->where('status', 'sold')
                ->where('sold_at', '>=', now()->subYear());

            if ($district) {
                $query->where('district', $district);
            }

            // Monthly average prices
            $monthlyData = $query->selectRaw('
                DATE_FORMAT(sold_at, "%Y-%m") as month,
                AVG(price_per_sqm) as avg_price_per_sqm,
                COUNT(*) as transaction_count
            ')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

            // Calculate trend
            $prices = $monthlyData->pluck('avg_price_per_sqm')->toArray();
            $trend = $this->calculateTrend($prices);

            return [
                'monthly_data' => $monthlyData,
                'trend_direction' => $trend > 0 ? 'up' : ($trend < 0 ? 'down' : 'stable'),
                'trend_percentage' => round($trend * 100, 2),
                'avg_days_on_market' => $this->getAvgDaysOnMarket($city, $district),
                'supply_demand_ratio' => $this->getSupplyDemandRatio($city, $district),
            ];
        });
    }

    /**
     * Calculate ROI metrics
     */
    protected function calculateROI(Listing $listing, array $valuation): array
    {
        $estimatedValue = $valuation['estimated_value'];
        
        // Estimate rental income
        $monthlyRent = $this->estimateMonthlyRent($listing);
        $annualRent = $monthlyRent * 12;
        
        // Calculate gross yield
        $grossYield = ($annualRent / $estimatedValue) * 100;
        
        // Estimate expenses (roughly 20% of rental income)
        $annualExpenses = $annualRent * 0.2;
        $netRent = $annualRent - $annualExpenses;
        
        // Calculate net yield
        $netYield = ($netRent / $estimatedValue) * 100;
        
        // Calculate payback period
        $paybackYears = $estimatedValue / $netRent;
        
        // 5-year projection
        $appreciationRate = 0.05; // 5% annual appreciation
        $projections = [];
        $currentValue = $estimatedValue;
        
        for ($year = 1; $year <= 5; $year++) {
            $currentValue *= (1 + $appreciationRate);
            $projections[] = [
                'year' => $year,
                'property_value' => round($currentValue, -3),
                'cumulative_rent' => round($netRent * $year, -3),
                'total_return' => round(($currentValue - $estimatedValue) + ($netRent * $year), -3),
            ];
        }

        return [
            'estimated_monthly_rent' => round($monthlyRent, -2),
            'gross_yield' => round($grossYield, 2),
            'net_yield' => round($netYield, 2),
            'payback_years' => round($paybackYears, 1),
            'five_year_projection' => $projections,
            'rent_vs_buy_analysis' => $this->rentVsBuyAnalysis($listing, $valuation, $monthlyRent),
        ];
    }

    /**
     * Get AI analysis
     */
    protected function getAIAnalysis(Listing $listing, $comparables, array $trends): array
    {
        $prompt = $this->buildValuationPrompt($listing, $comparables, $trends);
        
        $messages = [
            [
                'role' => 'system',
                'content' => 'You are a real estate valuation expert. Analyze the property and provide insights in Turkish. Return JSON with: summary (string), strengths (array), weaknesses (array), recommendations (array), market_position (string: premium/average/budget).'
            ],
            [
                'role' => 'user',
                'content' => $prompt
            ]
        ];

        return $this->ai->chatJson($messages) ?? [
            'summary' => 'Değerleme analizi yapılamadı.',
            'strengths' => [],
            'weaknesses' => [],
            'recommendations' => [],
            'market_position' => 'average'
        ];
    }

    /**
     * Calculate sale probability
     */
    protected function calculateSaleProbability(Listing $listing, array $valuation): array
    {
        $currentPrice = $listing->price;
        $estimatedValue = $valuation['estimated_value'];
        
        $priceDiff = ($currentPrice - $estimatedValue) / $estimatedValue;
        
        // Base probability
        $probability = 50;
        
        // Adjust based on price difference
        if ($priceDiff <= -0.1) $probability += 30; // 10%+ below market
        elseif ($priceDiff <= 0) $probability += 15; // At or below market
        elseif ($priceDiff <= 0.1) $probability -= 10; // Up to 10% above
        elseif ($priceDiff <= 0.2) $probability -= 25; // 10-20% above
        else $probability -= 40; // 20%+ above

        // Adjust based on quality score
        $probability += ($listing->quality_score - 50) / 5;

        $probability = max(5, min(95, $probability));

        return [
            'probability' => round($probability),
            'price_position' => $priceDiff > 0.05 ? 'above_market' : ($priceDiff < -0.05 ? 'below_market' : 'at_market'),
            'price_difference_percent' => round($priceDiff * 100, 1),
        ];
    }

    /**
     * Estimate sale time
     */
    protected function estimateSaleTime(Listing $listing, array $valuation): array
    {
        $avgDays = $this->getAvgDaysOnMarket($listing->city, $listing->district);
        
        $priceDiff = ($listing->price - $valuation['estimated_value']) / $valuation['estimated_value'];
        
        // Adjust based on price
        $multiplier = 1 + ($priceDiff * 2);
        
        // Adjust based on quality
        $multiplier *= (1 - ($listing->quality_score - 50) / 200);
        
        $estimatedDays = round($avgDays * $multiplier);

        return [
            'estimated_days' => max(7, $estimatedDays),
            'range' => [
                'min' => max(7, round($estimatedDays * 0.7)),
                'max' => round($estimatedDays * 1.5),
            ],
            'market_average_days' => $avgDays,
        ];
    }

    /**
     * Get price recommendations
     */
    protected function getPriceRecommendations(Listing $listing, array $valuation): array
    {
        $estimated = $valuation['estimated_value'];
        
        return [
            'quick_sale' => [
                'price' => round($estimated * 0.92, -3),
                'description' => 'Hızlı satış için önerilen fiyat (piyasanın %8 altı)',
                'estimated_days' => 14,
            ],
            'optimal' => [
                'price' => round($estimated * 0.98, -3),
                'description' => 'Optimal başlangıç fiyatı (piyasa değerine yakın)',
                'estimated_days' => 30,
            ],
            'premium' => [
                'price' => round($estimated * 1.05, -3),
                'description' => 'Premium fiyat (pazarlık payı ile)',
                'estimated_days' => 60,
            ],
            'price_reduction_suggestion' => $listing->price > $estimated * 1.1 ? [
                'current_price' => $listing->price,
                'suggested_price' => round($estimated * 1.02, -3),
                'reduction_amount' => $listing->price - round($estimated * 1.02, -3),
                'reason' => 'Mevcut fiyat piyasa değerinin %10\'undan fazla üzerinde',
            ] : null,
        ];
    }

    /**
     * Analyze quality factors
     */
    protected function analyzeQualityFactors(Listing $listing): array
    {
        $factors = [];
        
        // Photos
        $photoCount = $listing->getMedia('photos')->count();
        $factors['photos'] = [
            'score' => min(100, $photoCount * 5),
            'count' => $photoCount,
            'recommendation' => $photoCount < 10 ? 'Daha fazla fotoğraf ekleyin (en az 10 önerilir)' : null,
        ];
        
        // Description
        $descLength = strlen($listing->description ?? '');
        $factors['description'] = [
            'score' => min(100, $descLength / 5),
            'length' => $descLength,
            'recommendation' => $descLength < 300 ? 'Daha detaylı açıklama yazın (en az 300 karakter önerilir)' : null,
        ];
        
        // Location data
        $hasCoordinates = $listing->latitude && $listing->longitude;
        $factors['location'] = [
            'score' => $hasCoordinates ? 100 : 50,
            'has_coordinates' => $hasCoordinates,
            'recommendation' => !$hasCoordinates ? 'Harita konumu ekleyin' : null,
        ];
        
        // Floor plan
        $hasFloorPlan = $listing->getMedia('floor_plans')->count() > 0;
        $factors['floor_plan'] = [
            'score' => $hasFloorPlan ? 100 : 0,
            'has_floor_plan' => $hasFloorPlan,
            'recommendation' => !$hasFloorPlan ? 'Kat planı ekleyin' : null,
        ];
        
        // Virtual tour
        $hasVirtualTour = $listing->getMedia('virtual_tour')->count() > 0;
        $factors['virtual_tour'] = [
            'score' => $hasVirtualTour ? 100 : 0,
            'has_virtual_tour' => $hasVirtualTour,
            'recommendation' => !$hasVirtualTour ? '360° sanal tur ekleyin' : null,
        ];

        return $factors;
    }

    // Helper methods
    protected function calculateTrend(array $values): float
    {
        if (count($values) < 2) return 0;
        
        $n = count($values);
        $sumX = array_sum(range(0, $n - 1));
        $sumY = array_sum($values);
        $sumXY = 0;
        $sumX2 = 0;
        
        for ($i = 0; $i < $n; $i++) {
            $sumXY += $i * $values[$i];
            $sumX2 += $i * $i;
        }
        
        $slope = ($n * $sumXY - $sumX * $sumY) / ($n * $sumX2 - $sumX * $sumX);
        $avgY = $sumY / $n;
        
        return $avgY > 0 ? $slope / $avgY : 0;
    }

    protected function getAvgDaysOnMarket(string $city, ?string $district): int
    {
        return 45; // Placeholder - would calculate from actual data
    }

    protected function getSupplyDemandRatio(string $city, ?string $district): float
    {
        return 1.2; // Placeholder
    }

    protected function estimateMonthlyRent(Listing $listing): float
    {
        // Estimate based on price (roughly 0.4% of property value per month in Turkey)
        return $listing->price * 0.004;
    }

    protected function rentVsBuyAnalysis(Listing $listing, array $valuation, float $monthlyRent): array
    {
        $purchasePrice = $valuation['estimated_value'];
        $downPayment = $purchasePrice * 0.2;
        $loanAmount = $purchasePrice - $downPayment;
        $interestRate = 0.02; // Monthly rate
        $loanTermMonths = 120;
        
        // Calculate monthly mortgage payment
        $monthlyPayment = $loanAmount * ($interestRate * pow(1 + $interestRate, $loanTermMonths)) / 
                         (pow(1 + $interestRate, $loanTermMonths) - 1);

        return [
            'monthly_rent' => round($monthlyRent, -2),
            'monthly_mortgage' => round($monthlyPayment, -2),
            'down_payment_required' => round($downPayment, -3),
            'break_even_years' => round($downPayment / (($monthlyPayment - $monthlyRent) * 12), 1),
            'recommendation' => $monthlyPayment < $monthlyRent * 1.3 ? 'buy' : 'rent',
        ];
    }

    protected function buildValuationPrompt(Listing $listing, $comparables, array $trends): string
    {
        return "Mülk Bilgileri:
- Tip: {$listing->type}
- Konum: {$listing->district}, {$listing->city}
- Brüt m²: {$listing->gross_sqm}
- Oda: {$listing->room_info}
- Bina Yaşı: {$listing->building_age}
- Kat: {$listing->floor_number}/{$listing->total_floors}
- Fiyat: {$listing->formatted_price}

Emsal Sayısı: {$comparables->count()}
Piyasa Trendi: {$trends['trend_direction']} ({$trends['trend_percentage']}%)

Bu mülkün güçlü ve zayıf yönlerini, piyasadaki konumunu ve önerilerinizi analiz edin.";
    }
}
