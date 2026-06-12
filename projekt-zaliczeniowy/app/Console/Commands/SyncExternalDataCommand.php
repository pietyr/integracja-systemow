<?php

namespace App\Console\Commands;

use App\Services\Gus\GusDataSynchronizer;
use App\Services\NyTimes\NyTimesSynchronizer;
use Illuminate\Console\Command;

class SyncExternalDataCommand extends Command
{
    protected $signature = 'integrations:sync
                            {--source= : gus, nytimes lub puste dla obu źródeł}
                            {--nytimes-only : Skrót dla --source=nytimes}
                            {--blocking : NY Times: pobierz synchronicznie zamiast kolejkować}
                            {--force : NY Times: usuń zawieszone zadania z kolejki i uruchom od nowa}';

    protected $description = 'Pobiera dane z API GUS i NY Times do lokalnej bazy danych';

    public function handle(
        GusDataSynchronizer $gus,
        NyTimesSynchronizer $nytimes,
    ): int {
        $source = $this->option('nytimes-only')
            ? 'nytimes'
            : ($this->option('source') ?: 'all');

        if (in_array($source, ['all', 'gus'], true)) {
            $this->info('Synchronizacja danych GUS BDL...');
            $run = $gus->sync();
            $this->line("GUS: {$run->status}, rekordów: {$run->records_synced}");
        }

        if (in_array($source, ['all', 'nytimes'], true)) {
            if ($this->option('blocking')) {
                $this->info('Synchronizacja NY Times (tryb blokujący)...');
                $run = $nytimes->syncAllPending();
                $this->line("NY Times: {$run->status}, rekordów: {$run->records_synced}");
                if ($run->message) {
                    $this->comment($run->message);
                }
            } else {
                $run = $nytimes->startBackgroundSync((bool) $this->option('force'));
                $this->line("NY Times: {$run->status} — {$run->message}");
            }
        }

        $this->info('Synchronizacja zakończona.');

        return self::SUCCESS;
    }
}
