<?php

namespace App\Console\Concerns;

use App\Models\ScheduledTaskRun;

trait LogsScheduledTask
{
    protected function beginScheduledTask(string $taskName): ScheduledTaskRun
    {
        return ScheduledTaskRun::query()->create([
            'task_name' => $taskName,
            'status' => 'running',
            'started_at' => now(),
        ]);
    }

    protected function completeScheduledTask(ScheduledTaskRun $run, array $meta = []): void
    {
        $run->update([
            'status' => 'completed',
            'completed_at' => now(),
            'meta' => $meta,
        ]);
    }

    protected function failScheduledTask(ScheduledTaskRun $run, \Throwable $e, array $meta = []): void
    {
        $run->update([
            'status' => 'failed',
            'completed_at' => now(),
            'error' => $e->getMessage(),
            'meta' => $meta,
        ]);
    }
}
