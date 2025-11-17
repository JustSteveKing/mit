<?php

declare(strict_types=1);

namespace JustSteveKing\Mit\Examples\TaskScheduler\Listeners;

use DateTimeImmutable;
use JustSteveKing\Mit\Examples\TaskScheduler\Events\TaskCompleted;
use JustSteveKing\Mit\Examples\TaskScheduler\Events\TaskFailed;
use JustSteveKing\Mit\Examples\TaskScheduler\Events\TaskScheduled;
use JustSteveKing\Mit\Examples\TaskScheduler\Events\TaskStarted;

/**
 * Logs task lifecycle events.
 */
final class TaskLogger
{
    /**
     * @var list<string>
     */
    private array $logs = [];

    public function onTaskScheduled(TaskScheduled $event): void
    {
        $this->log(sprintf(
            'Task scheduled: %s (%s) at %s',
            $event->task->name,
            $event->task->id,
            $event->task->scheduledAt->format('Y-m-d H:i:s'),
        ));
    }

    public function onTaskStarted(TaskStarted $event): void
    {
        $this->log(sprintf(
            'Task started: %s (%s)',
            $event->task->name,
            $event->task->id,
        ));
    }

    public function onTaskCompleted(TaskCompleted $event): void
    {
        $this->log(sprintf(
            'Task completed: %s (%s)',
            $event->task->name,
            $event->task->id,
        ));
    }

    public function onTaskFailed(TaskFailed $event): void
    {
        $this->log(sprintf(
            'Task failed: %s (%s) - %s',
            $event->task->name,
            $event->task->id,
            $event->exception->getMessage(),
        ));
    }

    /**
     * @return list<string>
     */
    public function getLogs(): array
    {
        return $this->logs;
    }

    private function log(string $message): void
    {
        $timestamp = new DateTimeImmutable()->format('Y-m-d H:i:s');
        $this->logs[] = "[{$timestamp}] {$message}";
    }
}
