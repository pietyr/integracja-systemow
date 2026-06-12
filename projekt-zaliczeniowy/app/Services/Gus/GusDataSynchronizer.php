<?php

namespace App\Services\Gus;

use App\Models\Indicator;
use App\Models\IndicatorValue;
use App\Models\SyncRun;
use Illuminate\Support\Facades\DB;

class GusDataSynchronizer
{
    public function __construct(
        private readonly GusApiClient $client,
    ) {}

    public function sync(): SyncRun
    {
        $run = SyncRun::create([
            'source' => 'gus',
            'status' => 'running',
            'started_at' => now(),
        ]);

        $synced = 0;

        try {
            DB::transaction(function () use (&$synced) {
                foreach (config('integrations.gus.indicators') as $slug => $definition) {
                    $indicator = Indicator::updateOrCreate(
                        ['slug' => $slug],
                        [
                            'name' => $definition['name'],
                            'category' => $definition['category'],
                            'unit' => $definition['unit'],
                            'source' => $definition['source'] ?? 'gus',
                            'gus_variable_id' => $definition['gus_variable_id'] ?? null,
                        ],
                    );

                    $values = ($definition['source'] ?? 'gus') === 'manual'
                        ? $this->manualValues($definition['manual_values'] ?? [])
                        : $this->fetchPolandValues($definition['gus_variable_id']);

                    foreach ($values as $year => $value) {
                        IndicatorValue::updateOrCreate(
                            ['indicator_id' => $indicator->id, 'year' => $year],
                            ['value' => $value],
                        );
                        $synced++;
                    }
                }
            });

            $run->update([
                'status' => 'completed',
                'records_synced' => $synced,
                'finished_at' => now(),
            ]);
        } catch (\Throwable $e) {
            $run->update([
                'status' => 'failed',
                'message' => $e->getMessage(),
                'finished_at' => now(),
            ]);

            throw $e;
        }

        return $run->fresh();
    }

    /**
     * @param  array<int, float|int>  $values
     * @return array<int, float>
     */
    private function manualValues(array $values): array
    {
        return array_map('floatval', $values);
    }

    /**
     * @return array<int, float>
     */
    private function fetchPolandValues(int $variableId): array
    {
        $results = $this->client->getVariableData(
            $variableId,
            config('integrations.gus.unit_level'),
        );

        $poland = collect($results)->firstWhere('id', '000000000000');

        if (! $poland || empty($poland['values'])) {
            return [];
        }

        $values = [];

        foreach ($poland['values'] as $entry) {
            if (isset($entry['year'], $entry['val']) && $entry['attrId'] === 1) {
                $values[(int) $entry['year']] = (float) $entry['val'];
            }
        }

        return $values;
    }
}
