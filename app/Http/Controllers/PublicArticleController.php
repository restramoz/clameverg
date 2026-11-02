<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Services\RegionalService;
use Illuminate\View\View;

class PublicArticleController extends Controller
{
    /**
     * Display public index of published articles.
     */
    public function index(): View
    {
        $articles = Article::published()
            ->latest('published_at')
            ->paginate(12);

        // Self-referencing canonical: /artikel?page=N only for pages that
        // actually exist (2+); every other query combination, including
        // arbitrary parameters, falls back to the clean URL.
        $page = $articles->currentPage();
        $canonicalUrl = rtrim((string) config('app.url'), '/').'/artikel'
            .($page > 1 && $page <= $articles->lastPage() ? '?page='.$page : '');

        return view('articles.index', compact('articles', 'canonicalUrl'));
    }

    /**
     * Display single published article.
     */
    public function show(string $slug, RegionalService $regionalService): View
    {
        $article = Article::published()
            ->where('slug', $slug)
            ->firstOrFail();

        $targetCity = null;
        if ($article->target_city_slug) {
            $targetCity = $regionalService->findCity($article->target_city_slug);
        }

        $relatedArticles = Article::published()
            ->where('id', '!=', $article->id)
            ->latest('published_at')
            ->take(3)
            ->get();

        return view('articles.show', compact('article', 'targetCity', 'relatedArticles'));
    }
}
