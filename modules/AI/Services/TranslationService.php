<?php

namespace Modules\AI\Services;

use Illuminate\Support\Facades\Cache;

class TranslationService
{
    protected AIService $ai;
    protected array $supportedLanguages;

    public function __construct(AIService $ai)
    {
        $this->ai = $ai;
        $this->supportedLanguages = config('reos.ai.content.supported_languages', ['tr', 'en', 'ar', 'ru', 'de', 'fr']);
    }

    /**
     * Translate text to target language
     */
    public function translate(string $text, string $targetLanguage, string $sourceLanguage = 'tr'): string
    {
        if ($sourceLanguage === $targetLanguage) {
            return $text;
        }

        $cacheKey = 'translation_' . md5($text . $targetLanguage . $sourceLanguage);
        
        return Cache::remember($cacheKey, 86400, function () use ($text, $targetLanguage, $sourceLanguage) {
            $languageNames = [
                'tr' => 'Turkish',
                'en' => 'English',
                'ar' => 'Arabic',
                'ru' => 'Russian',
                'de' => 'German',
                'fr' => 'French',
            ];

            $messages = [
                [
                    'role' => 'system',
                    'content' => "You are a professional real estate translator. Translate the following text from {$languageNames[$sourceLanguage]} to {$languageNames[$targetLanguage]}. Maintain the professional tone and real estate terminology. Keep formatting intact."
                ],
                [
                    'role' => 'user',
                    'content' => $text
                ]
            ];

            return $this->ai->chat($messages) ?? $text;
        });
    }

    /**
     * Translate to multiple languages
     */
    public function translateToAll(string $text, string $sourceLanguage = 'tr'): array
    {
        $translations = [$sourceLanguage => $text];

        foreach ($this->supportedLanguages as $lang) {
            if ($lang !== $sourceLanguage) {
                $translations[$lang] = $this->translate($text, $lang, $sourceLanguage);
            }
        }

        return $translations;
    }

    /**
     * Translate listing fields
     */
    public function translateListing(\Modules\RealEstate\Models\Listing $listing, string $targetLanguage): array
    {
        $sourceLanguage = 'tr';
        
        return [
            'title' => $this->translate($listing->title, $targetLanguage, $sourceLanguage),
            'description' => $this->translate($listing->description ?? '', $targetLanguage, $sourceLanguage),
            'features_text' => $this->translate($listing->features_text ?? '', $targetLanguage, $sourceLanguage),
            'location_description' => $this->translate($listing->location_description ?? '', $targetLanguage, $sourceLanguage),
        ];
    }

    /**
     * Detect language of text
     */
    public function detectLanguage(string $text): string
    {
        $messages = [
            [
                'role' => 'system',
                'content' => 'Detect the language of the following text. Return only the ISO 639-1 language code (e.g., tr, en, ar, ru, de, fr).'
            ],
            [
                'role' => 'user',
                'content' => $text
            ]
        ];

        $result = $this->ai->chat($messages);
        
        return trim(strtolower($result ?? 'tr'));
    }

    /**
     * Get supported languages
     */
    public function getSupportedLanguages(): array
    {
        return [
            'tr' => 'Türkçe',
            'en' => 'English',
            'ar' => 'العربية',
            'ru' => 'Русский',
            'de' => 'Deutsch',
            'fr' => 'Français',
        ];
    }

    /**
     * Translate with context (for better accuracy)
     */
    public function translateWithContext(string $text, string $targetLanguage, string $context = 'real_estate'): string
    {
        $contextPrompts = [
            'real_estate' => 'This is real estate content. Use appropriate property and real estate terminology.',
            'legal' => 'This is legal/contract content. Use precise legal terminology.',
            'marketing' => 'This is marketing content. Keep it engaging and persuasive.',
        ];

        $messages = [
            [
                'role' => 'system',
                'content' => "Translate to {$targetLanguage}. {$contextPrompts[$context]} Maintain professional tone."
            ],
            [
                'role' => 'user',
                'content' => $text
            ]
        ];

        return $this->ai->chat($messages) ?? $text;
    }
}
