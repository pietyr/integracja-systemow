<?php

namespace App\Http\Controllers;

use App\Models\Indicator;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IndicatorPageController extends Controller
{
    public function index(Request $request): Response
    {
        $yearFrom = (int) $request->query('year_from', 2010);
        $yearTo = (int) $request->query('year_to', (int) date('Y'));
        $category = $request->query('category');

        $query = Indicator::query()->with(['values' => function ($q) use ($yearFrom, $yearTo) {
            $q->whereBetween('year', [$yearFrom, $yearTo])->orderBy('year');
        }]);

        if ($category) {
            $query->where('category', $category);
        }

        return Inertia::render('Indicators/Index', [
            'indicators' => $query->orderBy('category')->orderBy('name')->get(),
            'filters' => [
                'year_from' => $yearFrom,
                'year_to' => $yearTo,
                'category' => $category,
            ],
            'categories' => [
                ['value' => 'wage', 'label' => 'Wynagrodzenia'],
                ['value' => 'benefit', 'label' => 'Świadczenia'],
                ['value' => 'macro', 'label' => 'Makroekonomia'],
            ],
        ]);
    }
}
