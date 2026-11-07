<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CmsPage extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'locale',
        'excerpt',
        'body',
        'template',
        'seo_title',
        'seo_description',
        'is_published',
        'published_at',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'published_at' => 'datetime',
            'meta' => 'array',
        ];
    }
}
