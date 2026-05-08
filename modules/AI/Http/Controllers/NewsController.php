<?php

namespace Modules\AI\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\NewsArticle;
use Modules\AI\Services\NewsService;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    public function __construct(private NewsService $newsService) {}

    public function index(Request $request)
    {
        $query = NewsArticle::latest();

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('ai_summary', 'like', "%{$search}%");
            });
        }

        $articles = $query->paginate(25);
        $categories = NewsArticle::select('category')->distinct()->pluck('category');

        return view('ai::news.index', compact('articles', 'categories'));
    }

    public function fetch()
    {
        $result = $this->newsService->fetch();
        return back()->with('success', "{$result['new']} yeni haber eklendi.");
    }

    public function destroy(NewsArticle $article)
    {
        $article->delete();
        return back()->with('success', 'Haber silindi.');
    }
}
