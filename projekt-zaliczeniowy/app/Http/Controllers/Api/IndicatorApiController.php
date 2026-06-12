<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Indicator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;

class IndicatorApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Indicator::query()->with(['values' => function ($q) use ($request) {
            if ($request->filled('year_from')) {
                $q->where('year', '>=', (int) $request->year_from);
            }
            if ($request->filled('year_to')) {
                $q->where('year', '<=', (int) $request->year_to);
            }
            $q->orderBy('year');
        }]);

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('slug')) {
            $query->where('slug', $request->slug);
        }

        return response()->json([
            'data' => $query->orderBy('name')->get(),
        ]);
    }

    public function export(Request $request): Response
    {
        $indicators = Indicator::query()
            ->with(['values' => function ($q) use ($request) {
                if ($request->filled('year_from')) {
                    $q->where('year', '>=', (int) $request->year_from);
                }
                if ($request->filled('year_to')) {
                    $q->where('year', '<=', (int) $request->year_to);
                }
                if ($request->filled('category')) {
                    //
                }
                $q->orderBy('year');
            }])
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->category))
            ->orderBy('name')
            ->get();

        $format = $request->query('format', 'json');

        if ($format === 'xml') {
            $xml = $this->toXml($indicators);

            return response($xml, 200, [
                'Content-Type' => 'application/xml',
                'Content-Disposition' => 'attachment; filename="wskazniki.xml"',
            ]);
        }

        $json = json_encode(['data' => $indicators], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        return response($json, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="wskazniki.json"',
        ]);
    }

    /**
     * @param  Collection<int, Indicator>  $indicators
     */
    private function toXml($indicators): string
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><indicators/>');

        foreach ($indicators as $indicator) {
            $node = $xml->addChild('indicator');
            $node->addChild('slug', htmlspecialchars($indicator->slug));
            $node->addChild('name', htmlspecialchars($indicator->name));
            $node->addChild('category', htmlspecialchars($indicator->category));
            $node->addChild('unit', htmlspecialchars($indicator->unit ?? ''));

            $valuesNode = $node->addChild('values');
            foreach ($indicator->values as $value) {
                $valueNode = $valuesNode->addChild('value');
                $valueNode->addChild('year', (string) $value->year);
                $valueNode->addChild('amount', (string) $value->value);
            }
        }

        return $xml->asXML() ?: '';
    }
}
