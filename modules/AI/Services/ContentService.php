<?php

namespace Modules\AI\Services;

use Modules\RealEstate\Models\Listing;

class ContentService
{
    protected AIService $ai;

    public function __construct(AIService $ai)
    {
        $this->ai = $ai;
    }

    /**
     * Generate listing description
     */
    public function generateDescription(Listing $listing, string $style = 'professional'): string
    {
        $styles = [
            'professional' => 'Write a professional, detailed real estate listing description.',
            'luxury' => 'Write a luxurious, elegant description emphasizing premium features.',
            'friendly' => 'Write a warm, friendly description that feels welcoming.',
            'minimal' => 'Write a concise, to-the-point description with key facts.',
        ];

        $prompt = $this->buildListingPrompt($listing);

        $messages = [
            [
                'role' => 'system',
                'content' => "{$styles[$style]} Write in Turkish. Focus on unique selling points. Include neighborhood benefits. Be engaging but factual. Length: 200-400 words."
            ],
            [
                'role' => 'user',
                'content' => $prompt
            ]
        ];

        return $this->ai->chat($messages) ?? '';
    }

    /**
     * Generate multiple description variations
     */
    public function generateDescriptionVariations(Listing $listing, int $count = 3): array
    {
        $styles = ['professional', 'luxury', 'friendly'];
        $variations = [];

        foreach (array_slice($styles, 0, $count) as $style) {
            $variations[$style] = $this->generateDescription($listing, $style);
        }

        return $variations;
    }

    /**
     * Generate social media content
     */
    public function generateSocialContent(Listing $listing): array
    {
        $prompt = $this->buildListingPrompt($listing);

        $messages = [
            [
                'role' => 'system',
                'content' => 'Generate social media content for a real estate listing in Turkish. Return JSON with: instagram_caption (with emojis, hashtags), facebook_post, twitter_post (max 280 chars), linkedin_post (professional tone), story_text (short, catchy for Instagram/Facebook stories).'
            ],
            [
                'role' => 'user',
                'content' => $prompt
            ]
        ];

        return $this->ai->chatJson($messages) ?? [
            'instagram_caption' => '',
            'facebook_post' => '',
            'twitter_post' => '',
            'linkedin_post' => '',
            'story_text' => '',
        ];
    }

    /**
     * Generate Meta ad copy variations
     */
    public function generateAdCopy(Listing $listing, int $variations = 5): array
    {
        $prompt = $this->buildListingPrompt($listing);

        $messages = [
            [
                'role' => 'system',
                'content' => "Generate {$variations} Facebook/Instagram ad copy variations in Turkish. Return JSON array with objects containing: headline (max 40 chars), primary_text (max 125 chars), description (max 30 chars), cta (call to action text)."
            ],
            [
                'role' => 'user',
                'content' => $prompt
            ]
        ];

        return $this->ai->chatJson($messages) ?? [];
    }

    /**
     * Generate Reels/TikTok script
     */
    public function generateReelsScript(Listing $listing): array
    {
        $prompt = $this->buildListingPrompt($listing);

        $messages = [
            [
                'role' => 'system',
                'content' => 'Generate a short video script for Instagram Reels/TikTok in Turkish. Return JSON with: hook (attention-grabbing first 3 seconds), scenes (array of scene descriptions with timing), voiceover_script, music_suggestion, hashtags (array), cta (call to action).'
            ],
            [
                'role' => 'user',
                'content' => $prompt
            ]
        ];

        return $this->ai->chatJson($messages) ?? [
            'hook' => '',
            'scenes' => [],
            'voiceover_script' => '',
            'music_suggestion' => '',
            'hashtags' => [],
            'cta' => '',
        ];
    }

    /**
     * Generate headline variations
     */
    public function generateHeadlines(Listing $listing, int $count = 10): array
    {
        $prompt = $this->buildListingPrompt($listing);

        $messages = [
            [
                'role' => 'system',
                'content' => "Generate {$count} catchy, unique headline variations for this real estate listing in Turkish. Return JSON array of strings. Mix styles: some with emojis, some professional, some with numbers, some question-based."
            ],
            [
                'role' => 'user',
                'content' => $prompt
            ]
        ];

        return $this->ai->chatJson($messages) ?? [];
    }

    /**
     * Generate SEO content
     */
    public function generateSEOContent(Listing $listing): array
    {
        $prompt = $this->buildListingPrompt($listing);

        $messages = [
            [
                'role' => 'system',
                'content' => 'Generate SEO-optimized content for a real estate listing in Turkish. Return JSON with: meta_title (max 60 chars), meta_description (max 160 chars), keywords (array of 10 keywords), h1_title, h2_sections (array of section titles), alt_texts (array of image alt text suggestions).'
            ],
            [
                'role' => 'user',
                'content' => $prompt
            ]
        ];

        return $this->ai->chatJson($messages) ?? [
            'meta_title' => $listing->title,
            'meta_description' => '',
            'keywords' => [],
            'h1_title' => $listing->title,
            'h2_sections' => [],
            'alt_texts' => [],
        ];
    }

    /**
     * Improve existing description
     */
    public function improveDescription(string $description, array $options = []): string
    {
        $instructions = [];
        
        if ($options['fix_grammar'] ?? true) {
            $instructions[] = 'Fix any grammar or spelling errors';
        }
        if ($options['add_keywords'] ?? false) {
            $instructions[] = 'Add relevant SEO keywords naturally';
        }
        if ($options['make_engaging'] ?? true) {
            $instructions[] = 'Make the text more engaging and persuasive';
        }
        if ($options['add_details'] ?? false) {
            $instructions[] = 'Expand with more descriptive details';
        }

        $messages = [
            [
                'role' => 'system',
                'content' => 'Improve the following real estate listing description in Turkish. ' . implode('. ', $instructions) . '. Keep the same general structure but enhance quality.'
            ],
            [
                'role' => 'user',
                'content' => $description
            ]
        ];

        return $this->ai->chat($messages) ?? $description;
    }

    /**
     * Generate email templates
     */
    public function generateEmailTemplates(Listing $listing): array
    {
        $prompt = $this->buildListingPrompt($listing);

        $messages = [
            [
                'role' => 'system',
                'content' => 'Generate email templates for a real estate listing in Turkish. Return JSON with: new_listing_announcement (subject, body), follow_up (subject, body), price_reduction (subject, body), open_house_invitation (subject, body). Use placeholders like {customer_name}, {agent_name}, {listing_url}.'
            ],
            [
                'role' => 'user',
                'content' => $prompt
            ]
        ];

        return $this->ai->chatJson($messages) ?? [];
    }

    /**
     * Build listing prompt
     */
    protected function buildListingPrompt(Listing $listing): string
    {
        $features = is_array($listing->features) ? implode(', ', $listing->features) : '';
        $amenities = is_array($listing->amenities) ? implode(', ', $listing->amenities) : '';

        return "Property Details:
- Type: {$listing->type} ({$listing->category})
- Transaction: {$listing->listing_type}
- Location: {$listing->neighborhood}, {$listing->district}, {$listing->city}
- Price: {$listing->formatted_price}
- Size: {$listing->gross_sqm} m² gross, {$listing->net_sqm} m² net
- Rooms: {$listing->room_info}
- Floor: {$listing->floor_number}/{$listing->total_floors}
- Building Age: {$listing->building_age} years
- Heating: {$listing->heating_type}
- Furnished: " . ($listing->is_furnished ? 'Yes' : 'No') . "
- Features: {$features}
- Amenities: {$amenities}
- In Complex: " . ($listing->is_in_site ? "Yes ({$listing->site_name})" : 'No') . "
- Monthly Dues: {$listing->dues_amount}";
    }
}
