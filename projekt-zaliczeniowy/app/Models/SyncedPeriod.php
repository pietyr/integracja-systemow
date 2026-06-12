<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SyncedPeriod extends Model
{
    protected $fillable = [
        'source',
        'year',
        'month',
        'records_synced',
        'synced_at',
    ];

    protected function casts(): array
    {
        return [
            'synced_at' => 'datetime',
        ];
    }

    public static function isSynced(string $source, int $year, int $month): bool
    {
        return static::query()
            ->where('source', $source)
            ->where('year', $year)
            ->where('month', $month)
            ->exists();
    }
}
