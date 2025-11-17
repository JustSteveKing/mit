<?php

declare(strict_types=1);

namespace JustSteveKing\Mit\Examples\TaskScheduler\Events;

use JustSteveKing\Mit\Event;
use JustSteveKing\Mit\Examples\TaskScheduler\Task;

/**
 * @extends Event<Task>
 */
final class TaskCompleted extends Event
{
    public function __construct(
        public readonly Task $task,
        public readonly mixed $result = null,
    ) {
        parent::__construct($task);
    }
}
