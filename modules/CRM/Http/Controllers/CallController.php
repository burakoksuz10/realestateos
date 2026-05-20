<?php

namespace Modules\CRM\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\CRM\Models\Activity;
use Modules\CRM\Models\Lead;
use Modules\CRM\Services\CallTranscriptionService;

class CallController extends Controller
{
    public function __construct(protected CallTranscriptionService $transcription) {}

    /**
     * Lead için manuel ses dosyası yükle + transkript + AI özet.
     * - audio file (mp3/wav/m4a/ogg) veya recording_url
     * - lead_id zorunlu
     */
    public function transcribe(Request $request)
    {
        $data = $request->validate([
            'lead_id'        => 'required|integer|exists:leads,id',
            'audio'          => 'nullable|file|mimes:mp3,wav,m4a,ogg,opus,webm,mpga|max:51200', // 50MB
            'recording_url'  => 'nullable|url',
            'duration'       => 'nullable|integer',
            'language'       => 'nullable|string|max:5',
        ]);

        if (empty($data['audio']) && empty($data['recording_url'])) {
            return back()->withErrors(['audio' => 'Bir ses dosyası veya kayıt URL\'si zorunlu.']);
        }

        $lead = Lead::findOrFail($data['lead_id']);

        try {
            $opts = [
                'language'  => $data['language'] ?? 'tr',
                'office_id' => $request->user()->office_id ?? null,
                'user_id'   => $request->user()->id,
            ];

            if (!empty($data['audio'])) {
                $tmpPath = $data['audio']->getRealPath();
                $result = $this->transcription->fromFile($tmpPath, $opts);
                $recordingUrl = null;
            } else {
                $result = $this->transcription->fromUrl($data['recording_url'], $opts);
                $recordingUrl = $data['recording_url'];
            }

            if (empty($result['text'])) {
                return back()->withErrors([
                    'transcribe' => 'Transkripsiyon boş döndü. Sağlayıcı: ' . ($result['provider'] ?? '?'),
                ]);
            }

            $activity = $this->transcription->createCallActivity(
                leadId: $lead->id,
                contactId: $lead->contact_id,
                recordingUrl: $recordingUrl,
                result: $result,
                userId: $request->user()->id,
                durationSeconds: $data['duration'] ?? null,
            );

            if ($request->wantsJson()) {
                return response()->json([
                    'success'  => true,
                    'activity' => $activity,
                    'result'   => $result,
                ]);
            }

            return back()->with('success', 'Çağrı özetlendi. Aktivite #' . $activity->id);
        } catch (\Throwable $e) {
            Log::error('Call transcribe failed', ['lead' => $lead->id, 'error' => $e->getMessage()]);
            return back()->withErrors(['transcribe' => 'Hata: ' . $e->getMessage()]);
        }
    }

    /**
     * Var olan bir Activity'nin (örneğin webhook'tan otomatik düşen) ses kaydını
     * sonradan transkript et / AI özet ekle.
     */
    public function transcribeActivity(Request $request, Activity $activity)
    {
        if ($activity->type !== 'call') {
            return back()->withErrors(['transcribe' => 'Activity bir çağrı değil.']);
        }
        if (!$activity->call_recording_url) {
            return back()->withErrors(['transcribe' => 'Çağrının recording URL\'si yok.']);
        }

        try {
            $result = $this->transcription->fromUrl($activity->call_recording_url, [
                'office_id' => $request->user()->office_id ?? null,
                'user_id'   => $request->user()->id,
            ]);
            $this->transcription->attachToActivity($activity, $result);

            return back()->with('success', 'Activity güncellendi.');
        } catch (\Throwable $e) {
            Log::error('Activity transcribe failed', ['activity' => $activity->id, 'error' => $e->getMessage()]);
            return back()->withErrors(['transcribe' => 'Hata: ' . $e->getMessage()]);
        }
    }
}
