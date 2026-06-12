<?php

namespace App\Console\Commands;

use App\Models\IndicatorValue;
use App\Models\NewsArticle;
use App\Models\SyncedPeriod;
use App\Models\SyncRun;
use App\Services\NyTimes\NyTimesRateLimiter;
use App\Services\NyTimes\NyTimesSynchronizer;
use App\Services\NyTimes\NyTimesSyncPlanner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class IntegrationsStatusCommand extends Command
{
    protected $signature = 'integrations:status';

    protected $description = 'Status synchronizacji danych GUS i NY Times';

    public function handle(
        NyTimesSynchronizer $nytimes,
        NyTimesRateLimiter $rateLimiter,
    ): int {
        $this->info('=== GUS BDL ===');
        $gusRun = SyncRun::query()->where('source', 'gus')->orderByDesc('started_at')->first();
        $this->line('Wskaźniki: '.DB::table('indicators')->count());
        $this->line('Wartości roczne: '.IndicatorValue::count());
        if ($gusRun) {
            $this->line("Ostatnia synchronizacja: {$gusRun->status} ({$gusRun->started_at}), rekordów: {$gusRun->records_synced}");
        }

        $this->newLine();
        $this->info('=== NY Times Archive ===');

        $syncedMonths = SyncedPeriod::query()->where('source', 'nytimes')->count();
        $articles = NewsArticle::count();
        $pendingJobs = $nytimes->pendingJobsCount();
        $staleJobs = $nytimes->stalePendingJobsCount();
        $next = $nytimes->findNextUnsyncedMonth();
        $remaining = $nytimes->remainingMonthsCount();

        $range = SyncedPeriod::query()
            ->where('source', 'nytimes')
            ->selectRaw('MIN(year * 100 + month) as oldest_key, MAX(year * 100 + month) as newest_key')
            ->first();

        $this->line('Skonfigurowany zakres: '.NyTimesSyncPlanner::rangeLabel());
        $this->line("Zsynchronizowane miesiące: {$syncedMonths}");
        $this->line("Artykuły w bazie: {$articles}");
        $this->line("Zadania w kolejce: {$pendingJobs}");
        $this->line('Pozostało miesięcy do pobrania: '.$remaining);
        $this->line('Limit API dziś: '.$rateLimiter->requestsToday().'/'.config('integrations.nytimes.max_requests_per_day').' (pozostało: '.$rateLimiter->remainingToday().')');

        if ($range?->oldest_key) {
            $oldest = $this->formatPeriodKey((int) $range->oldest_key);
            $newest = $this->formatPeriodKey((int) $range->newest_key);
            $this->line("Zakres w bazie: {$newest} → {$oldest}");
        }

        if ($next) {
            $this->line('Kolejny miesiąc do pobrania: '.$this->formatPeriod($next['year'], $next['month']));
        } else {
            $this->line('Kolejny miesiąc: brak (zakres ukończony lub limit dzienny)');
        }

        $nytRun = SyncRun::query()->where('source', 'nytimes')->orderByDesc('started_at')->first();
        if ($nytRun) {
            $this->line("Ostatnie uruchomienie: {$nytRun->status} — {$nytRun->message}");
        }

        if ($staleJobs > 0) {
            $this->warn("{$staleJobs} zadań czeka w kolejce ponad 5 min — worker może nie działać.");
            $this->warn('Sprawdź: docker compose ps worker');
            $this->warn('Naprawa: docker compose up -d worker && php artisan integrations:sync --source=nytimes --force');
        } elseif ($pendingJobs > 0) {
            $this->comment('Synchronizacja trwa w tle (worker kolejki).');
        } elseif ($remaining > 0 && $rateLimiter->canMakeRequest()) {
            $this->comment('Uruchom: php artisan integrations:sync --source=nytimes');
        }

        return self::SUCCESS;
    }

    private function formatPeriodKey(int $key): string
    {
        return $this->formatPeriod(intdiv($key, 100), $key % 100);
    }

    private function formatPeriod(int $year, int $month): string
    {
        return sprintf('%d-%02d', $year, $month);
    }
}
