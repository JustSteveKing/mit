<?php

declare(strict_types=1);

namespace JustSteveKing\Mit\Examples\TaskScheduler;

use DateTimeImmutable;
use JustSteveKing\Mit\EmitsEvents;
use JustSteveKing\Mit\Examples\TaskScheduler\Events\TaskCompleted;
use JustSteveKing\Mit\Examples\TaskScheduler\Events\TaskFailed;
use JustSteveKing\Mit\Examples\TaskScheduler\Events\TaskScheduled;
use JustSteveKing\Mit\Examples\TaskScheduler\Events\TaskStarted;
use RuntimeException;
use Throwable;

/**
 * Simple task scheduler that emits events for each stage of task execution.
 */
final class Scheduler
{
    use EmitsEvents;

    /**
     * @var array<string, Task>
     */
    private array $tasks = [];

    /**
     * @var array<string, callable>
     */
    private array $handlers = [];

    /**
     * Register a task handler.
     *
     * @param  callable(Task): mixed  $handler
     */
    public function registerHandler(string $name, callable $handler): void
    {
        $this->handlers[$name] = $handler;
    }

    /**
     * Schedule a task for execution.
     */
    public function schedule(Task $task): void
    {
        $this->tasks[$task->id] = $task;
        $this->emit(new TaskScheduled($task));
    }

    /**
     * Process all tasks that are due.
     */
    public function processDueTasks(DateTimeImmutable $now): void
    {
        foreach ($this->tasks as $id => $task) {
            if ($task->scheduledAt <= $now) {
                $this->processTask($task);
                unset($this->tasks[$id]);
            }
        }
    }

    /**
     * Get count of pending tasks.
     */
    public function getPendingCount(): int
    {
        return count($this->tasks);
    }

    /**
     * Process a single task.
     */
    private function processTask(Task $task): void
    {
        $this->emit(new TaskStarted($task));

        try {
            $handler = $this->handlers[$task->handler] ?? null;

            if (null === $handler) {
                throw new RuntimeException(
                    sprintf('No handler registered for: %s', $task->handler),
                );
            }

            $result = $handler($task);
            $this->emit(new TaskCompleted($task, $result));
        } catch (Throwable $exception) {
            $this->emit(new TaskFailed($task, $exception));

            // Retry logic
            if ($task->canRetry()) {
                $retryTask = $task->withRetry();
                $this->schedule($retryTask);
            }
        }
    }
}
