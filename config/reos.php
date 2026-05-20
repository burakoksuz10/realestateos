<?php

return [

    /*
    |--------------------------------------------------------------------------
    | ReCRM Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains all the configuration options for the ReCRM
    | Real Estate CRM System.
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
        'model' => env('AI_MODEL', 'gpt-4o'),
        'mini_model' => env('AI_MINI_MODEL', 'gpt-4o-mini'),
        'embedding_model' => env('AI_EMBEDDING_MODEL', 'text-embedding-3-small'),
        'max_tokens' => env('AI_MAX_TOKENS', 2048),
        'temperature' => env('AI_TEMPERATURE', 0.7),
        'timeout' => env('AI_TIMEOUT', 60),
        'cache_ttl' => env('AI_CACHE_TTL', 3600),
        'openai_key' => env('OPENAI_API_KEY'),
        'openai_organization' => env('OPENAI_ORGANIZATION'),

        // Speech-to-text — Faz 4.3
        'transcription_provider' => env('AI_TRANSCRIPTION_PROVIDER', 'elevenlabs'), // elevenlabs | whisper
        'transcription_model'    => env('AI_TRANSCRIPTION_MODEL', 'whisper-1'),     // legacy alias (Whisper)
        'whisper_model'          => env('AI_WHISPER_MODEL', 'whisper-1'),
        'summary_model'          => env('AI_SUMMARY_MODEL', 'gpt-4o-mini'),

        // Pricing per 1K tokens (USD, used for cost estimation)
        'pricing' => [
            'gpt-4o' => ['input' => 0.0025, 'output' => 0.01],
            'gpt-4o-mini' => ['input' => 0.00015, 'output' => 0.0006],
            'gpt-4-turbo' => ['input' => 0.01, 'output' => 0.03],
            'gpt-4-turbo-preview' => ['input' => 0.01, 'output' => 0.03],
        ],

        // Monthly credit defaults per office (1 credit = 1 AI call)
        'credits' => [
            'default_monthly_quota' => env('AI_DEFAULT_MONTHLY_QUOTA', 500),
            'enforce' => env('AI_ENFORCE_CREDITS', true),
        ],

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

        'image' => [
            'enabled' => env('AI_IMAGE_ENABLED', true),
            'provider' => env('AI_IMAGE_PROVIDER', 'fal'),
            // Fal.ai model endpoints — değiştirilebilir
            'models' => [
                'sky_replacement' => env('FAL_MODEL_SKY', 'fal-ai/iclight-v2'),
                'twilight' => env('FAL_MODEL_TWILIGHT', 'fal-ai/iclight-v2'),
                'declutter' => env('FAL_MODEL_DECLUTTER', 'fal-ai/lama'),
                'virtual_staging' => env('FAL_MODEL_STAGING', 'fal-ai/flux-pro/v1.1'),
                'enhance' => env('FAL_MODEL_ENHANCE', 'fal-ai/clarity-upscaler'),
            ],
            'timeout' => env('FAL_TIMEOUT', 90),
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
    | Voice / Telekom — Faz 4.3 (ElevenLabs + Netgsm)
    |--------------------------------------------------------------------------
    */

    'voice' => [
        'provider' => env('VOICE_PROVIDER', 'netgsm'), // netgsm | bulutfon | twilio
        'enabled'  => env('VOICE_ENABLED', false),
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
