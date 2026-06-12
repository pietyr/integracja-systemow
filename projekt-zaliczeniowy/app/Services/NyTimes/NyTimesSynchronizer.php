<?php

namespace App\Services\NyTimes;

use App\Jobs\SyncNyTimesMonthJob;
use App\Models\NewsArticle;
use App\Models\SyncedPeriod;
use App\Models\SyncRun;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NyTimesSynchronizer
{
    public function __construct(
        private readonly NyTimesApiClient $client,
        private readonly NyTimesRateLimiter $rateLimiter,
    ) {}

    /**
     * Uruchamia łańcuch synchronizacji w kolejce (od najnowszego miesiąca wstecz do granicy w configu).
     */
    public function startBackgroundSync(bool $force = false): SyncRun
    {
        if ($force) {
            $this->clearPendingJobs();
        } elseif ($this->hasPendingChainJob()) {
            return SyncRun::create([
                'source' => 'nytimes',
                'status' => 'queued',
                'message' => 'Synchronizacja NY Times już trwa w kolejce.',
                'started_at' => now(),
            ]);
        }

        $next = $this->findNextUnsyncedMonth();

        if ($next === null) {
            return SyncRun::create([
                'source' => 'nytimes',
                'status' => 'completed',
                'message' => 'Wszystkie miesiące w skonfigurowanym zakresie są zsynchronizowane.',
                'started_at' => now(),
                'finished_at' => now(),
            ]);
        }

        if (! $this->rateLimiter->canMakeRequest()) {
            $this->scheduleMonth($next['year'], $next['month'], now()->addDay()->startOfDay()->addHours(1));

            return SyncRun::create([
                'source' => 'nytimes',
                'status' => 'queued',
                'message' => 'Osiągnięto dzienny limit API. Wznowienie jutro o 01:00.',
                'started_at' => now(),
            ]);
        }

        $this->scheduleMonth($next['year'], $next['month']);

        $remaining = $this->remainingMonthsCount();

        return SyncRun::create([
            'source' => 'nytimes',
            'status' => 'queued',
            'message' => "Rozpoczęto łańcuch synchronizacji. Kolejny miesiąc: {$next['year']}-".str_pad((string) $next['month'], 2, '0', STR_PAD_LEFT).". Pozostało ok. {$remaining} miesięcy.",
            'started_at' => now(),
        ]);
    }

    /**
     * Synchroniczne pobranie — jeden miesiąc po drugim z przerwą (tryb --blocking).
     */
    public function syncAllPending(): SyncRun
    {
        $run = SyncRun::create([
            'source' => 'nytimes',
            'status' => 'running',
            'started_at' => now(),
        ]);

        $synced = 0;
        $delay = (int) config('integrations.nytimes.request_delay_seconds');

        try {
            while ($this->rateLimiter->canMakeRequest()) {
                $next = $this->findNextUnsyncedMonth();
                if ($next === null) {
                    break;
                }

                $synced += $this->syncMonth($next['year'], $next['month']);
                sleep($delay);
            }

            $run->update([
                'status' => 'completed',
                'records_synced' => $synced,
                'message' => $this->rateLimiter->canMakeRequest()
                    ? null
                    : 'Zatrzymano — osiągnięto dzienny limit API. Uruchom ponownie jutro.',
                'finished_at' => now(),
            ]);
        } catch (\Throwable $e) {
            $run->update([
                'status' => 'failed',
                'records_synced' => $synced,
                'message' => $e->getMessage(),
                'finished_at' => now(),
            ]);

            throw $e;
        }

        return $run->fresh();
    }

    public function syncMonth(int $year, int $month): int
    {
        if (SyncedPeriod::isSynced('nytimes', $year, $month)) {
            return 0;
        }

        if (! $this->rateLimiter->canMakeRequest()) {
            return 0;
        }

        ini_set('memory_limit', '512M');

        $keywords = config('integrations.nytimes.keywords');
        $maxPerMonth = config('integrations.nytimes.max_articles_per_month');

        $docs = $this->client->getArchive($year, $month);
        $this->rateLimiter->recordRequest();

        $matched = $this->filterArticles($docs, $keywords, $maxPerMonth);
        unset($docs);

        $synced = 0;

        DB::transaction(function () use ($matched, &$synced) {
            foreach ($matched as $doc) {
                NewsArticle::updateOrCreate(
                    ['external_id' => $this->externalId($doc)],
                    [
                        'headline' => $doc['headline']['main'] ?? 'Bez tytułu',
                        'snippet' => $doc['snippet'] ?? null,
                        'section' => $doc['section_name'] ?? null,
                        'subsection' => $doc['subsection_name'] ?? null,
                        'published_at' => isset($doc['pub_date'])
                            ? date('Y-m-d', strtotime($doc['pub_date']))
                            : null,
                        'url' => $doc['web_url'] ?? null,
                        'keywords' => collect($doc['keywords'] ?? [])
                            ->pluck('value')
                            ->filter()
                            ->values()
                            ->all(),
                    ],
                );

                $synced++;
            }
        });

        SyncedPeriod::updateOrCreate(
            ['source' => 'nytimes', 'year' => $year, 'month' => $month],
            ['records_synced' => $synced, 'synced_at' => now()],
        );

        unset($matched);
        gc_collect_cycles();

        return $synced;
    }

    public function scheduleNextAfter(int $year, int $month): void
    {
        $cursor = NyTimesSyncPlanner::previousMonth($year, $month);

        while ($cursor !== null) {
            if (! SyncedPeriod::isSynced('nytimes', $cursor['year'], $cursor['month'])) {
                if (! $this->rateLimiter->canMakeRequest()) {
                    $this->scheduleMonth(
                        $cursor['year'],
                        $cursor['month'],
                        now()->addDay()->startOfDay()->addHours(1),
                    );

                    return;
                }

                $this->scheduleMonth($cursor['year'], $cursor['month']);

                return;
            }

            $cursor = NyTimesSyncPlanner::previousMonth($cursor['year'], $cursor['month']);
        }
    }

    public function hasPendingChainJob(): bool
    {
        return $this->pendingJobsCount() > 0;
    }

    public function pendingJobsCount(): int
    {
        return DB::table('jobs')
            ->where('payload', 'like', '%SyncNyTimesMonthJob%')
            ->count();
    }

    public function stalePendingJobsCount(): int
    {
        return DB::table('jobs')
            ->where('payload', 'like', '%SyncNyTimesMonthJob%')
            ->where('attempts', 0)
            ->where('reserved_at', null)
            ->where('available_at', '<', now()->subMinutes(5)->timestamp)
            ->count();
    }

    public function clearPendingJobs(): int
    {
        return DB::table('jobs')
            ->where('payload', 'like', '%SyncNyTimesMonthJob%')
            ->delete();
    }

    public function remainingMonthsCount(): int
    {
        $count = 0;
        $cursor = NyTimesSyncPlanner::startMonth();

        while ($cursor !== null) {
            if (! SyncedPeriod::isSynced('nytimes', $cursor['year'], $cursor['month'])) {
                $count++;
            }

            $cursor = NyTimesSyncPlanner::previousMonth($cursor['year'], $cursor['month']);
        }

        return $count;
    }

    /**
     * @return array{year: int, month: int}|null
     */
    public function findNextUnsyncedMonth(): ?array
    {
        $cursor = NyTimesSyncPlanner::startMonth();

        while ($cursor !== null) {
            if (! SyncedPeriod::isSynced('nytimes', $cursor['year'], $cursor['month'])) {
                return $cursor;
            }

            $cursor = NyTimesSyncPlanner::previousMonth($cursor['year'], $cursor['month']);
        }

        return null;
    }

    private function scheduleMonth(int $year, int $month, ?\DateTimeInterface $when = null): void
    {
        $delay = $when ?? now()->addSeconds((int) config('integrations.nytimes.request_delay_seconds'));

        SyncNyTimesMonthJob::dispatch($year, $month)->delay($delay);
    }

    /**
     * @param  array<int, array<string, mixed>>  $docs
     * @param  array<int, string>  $keywords
     * @return array<int, array<string, mixed>>
     */
    private function filterArticles(array $docs, array $keywords, int $limit): array
    {
        $matched = [];

        foreach ($docs as $doc) {
            if ($this->matchesKeywords($doc, $keywords)) {
                $matched[] = $doc;

                if (count($matched) >= $limit) {
                    break;
                }
            }
        }

        return $matched;
    }

    /**
     * @param  array<string, mixed>  $doc
     * @param  array<int, string>  $keywords
     */
    private function matchesKeywords(array $doc, array $keywords): bool
    {
        $haystack = Str::lower(implode(' ', array_filter([
            $doc['headline']['main'] ?? '',
            $doc['snippet'] ?? '',
            $doc['section_name'] ?? '',
            $doc['subsection_name'] ?? '',
            collect($doc['keywords'] ?? [])->pluck('value')->implode(' '),
        ])));

        foreach ($keywords as $keyword) {
            if (Str::contains($haystack, Str::lower($keyword))) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $doc
     */
    private function externalId(array $doc): string
    {
        if (! empty($doc['_id'])) {
            return (string) $doc['_id'];
        }

        return md5(($doc['web_url'] ?? '').($doc['pub_date'] ?? ''));
    }
}
