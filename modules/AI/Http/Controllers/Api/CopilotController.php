<?php

namespace Modules\AI\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\CRM\Models\Lead;

class CopilotController extends Controller
{
    private string $note = 'AI Copilot aktif değil. OpenAI API anahtarını ayarlardan ekleyin.';

    public function leadSuggestions(Request $request, Lead $lead)
    {
        return response()->json(['success' => true, 'data' => ['suggestions' => [], 'note' => $this->note]]);
    }

    public function analyzeCall(Request $request)
    {
        return response()->json(['success' => true, 'data' => ['analysis' => null, 'note' => $this->note]]);
    }

    public function analyzeMessage(Request $request)
    {
        return response()->json(['success' => true, 'data' => ['analysis' => null, 'note' => $this->note]]);
    }

    public function suggestResponse(Request $request)
    {
        return response()->json(['success' => true, 'data' => ['response' => null, 'note' => $this->note]]);
    }

    public function nextActions(Request $request, Lead $lead)
    {
        return response()->json(['success' => true, 'data' => ['actions' => [], 'note' => $this->note]]);
    }

    public function chat(Request $request)
    {
        return response()->json(['success' => true, 'data' => ['reply' => $this->note]]);
    }
}
