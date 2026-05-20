<?php

namespace Modules\AI\Services;

use OpenAI\Client as OpenAIClient;
use OpenAI\Exceptions\ErrorException as OpenAIErrorException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\AiUsageLog;
use App\Models\AiSetting;
use App\Services\AI\AiCreditService;

class AIService
{
    protected string $model;
    protected string $miniModel;
    protected string $embeddingModel;
    protected int $maxTokens;
    protected float $temperature;
    protected int $cacheTtl;

    /**
     * Office context for usage tracking / per-office API key override.
     * Set via withOffice() per call when available.
     */
    protected ?int $officeId = null;
    protected ?int $userId = null;
    protected ?string $feature = null;

    public function __construct(
        protected AiCreditService $credits,
    ) {
        $this->model          = config('reos.ai.model', 'gpt-4o');
        $this->miniModel      = config('reos.ai.mini_model', 'gpt-4o-mini');
        $this->embeddingModel = config('reos.ai.embedding_model', 'text-embedding-3-small');
        $this->maxTokens      = (int) config('reos.ai.max_tokens', 2048);
        $this->temperature    = (float) config('reos.ai.temperature', 0.7);
        $this->cacheTtl       = (int) config('reos.ai.cache_ttl', 3600);
    }

    /**
     * Fluent context setter — call before chat/chatJson to attribute usage.
     */
    public function withContext(?int $officeId = null, ?int $userId = null, ?string $feature = null): self
    {
        $this->officeId = $officeId;
        $this->userId = $userId;
        $this->feature = $feature;
        return $this;
    }

    /**
     * Resolve the OpenAI client, preferring office-level overrides if present.
     */
    protected function client(): OpenAIClient
    {
        if ($this->officeId) {
            $override = AiSetting::keyFor($this->officeId);
            if ($override) {
                return \OpenAI::client($override);
            }
        }

        return app(OpenAIClient::class);
    }

    /**
     * Check whether OpenAI is actually configured. When false, return early.
     */
    public function isConfigured(): bool
    {
        if ($this->officeId && AiSetting::keyFor($this->officeId)) {
            return true;
        }
        $env = config('reos.ai.openai_key');
        return !empty($env) && $env !== 'sk-placeholder-not-configured';
    }

    /**
     * Send a chat completion request.
     */
    public function chat(array $messages, array $options = []): ?string
    {
        if (!$this->isConfigured()) {
            Log::warning('AI chat skipped: OpenAI not configured', ['feature' => $this->feature]);
            return null;
        }

        if (!$this->canConsume()) {
            Log::warning('AI chat blocked: office credits exhausted', ['office_id' => $this->officeId]);
            return null;
        }

        $model = $options['model'] ?? $this->model;
        $payload = [
            'model'       => $model,
            'messages'    => $messages,
            'max_tokens'  => $options['max_tokens'] ?? $this->maxTokens,
            'temperature' => $options['temperature'] ?? $this->temperature,
        ];

        $cacheKey = $this->cacheKey($payload);
        if (($options['cache'] ?? false) && ($cached = Cache::get($cacheKey)) !== null) {
            return $cached;
        }

        try {
            $start = microtime(true);
            $response = $this->client()->chat()->create($payload);
            $latencyMs = (int) ((microtime(true) - $start) * 1000);

            $content = $response->choices[0]->message->content ?? null;
            $usage = $response->usage ?? null;

            $this->logUsage('chat', $model, $usage, $latencyMs, 'success');
            $this->credits->consume($this->officeId, 1);

            if (($options['cache'] ?? false) && $content) {
                Cache::put($cacheKey, $content, $this->cacheTtl);
            }

            return $content;
        } catch (OpenAIErrorException $e) {
            $this->logUsage('chat', $model, null, 0, 'error', $e->getMessage());
            Log::error('OpenAI API error', ['feature' => $this->feature, 'message' => $e->getMessage()]);
            return null;
        } catch (\Throwable $e) {
            $this->logUsage('chat', $model, null, 0, 'error', $e->getMessage());
            Log::error('AI Service error', ['feature' => $this->feature, 'message' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Send a chat completion request and parse JSON response.
     */
    public function chatJson(array $messages, array $options = []): ?array
    {
        if (!$this->isConfigured()) {
            Log::warning('AI chatJson skipped: OpenAI not configured', ['feature' => $this->feature]);
            return null;
        }

        if (!$this->canConsume()) {
            Log::warning('AI chatJson blocked: office credits exhausted', ['office_id' => $this->officeId]);
            return null;
        }

        $model = $options['model'] ?? $this->model;
        $payload = [
            'model'          => $model,
            'messages'       => $messages,
            'max_tokens'     => $options['max_tokens'] ?? $this->maxTokens,
            'temperature'    => $options['temperature'] ?? $this->temperature,
            'response_format' => ['type' => 'json_object'],
        ];

        try {
            $start = microtime(true);
            $response = $this->client()->chat()->create($payload);
            $latencyMs = (int) ((microtime(true) - $start) * 1000);

            $content = $response->choices[0]->message->content ?? null;
            $usage = $response->usage ?? null;

            $this->logUsage('chat_json', $model, $usage, $latencyMs, 'success');
            $this->credits->consume($this->officeId, 1);

            return $content ? json_decode($content, true) : null;
        } catch (OpenAIErrorException $e) {
            $this->logUsage('chat_json', $model, null, 0, 'error', $e->getMessage());
            Log::error('OpenAI JSON API error', ['feature' => $this->feature, 'message' => $e->getMessage()]);
            return null;
        } catch (\Throwable $e) {
            $this->logUsage('chat_json', $model, null, 0, 'error', $e->getMessage());
            Log::error('AI JSON Service error', ['feature' => $this->feature, 'message' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Generate embeddings for text.
     */
    public function embeddings(string $text): ?array
    {
        if (!$this->isConfigured() || !$this->canConsume()) {
            return null;
        }

        try {
            $start = microtime(true);
            $response = $this->client()->embeddings()->create([
                'model' => $this->embeddingModel,
                'input' => $text,
            ]);
            $latencyMs = (int) ((microtime(true) - $start) * 1000);

            $this->logUsage('embedding', $this->embeddingModel, $response->usage ?? null, $latencyMs, 'success');
            $this->credits->consume($this->officeId, 1);

            return $response->embeddings[0]->embedding ?? null;
        } catch (\Throwable $e) {
            $this->logUsage('embedding', $this->embeddingModel, null, 0, 'error', $e->getMessage());
            Log::error('AI Embeddings Error', ['message' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Cosine similarity between two embedding vectors.
     */
    public function cosineSimilarity(array $a, array $b): float
    {
        $dot = 0; $na = 0; $nb = 0;
        $len = min(count($a), count($b));
        for ($i = 0; $i < $len; $i++) {
            $dot += $a[$i] * $b[$i];
            $na  += $a[$i] * $a[$i];
            $nb  += $b[$i] * $b[$i];
        }
        if ($na == 0 || $nb == 0) return 0.0;
        return $dot / (sqrt($na) * sqrt($nb));
    }

    /**
     * Quick sentiment analysis (Turkish-friendly).
     */
    public function analyzeSentiment(string $text): array
    {
        $messages = [
            ['role' => 'system', 'content' => 'Verilen metnin duygu analizini yap. JSON döndür: {"sentiment":"positive|negative|neutral","score":-1..1,"key_phrases":[...]}'],
            ['role' => 'user',   'content' => $text],
        ];

        return $this->chatJson($messages, ['model' => $this->miniModel]) ?? [
            'sentiment'   => 'neutral',
            'score'       => 0,
            'key_phrases' => [],
        ];
    }

    /**
     * Extract real-estate intent from a text snippet.
     */
    public function extractIntent(string $text): array
    {
        $messages = [
            ['role' => 'system', 'content' => 'Bir emlak sorgusunu analiz et: intent (buy/rent/sell/invest/info), urgency (immediate/soon/exploring), budget_mentioned, location_mentioned, property_type_mentioned. JSON döndür.'],
            ['role' => 'user',   'content' => $text],
        ];

        return $this->chatJson($messages, ['model' => $this->miniModel]) ?? [
            'intent' => 'info',
            'urgency' => 'exploring',
            'budget_mentioned' => false,
            'location_mentioned' => false,
            'property_type_mentioned' => false,
        ];
    }

    /**
     * Whether the current office (if any) has credits left.
     */
    protected function canConsume(): bool
    {
        if (!config('reos.ai.credits.enforce', true)) {
            return true;
        }
        return $this->credits->hasAvailable($this->officeId, 1);
    }

    protected function cacheKey(array $payload): string
    {
        return 'ai:chat:' . md5(json_encode($payload));
    }

    /**
     * Persist a row in ai_usage_logs and update office monthly usage.
     */
    protected function logUsage(string $kind, string $model, $usage, int $latencyMs, string $status, ?string $error = null): void
    {
        try {
            $promptTokens = $usage?->promptTokens ?? 0;
            $completionTokens = $usage?->completionTokens ?? 0;
            $totalTokens = $usage?->totalTokens ?? ($promptTokens + $completionTokens);

            $pricing = config("reos.ai.pricing.{$model}", ['input' => 0, 'output' => 0]);
            $cost = (($promptTokens / 1000) * $pricing['input']) + (($completionTokens / 1000) * $pricing['output']);

            AiUsageLog::create([
                'office_id'         => $this->officeId,
                'user_id'           => $this->userId,
                'feature'           => $this->feature ?? $kind,
                'model'             => $model,
                'kind'              => $kind,
                'prompt_tokens'     => $promptTokens,
                'completion_tokens' => $completionTokens,
                'total_tokens'      => $totalTokens,
                'cost_usd'          => round($cost, 6),
                'latency_ms'        => $latencyMs,
                'status'            => $status,
                'error'             => $error,
            ]);
        } catch (\Throwable $e) {
            // Logging failures must never crash the AI flow.
            Log::warning('AI usage log write failed: ' . $e->getMessage());
        }
    }
}
