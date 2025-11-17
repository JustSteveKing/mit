<?php

declare(strict_types=1);

namespace JustSteveKing\Mit\Examples\TaskScheduler\Events;

use JustSteveKing\Mit\Event;
use JustSteveKing\Mit\Examples\TaskScheduler\Task;

/**
 * @extends Event<Task>
 */
final class TaskStarted extends Event
{
    public function __construct(
        public readonly Task $task,
    ) {
        parent::__construct($task);
    }
}
