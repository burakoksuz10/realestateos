<?php

namespace Modules\AI\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ContentController extends Controller
{
    private function stub(string $type): array
    {
        return [
            'content'      => "AI içerik üretimi aktif değil. ({$type})",
            'generated_at' => now()->toISOString(),
            'note'         => 'OpenAI API anahtarını ayarlardan ekleyin.',
        ];
    }

    public function generateDescription(Request $request)
    {
        return response()->json(['success' => true, 'data' => $this->stub('description')]);
    }

    public function generateSocial(Request $request)
    {
        return response()->json(['success' => true, 'data' => $this->stub('social')]);
    }

    public function generateAds(Request $request)
    {
        return response()->json(['success' => true, 'data' => $this->stub('ads')]);
    }

    public function generateHeadlines(Request $request)
    {
        return response()->json(['success' => true, 'data' => $this->stub('headlines')]);
    }

    public function generateSEO(Request $request)
    {
        return response()->json(['success' => true, 'data' => $this->stub('seo')]);
    }

    public function improve(Request $request)
    {
        return response()->json(['success' => true, 'data' => $this->stub('improve')]);
    }
}
