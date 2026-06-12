<?php

namespace App\Http\Controllers;

use App\Models\NewsArticle;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NewsPageController extends Controller
{
    public function index(Request $request): Response
    {
        $query = NewsArticle::query()->orderByDesc('published_at');

        if ($request->filled('section')) {
            $query->where('section', $request->section);
        }

        if ($request->filled('year')) {
            $query->whereYear('published_at', (int) $request->year);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('headline', 'like', "%{$search}%")
                    ->orWhere('snippet', 'like', "%{$search}%");
            });
        }

        $sections = NewsArticle::query()
            ->whereNotNull('section')
            ->distinct()
            ->orderBy('section')
            ->pluck('section');

        $years = NewsArticle::query()
            ->whereNotNull('published_at')
            ->selectRaw('YEAR(published_at) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');

        return Inertia::render('News/Index', [
            'articles' => $query->paginate(15)->withQueryString(),
            'sections' => $sections,
            'years' => $years,
            'filters' => $request->only(['section', 'year', 'search']),
        ]);
    }
}
