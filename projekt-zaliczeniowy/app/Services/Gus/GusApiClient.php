<?php

namespace App\Services\Gus;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class GusApiClient
{
    public function getVariableData(int $variableId, int $unitLevel = 0): array
    {
        $this->throttle();

        $response = $this->client()->get("/data/by-variable/{$variableId}", [
            'unit-level' => $unitLevel,
            'format' => 'json',
        ]);

        $response->throw();

        return $response->json('results', []);
    }

    private function client(): PendingRequest
    {
        return Http::baseUrl(config('integrations.gus.base_url'))
            ->acceptJson()
            ->timeout(60);
    }

    private function throttle(): void
    {
        usleep(config('integrations.gus.request_delay_ms') * 1000);
    }
}
