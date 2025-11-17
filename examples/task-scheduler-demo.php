<?php

declare(strict_types=1);

use JustSteveKing\Mit\EventDispatcher;
use JustSteveKing\Mit\Examples\TaskScheduler\Listeners\TaskLogger;
use JustSteveKing\Mit\Examples\TaskScheduler\Listeners\TaskMetrics;
use JustSteveKing\Mit\Examples\TaskScheduler\Scheduler;
use JustSteveKing\Mit\Examples\TaskScheduler\Task;
use JustSteveKing\Mit\ListenerProvider;

require_once __DIR__ . '/../vendor/autoload.php';

// Set up the event system
$provider = new ListenerProvider();
$dispatcher = new EventDispatcher($provider);

// Create listeners
$logger = new TaskLogger();
$metrics = new TaskMetrics();

// Register listeners using first-class callable syntax
$provider->listen($logger->onTaskScheduled(...));
$provider->listen($logger->onTaskStarted(...));
$provider->listen($logger->onTaskCompleted(...));
$provider->listen($logger->onTaskFailed(...));

$provider->listen($metrics->onTaskScheduled(...));
$provider->listen($metrics->onTaskCompleted(...));
$provider->listen($metrics->onTaskFailed(...));

// Create scheduler
$scheduler = new Scheduler();
$scheduler->setEventDispatcher($dispatcher);

// Register task handlers
$scheduler->registerHandler('send_email', static function (Task $task): void {
    echo "Sending email to: {$task->payload['email']}\n";
    // Simulate sending email
    usleep(100000); // 100ms
});

$scheduler->registerHandler('process_payment', static function (Task $task): array {
    if (random_int(0, 10) > 7) {
        throw new RuntimeException('Payment processing failed');
    }

    echo "Processing payment: \${$task->payload['amount']}\n";

    return ['transaction_id' => uniqid('txn_', true)];
});

$scheduler->registerHandler('generate_report', static function (Task $task): void {
    echo "Generating {$task->payload['type']} report\n";
    usleep(200000); // 200ms
});

// Schedule some tasks
$now = new DateTimeImmutable();

$scheduler->schedule(new Task(
    id: 'task-1',
    name: 'Send welcome email',
    handler: 'send_email',
    scheduledAt: $now,
    payload: ['email' => 'user@example.com'],
));

$scheduler->schedule(new Task(
    id: 'task-2',
    name: 'Process subscription payment',
    handler: 'process_payment',
    scheduledAt: $now,
    payload: ['amount' => 29.99],
    maxRetries: 5,
));

$scheduler->schedule(new Task(
    id: 'task-3',
    name: 'Generate monthly report',
    handler: 'generate_report',
    scheduledAt: $now,
    payload: ['type' => 'monthly'],
));

$scheduler->schedule(new Task(
    id: 'task-4',
    name: 'Another payment',
    handler: 'process_payment',
    scheduledAt: $now,
    payload: ['amount' => 99.99],
    maxRetries: 3,
));

// Process all due tasks
echo "Processing scheduled tasks...\n\n";
$scheduler->processDueTasks($now);

// Display results
echo "\n=== Task Logs ===\n";
foreach ($logger->getLogs() as $log) {
    echo $log . "\n";
}

echo "\n=== Metrics ===\n";
$metrics = $metrics->getMetrics();
foreach ($metrics as $key => $value) {
    echo ucfirst(str_replace('_', ' ', $key)) . ": {$value}\n";
}

echo "\n=== Pending Tasks ===\n";
echo "Remaining in queue: {$scheduler->getPendingCount()}\n";
