# Examples

This page contains runnable examples demonstrating typical usage patterns and integration points.

## Task scheduler demo

See `examples/task-scheduler-demo.php` for a runnable demonstration. In short:

```php
// schedule a task
$task = new \JustSteveKing\Mit\Examples\TaskScheduler\Task(
    id: uniqid('', true),
    name: 'send-email',
    handler: 'email:send',
    scheduledAt: new \DateTimeImmutable('+1 minute'),
    payload: ['to' => 'user@example.com']
);

// register a handler
$provider->on(\JustSteveKing\Mit\Examples\TaskScheduler\Task::class, function ($task) {
    // run the job
});
```

## First-class callable example

```php
$handler = new class {
    public function handle(SomeEvent $e): void { /* ... */ }
};
$provider->listen($handler->handle(...));
```

