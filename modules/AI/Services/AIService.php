<?php

namespace Modules\AI\Services;

use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AIService
{
    protected string $model;
    protected int $maxTokens;
    protected float $temperature;

    public function __construct()
    {
        $this->model = config('reos.ai.model', 'gpt-4-turbo-preview');
        $this->maxTokens = config('reos.ai.max_tokens', 4096);
        $this->temperature = config('reos.ai.temperature', 0.7);
    }

    /**
     * Send a chat completion request
     */
    public function chat(array $messages, array $options = []): ?string
    {
        try {
            $response = OpenAI::chat()->create([
                'model' => $options['model'] ?? $this->model,
                'messages' => $messages,
                'max_tokens' => $options['max_tokens'] ?? $this->maxTokens,
                'temperature' => $options['temperature'] ?? $this->temperature,
            ]);

            return $response->choices[0]->message->content;
        } catch (\Exception $e) {
            Log::error('AI Service Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Send a chat completion request with JSON response
     */
    public function chatJson(array $messages, array $options = []): ?array
    {
        try {
            $response = OpenAI::chat()->create([
                'model' => $options['model'] ?? $this->model,
                'messages' => $messages,
                'max_tokens' => $options['max_tokens'] ?? $this->maxTokens,
                'temperature' => $options['temperature'] ?? $this->temperature,
                'response_format' => ['type' => 'json_object'],
            ]);

            $content = $response->choices[0]->message->content;
            return json_decode($content, true);
        } catch (\Exception $e) {
            Log::error('AI Service Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate embeddings for text
     */
    public function embeddings(string $text): ?array
    {
        try {
            $response = OpenAI::embeddings()->create([
                'model' => 'text-embedding-3-small',
                'input' => $text,
            ]);

            return $response->embeddings[0]->embedding;
        } catch (\Exception $e) {
            Log::error('AI Embeddings Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Calculate cosine similarity between two vectors
     */
    public function cosineSimilarity(array $a, array $b): float
    {
        $dotProduct = 0;
        $normA = 0;
        $normB = 0;

        for ($i = 0; $i < count($a); $i++) {
            $dotProduct += $a[$i] * $b[$i];
            $normA += $a[$i] * $a[$i];
            $normB += $b[$i] * $b[$i];
        }

        return $dotProduct / (sqrt($normA) * sqrt($normB));
    }

    /**
     * Analyze sentiment of text
     */
    public function analyzeSentiment(string $text): array
    {
        $messages = [
            [
                'role' => 'system',
                'content' => 'Analyze the sentiment of the following text. Return JSON with: sentiment (positive/negative/neutral), score (-1 to 1), and key_phrases (array of important phrases).'
            ],
            [
                'role' => 'user',
                'content' => $text
            ]
        ];

        return $this->chatJson($messages) ?? [
            'sentiment' => 'neutral',
            'score' => 0,
            'key_phrases' => []
        ];
    }

    /**
     * Extract intent from text
     */
    public function extractIntent(string $text): array
    {
        $messages = [
            [
                'role' => 'system',
                'content' => 'Analyze the following real estate inquiry and extract: intent (buy/rent/sell/invest/info), urgency (immediate/soon/exploring), budget_mentioned (boolean), location_mentioned (boolean), property_type_mentioned (boolean). Return as JSON.'
            ],
            [
                'role' => 'user',
                'content' => $text
            ]
        ];

        return $this->chatJson($messages) ?? [
            'intent' => 'info',
            'urgency' => 'exploring',
            'budget_mentioned' => false,
            'location_mentioned' => false,
            'property_type_mentioned' => false
        ];
    }
}
