<?php

namespace App\Services\NyTimes;

use Carbon\Carbon;

class NyTimesSyncPlanner
{
    /**
     * @return array{year: int, month: int}
     */
    public static function startMonth(): array
    {
        return [
            'year' => (int) config('integrations.nytimes.from.year'),
            'month' => (int) config('integrations.nytimes.from.month'),
        ];
    }

    /**
     * @return array{year: int, month: int}|null
     */
    public static function previousMonth(int $year, int $month): ?array
    {
        $date = Carbon::create($year, $month, 1)->subMonth();

        if (! self::isWithinRange($date->year, $date->month)) {
            return null;
        }

        return [
            'year' => $date->year,
            'month' => $date->month,
        ];
    }

    public static function isWithinRange(int $year, int $month): bool
    {
        $untilYear = (int) config('integrations.nytimes.until.year');
        $untilMonth = (int) config('integrations.nytimes.until.month');
        $fromYear = (int) config('integrations.nytimes.from.year');
        $fromMonth = (int) config('integrations.nytimes.from.month');

        $period = $year * 100 + $month;
        $from = $fromYear * 100 + $fromMonth;
        $until = $untilYear * 100 + $untilMonth;

        return $period >= $until && $period <= $from;
    }

    public static function rangeLabel(): string
    {
        $from = self::startMonth();
        $untilYear = (int) config('integrations.nytimes.until.year');
        $untilMonth = (int) config('integrations.nytimes.until.month');

        return sprintf(
            '%d-%02d → %d-%02d',
            $from['year'],
            $from['month'],
            $untilYear,
            $untilMonth,
        );
    }
}
