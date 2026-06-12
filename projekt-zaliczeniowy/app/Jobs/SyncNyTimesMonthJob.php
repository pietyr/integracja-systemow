<?php

namespace App\Jobs;

use App\Services\NyTimes\NyTimesSynchronizer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SyncNyTimesMonthJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 300;

    public function __construct(
        public int $year,
        public int $month,
    ) {}

    public function handle(NyTimesSynchronizer $synchronizer): void
    {
        $synchronizer->syncMonth($this->year, $this->month);
        $synchronizer->scheduleNextAfter($this->year, $this->month);
    }
}
