<?php

namespace App\Services\NyTimes;

use Illuminate\Support\Facades\Cache;

class NyTimesRateLimiter
{
    public function requestsToday(): int
    {
        return (int) Cache::get($this->cacheKey(), 0);
    }

    public function canMakeRequest(): bool
    {
        return $this->requestsToday() < (int) config('integrations.nytimes.max_requests_per_day');
    }

    public function recordRequest(): void
    {
        $key = $this->cacheKey();
        $count = $this->requestsToday() + 1;

        Cache::put($key, $count, now()->endOfDay());
    }

    public function remainingToday(): int
    {
        return max(0, (int) config('integrations.nytimes.max_requests_per_day') - $this->requestsToday());
    }

    private function cacheKey(): string
    {
        return 'nytimes_api_requests_'.now()->format('Y-m-d');
    }
}
