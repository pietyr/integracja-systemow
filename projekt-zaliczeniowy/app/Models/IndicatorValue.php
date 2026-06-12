<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IndicatorValue extends Model
{
    protected $fillable = [
        'indicator_id',
        'year',
        'value',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'value' => 'decimal:4',
        ];
    }

    public function indicator(): BelongsTo
    {
        return $this->belongsTo(Indicator::class);
    }
}
