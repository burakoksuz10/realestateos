<?php

namespace Modules\AI\Services;

use App\Models\AiUsageLog;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Fal.ai görsel iyileştirme servisi.
 *
 * Desteklenen operasyonlar:
 *  - skyReplacement: gökyüzünü iyileştir / değiştir
 *  - twilight: gün batımı / altın saat efekti
 *  - declutter: oda toplamak (eşyaları kaldır)
 *  - virtualStaging: boş odaya AI ile mobilya yerleştir
 *  - enhance: çözünürlük artırma + netleştirme
 *
 * Fal.ai sync API kullanır — yanıt direkt image URL döner.
 * Uzun süren işler için subscribe/queue API'sine geçilebilir.
 */
class FalAiService
{
    protected ?int $officeId = null;
    protected ?int $userId = null;

    public function withContext(?int $officeId = null, ?int $userId = null): self
    {
        $this->officeId = $officeId;
        $this->userId = $userId;
        return $this;
    }

    public function isEnabled(): bool
    {
        return (bool) config('reos.ai.image.enabled', true)
            && !empty(config('services.fal.api_key'));
    }

    /**
     * Gökyüzünü iyileştir — bulutlu / kapalı havadaki ilanları temizle.
     */
    public function skyReplacement(string $imageUrl, array $options = []): array
    {
        return $this->run('sky_replacement', $imageUrl, array_merge([
            'prompt' => 'bright clear blue sky, sunny daylight, professional real estate photography',
            'strength' => 0.55,
        ], $options));
    }

    /**
     * Twilight / altın saat efekti — fotoğrafa sıcak akşam ışığı ver.
     */
    public function twilight(string $imageUrl, array $options = []): array
    {
        return $this->run('twilight', $imageUrl, array_merge([
            'prompt' => 'twilight golden hour lighting, warm sky, dramatic real estate exterior',
            'strength' => 0.6,
        ], $options));
    }

    /**
     * Eşya temizleme — odadaki dağınıklığı / fazla nesneyi kaldır.
     */
    public function declutter(string $imageUrl, array $maskOrOptions = []): array
    {
        return $this->run('declutter', $imageUrl, array_merge([
            'prompt' => 'remove clutter, clean and minimalist interior, professional staging',
        ], $maskOrOptions));
    }

    /**
     * Boş alana AI ile mobilya yerleştir — virtual staging.
     */
    public function virtualStaging(string $imageUrl, string $stylePrompt = 'modern Scandinavian living room'): array
    {
        return $this->run('virtual_staging', $imageUrl, [
            'prompt' => $stylePrompt . ', tasteful furniture, natural light, real estate listing photo',
            'image_size' => 'landscape_16_9',
        ]);
    }

    /**
     * Netleştirme + çözünürlük artırma.
     */
    public function enhance(string $imageUrl, array $options = []): array
    {
        return $this->run('enhance', $imageUrl, array_merge([
            'upscale_factor' => 2,
            'creativity' => 0.35,
        ], $options));
    }

    /**
     * Genel runner — model adını config'den alır, Fal.ai sync API çağırır.
     */
    protected function run(string $operation, string $imageUrl, array $params): array
    {
        if (!$this->isEnabled()) {
            return [
                'success' => false,
                'error' => 'Fal.ai entegrasyonu kapalı veya FAL_API_KEY eksik.',
            ];
        }

        $model = config("reos.ai.image.models.{$operation}");
        if (!$model) {
            return ['success' => false, 'error' => "Bilinmeyen operasyon: {$operation}"];
        }

        $payload = array_merge([
            'image_url' => $imageUrl,
        ], $params);

        $startedAt = microtime(true);
        $status = 'success';
        $errorMessage = null;
        $resultUrl = null;
        $raw = null;

        try {
            $response = $this->httpClient()->post($this->endpoint($model), $payload);

            if ($response->failed()) {
                $status = 'error';
                $errorMessage = 'Fal.ai HTTP ' . $response->status() . ': ' . $response->body();
            } else {
                $raw = $response->json();
                $resultUrl = $this->extractImageUrl($raw);
                if (!$resultUrl) {
                    $status = 'error';
                    $errorMessage = 'Fal.ai yanıtında image_url bulunamadı.';
                }
            }
        } catch (RequestException $e) {
            $status = 'error';
            $errorMessage = $e->getMessage();
            Log::warning('Fal.ai request failed', ['op' => $operation, 'error' => $e->getMessage()]);
        } catch (\Throwable $e) {
            $status = 'error';
            $errorMessage = $e->getMessage();
            Log::error('Fal.ai unexpected error', ['op' => $operation, 'error' => $e->getMessage()]);
        }

        $latencyMs = (int) ((microtime(true) - $startedAt) * 1000);

        $this->logUsage($operation, $model, $status, $errorMessage, $latencyMs);

        return [
            'success' => $status === 'success',
            'operation' => $operation,
            'image_url' => $resultUrl,
            'error' => $errorMessage,
            'latency_ms' => $latencyMs,
            'raw' => $raw,
        ];
    }

    protected function httpClient(): PendingRequest
    {
        return Http::withHeaders([
            'Authorization' => 'Key ' . config('services.fal.api_key'),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->timeout((int) config('reos.ai.image.timeout', 90));
    }

    protected function endpoint(string $model): string
    {
        $base = rtrim((string) config('services.fal.base_url', 'https://fal.run'), '/');
        return $base . '/' . ltrim($model, '/');
    }

    /**
     * Fal modelleri farklı yanıt şekilleri döner — yaygın anahtarları sırayla dene.
     */
    protected function extractImageUrl(?array $response): ?string
    {
        if (!$response) return null;

        if (isset($response['image']['url'])) return $response['image']['url'];
        if (isset($response['images'][0]['url'])) return $response['images'][0]['url'];
        if (isset($response['output']['url'])) return $response['output']['url'];
        if (isset($response['url']) && is_string($response['url'])) return $response['url'];

        return null;
    }

    protected function logUsage(string $operation, string $model, string $status, ?string $error, int $latencyMs): void
    {
        try {
            AiUsageLog::create([
                'office_id' => $this->officeId,
                'user_id' => $this->userId,
                'feature' => 'image.' . $operation,
                'model' => $model,
                'kind' => 'image',
                'prompt_tokens' => 0,
                'completion_tokens' => 0,
                'total_tokens' => 0,
                'cost_usd' => 0,
                'latency_ms' => $latencyMs,
                'status' => $status,
                'error' => $error,
            ]);
        } catch (\Throwable $e) {
            Log::warning('FalAi usage log failed: ' . $e->getMessage());
        }
    }
}
