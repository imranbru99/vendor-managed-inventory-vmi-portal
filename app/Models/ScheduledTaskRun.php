<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduledTaskRun extends Model
{
    protected $fillable = [
        'task_name',
        'status',
        'started_at',
        'completed_at',
        'error',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'meta' => 'array',
        ];
    }
}
