<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsArticle extends Model
{
    protected $fillable = [
        'external_id',
        'headline',
        'snippet',
        'section',
        'subsection',
        'published_at',
        'url',
        'keywords',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'date',
            'keywords' => 'array',
        ];
    }
}
