<?php

namespace App\Http\Controllers;

use App\Models\Indicator;
use App\Models\NewsArticle;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ComparisonPageController extends Controller
{
    public function index(Request $request): Response
    {
        $slug = $request->query('indicator', 'inflation');
        $yearFrom = (int) $request->query('year_from', 2010);
        $yearTo = (int) $request->query('year_to', (int) date('Y'));

        $indicator = Indicator::query()
            ->where('slug', $slug)
            ->firstOrFail();

        $series = $indicator->values()
            ->whereBetween('year', [$yearFrom, $yearTo])
            ->orderBy('year')
            ->get(['year', 'value']);

        $newsByYear = NewsArticle::query()
            ->whereNotNull('published_at')
            ->whereYear('published_at', '>=', $yearFrom)
            ->whereYear('published_at', '<=', $yearTo)
            ->orderByDesc('published_at')
            ->get(['id', 'headline', 'snippet', 'section', 'published_at', 'url'])
            ->groupBy(fn ($article) => $article->published_at->year)
            ->map(fn ($articles) => $articles->take(6)->values());

        return Inertia::render('Comparison/Index', [
            'indicatorOptions' => Indicator::query()
                ->orderBy('category')
                ->orderBy('name')
                ->get(['slug', 'name', 'unit', 'category']),
            'selected' => [
                'slug' => $indicator->slug,
                'name' => $indicator->name,
                'unit' => $indicator->unit,
                'category' => $indicator->category,
            ],
            'series' => $series,
            'newsByYear' => $newsByYear,
            'filters' => [
                'year_from' => $yearFrom,
                'year_to' => $yearTo,
            ],
        ]);
    }
}
