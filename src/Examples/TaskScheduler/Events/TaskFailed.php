<?php

declare(strict_types=1);

namespace JustSteveKing\Mit\Examples\TaskScheduler\Events;

use JustSteveKing\Mit\Event;
use JustSteveKing\Mit\Examples\TaskScheduler\Task;
use Throwable;

/**
 * @extends Event<Task>
 */
final class TaskFailed extends Event
{
    public function __construct(
        public readonly Task $task,
        public readonly Throwable $exception,
    ) {
        parent::__construct($task);
    }
}
