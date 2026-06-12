<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Indicator extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'category',
        'unit',
        'source',
        'gus_variable_id',
    ];

    public function values(): HasMany
    {
        return $this->hasMany(IndicatorValue::class);
    }
}
