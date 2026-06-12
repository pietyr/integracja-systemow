<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NewsArticle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NewsApiController extends Controller
{
    public function index(Request $request): JsonResponse
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

        return response()->json([
            'data' => $query->paginate(20),
        ]);
    }
}
