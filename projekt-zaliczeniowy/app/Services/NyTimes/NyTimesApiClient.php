<?php

namespace App\Services\NyTimes;

use Illuminate\Support\Facades\Http;

class NyTimesApiClient
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function getArchive(int $year, int $month): array
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'nyt_');

        try {
            $response = Http::baseUrl(config('integrations.nytimes.base_url'))
                ->acceptJson()
                ->timeout(180)
                ->withOptions(['sink' => $tempFile])
                ->get("/{$year}/{$month}.json", [
                    'api-key' => config('services.nytimes.key'),
                ]);

            $response->throw();

            $payload = json_decode((string) file_get_contents($tempFile), true, 512, JSON_THROW_ON_ERROR);

            return $payload['response']['docs'] ?? [];
        } finally {
            if ($tempFile && file_exists($tempFile)) {
                unlink($tempFile);
            }
        }
    }
}
