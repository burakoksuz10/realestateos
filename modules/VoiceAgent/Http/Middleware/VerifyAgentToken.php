<?php

namespace Modules\VoiceAgent\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * ElevenLabs Agent tool çağrıları + webhook'ları için shared-secret doğrulaması.
 * Header: `X-Voice-Agent-Token: {{ ENV VOICE_AGENT_SHARED_SECRET }}`
 *
 * Anahtar `.env`'de tanımlı değilse middleware no-op (lokal geliştirme için).
 */
class VerifyAgentToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = config('services.voice_agent.shared_secret');
        if (empty($expected)) {
            return $next($request); // dev mode — auth off
        }

        $provided = $request->header('X-Voice-Agent-Token')
            ?? $request->bearerToken();

        if (!hash_equals($expected, (string) $provided)) {
            return response()->json(['error' => 'unauthorized'], 401);
        }

        return $next($request);
    }
}
