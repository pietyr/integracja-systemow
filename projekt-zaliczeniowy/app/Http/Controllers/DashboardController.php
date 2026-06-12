<?php

namespace App\Http\Controllers;

use App\Models\Indicator;
use App\Models\NewsArticle;
use App\Models\SyncRun;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        $latestValues = Indicator::query()
            ->with(['values' => fn ($q) => $q->orderByDesc('year')->limit(1)])
            ->orderBy('category')
            ->get()
            ->map(fn (Indicator $indicator) => [
                'slug' => $indicator->slug,
                'name' => $indicator->name,
                'category' => $indicator->category,
                'unit' => $indicator->unit,
                'latest' => $indicator->values->first(),
            ]);

        return Inertia::render('Dashboard', [
            'indicators' => $latestValues,
            'recentNews' => NewsArticle::query()
                ->orderByDesc('published_at')
                ->limit(5)
                ->get(['id', 'headline', 'section', 'published_at', 'url']),
            'lastSync' => SyncRun::query()
                ->orderByDesc('started_at')
                ->limit(3)
                ->get(),
        ]);
    }
}
