<?php

return [

    /*
    |--------------------------------------------------------------------------
    | RE-OS Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains all the configuration options for the RE-OS
    | Real Estate Operating System.
    |
    */

    'version' => '1.0.0',

    /*
    |--------------------------------------------------------------------------
    | AI Configuration
    |--------------------------------------------------------------------------
    */

    'ai' => [
        'enabled' => env('AI_ENABLED', true),
        'provider' => env('AI_PROVIDER', 'openai'),
        'model' => env('AI_MODEL', 'gpt-4-turbo-preview'),
        'max_tokens' => env('AI_MAX_TOKENS', 4096),
        'temperature' => env('AI_TEMPERATURE', 0.7),
        
        'copilot' => [
            'enabled' => env('AI_COPILOT_ENABLED', true),
            'auto_suggest' => env('AI_COPILOT_AUTO_SUGGEST', true),
            'lead_scoring' => env('AI_LEAD_SCORING_ENABLED', true),
            'call_analysis' => env('AI_CALL_ANALYSIS_ENABLED', true),
        ],
        
        'valuation' => [
            'enabled' => env('AI_VALUATION_ENABLED', true),
            'confidence_threshold' => env('AI_VALUATION_CONFIDENCE', 0.75),
            'include_comparables' => true,
            'include_trends' => true,
            'include_roi' => true,
        ],
        
        'content' => [
            'enabled' => env('AI_CONTENT_ENABLED', true),
            'auto_translate' => env('AI_AUTO_TRANSLATE', true),
            'supported_languages' => ['tr', 'en', 'ar', 'ru', 'de', 'fr'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Lead Scoring Configuration
    |--------------------------------------------------------------------------
    */

    'lead_scoring' => [
        'enabled' => true,
        'weights' => [
            'response_time' => 20,
            'engagement' => 25,
            'budget_match' => 20,
            'location_interest' => 15,
            'behavior_signals' => 20,
        ],
        'thresholds' => [
            'hot' => 80,
            'warm' => 50,
            'cold' => 20,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Valuation Configuration
    |--------------------------------------------------------------------------
    */

    'valuation' => [
        'price_range_margin' => 0.10, // 10% margin
        'comparable_radius_km' => 2,
        'comparable_time_months' => 12,
        'min_comparables' => 3,
        'max_comparables' => 10,
        'factors' => [
            'location' => 0.30,
            'size' => 0.25,
            'age' => 0.15,
            'condition' => 0.15,
            'amenities' => 0.15,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | MLS / Sharing Pool Configuration
    |--------------------------------------------------------------------------
    */

    'mls' => [
        'enabled' => env('MLS_ENABLED', true),
        'commission_split' => [
            'exclusive' => [
                'listing_agent' => 60,
                'selling_agent' => 40,
            ],
            'open' => [
                'listing_agent' => 50,
                'selling_agent' => 50,
            ],
        ],
        'auto_share' => env('MLS_AUTO_SHARE', false),
        'approval_required' => env('MLS_APPROVAL_REQUIRED', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Portal Integration Configuration
    |--------------------------------------------------------------------------
    */

    'portals' => [
        'sahibinden' => [
            'enabled' => env('PORTAL_SAHIBINDEN_ENABLED', false),
            'auto_sync' => env('PORTAL_SAHIBINDEN_AUTO_SYNC', false),
            'sync_interval' => 60, // minutes
        ],
        'hepsiemlak' => [
            'enabled' => env('PORTAL_HEPSIEMLAK_ENABLED', false),
            'auto_sync' => env('PORTAL_HEPSIEMLAK_AUTO_SYNC', false),
            'sync_interval' => 60,
        ],
        'emlakjet' => [
            'enabled' => env('PORTAL_EMLAKJET_ENABLED', false),
            'auto_sync' => env('PORTAL_EMLAKJET_AUTO_SYNC', false),
            'sync_interval' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Marketing Automation Configuration
    |--------------------------------------------------------------------------
    */

    'marketing' => [
        'meta_ads' => [
            'enabled' => env('META_ADS_ENABLED', false),
            'auto_create' => false,
            'default_budget' => 50,
            'default_duration' => 7,
        ],
        'google_ads' => [
            'enabled' => env('GOOGLE_ADS_ENABLED', false),
            'auto_create' => false,
        ],
        'email' => [
            'enabled' => true,
            'weekly_digest' => true,
            'instant_alerts' => true,
        ],
        'sms' => [
            'enabled' => env('SMS_ENABLED', false),
            'provider' => env('SMS_PROVIDER', 'netgsm'),
        ],
        'whatsapp' => [
            'enabled' => env('WHATSAPP_ENABLED', false),
            'auto_reply' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Document & Compliance Configuration
    |--------------------------------------------------------------------------
    */

    'documents' => [
        'e_signature' => [
            'enabled' => env('ESIGN_ENABLED', false),
            'provider' => env('ESIGN_PROVIDER', 'onaylarim'),
        ],
        'kvkk' => [
            'enabled' => true,
            'consent_required' => true,
            'retention_days' => 365 * 3, // 3 years
        ],
        'audit_trail' => [
            'enabled' => true,
            'retention_days' => 365 * 5, // 5 years
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Franchise / Office Configuration
    |--------------------------------------------------------------------------
    */

    'franchise' => [
        'enabled' => env('FRANCHISE_MODE', false),
        'royalty_percentage' => 5,
        'billing_cycle' => 'monthly',
        'hierarchy' => [
            'levels' => ['headquarters', 'region', 'office', 'team', 'agent'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Website Builder Configuration
    |--------------------------------------------------------------------------
    */

    'website' => [
        'enabled' => true,
        'multi_language' => true,
        'default_theme' => 'modern',
        'themes' => ['modern', 'classic', 'minimal', 'luxury'],
        'tracking' => [
            'enabled' => true,
            'heatmap' => true,
            'favorites' => true,
            'comparisons' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Listing Configuration
    |--------------------------------------------------------------------------
    */

    'listings' => [
        'types' => [
            'residential' => [
                'apartment',
                'villa',
                'detached_house',
                'townhouse',
                'penthouse',
                'duplex',
                'triplex',
                'studio',
                'loft',
            ],
            'commercial' => [
                'office',
                'retail',
                'warehouse',
                'factory',
                'hotel',
                'restaurant',
                'showroom',
            ],
            'land' => [
                'residential_land',
                'commercial_land',
                'agricultural_land',
                'industrial_land',
            ],
            'project' => [
                'new_development',
                'off_plan',
                'renovation',
            ],
        ],
        'statuses' => [
            'draft',
            'active',
            'pending',
            'sold',
            'rented',
            'withdrawn',
            'expired',
        ],
        'media' => [
            'max_photos' => 50,
            'max_videos' => 5,
            'max_documents' => 10,
            'watermark' => true,
            'exif_cleanup' => true,
        ],
    ],

];
