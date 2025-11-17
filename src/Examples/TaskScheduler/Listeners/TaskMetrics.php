<?php

declare(strict_types=1);

namespace JustSteveKing\Mit\Examples\TaskScheduler\Listeners;

use JustSteveKing\Mit\Examples\TaskScheduler\Events\TaskCompleted;
use JustSteveKing\Mit\Examples\TaskScheduler\Events\TaskFailed;
use JustSteveKing\Mit\Examples\TaskScheduler\Events\TaskScheduled;

/**
 * Collects metrics about task execution.
 */
final class TaskMetrics
{
    private int $scheduled = 0;

    private int $completed = 0;

    private int $failed = 0;

    public function onTaskScheduled(TaskScheduled $event): void
    {
        $this->scheduled++;
    }

    public function onTaskCompleted(TaskCompleted $event): void
    {
        $this->completed++;
    }

    public function onTaskFailed(TaskFailed $event): void
    {
        $this->failed++;
    }

    /**
     * @return array{scheduled: int, completed: int, failed: int, success_rate: float}
     */
    public function getMetrics(): array
    {
        $total = $this->completed + $this->failed;
        $successRate = $total > 0 ? ($this->completed / $total) * 100 : 0;

        return [
            'scheduled' => $this->scheduled,
            'completed' => $this->completed,
            'failed' => $this->failed,
            'success_rate' => round($successRate, 2),
        ];
    }
}
