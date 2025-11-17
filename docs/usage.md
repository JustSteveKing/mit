# Usage

This page expands on the quick start in the README with more detailed usage patterns and examples.

## Basic setup

```php
use JustSteveKing\Mit\EventDispatcher;
use JustSteveKing\Mit\ListenerProvider;

$provider = new ListenerProvider();
$dispatcher = new EventDispatcher($provider);
```

## Registering listeners

- `on(string $eventType, callable $listener, int $priority = 0)` — registers a listener for an event class/type.
- `listen(callable $listener, int $priority = 0)` — detects the event type from the callable signature.
- `once(string $eventType, callable $listener, int $priority = 0)` — a one-time listener.

## Stoppable events

Extend `StoppableEvent` or implement `Psr\EventDispatcher\StoppableEventInterface` and call `stopPropagation()` inside a listener to prevent later listeners from running.

## Example: priority ordering

```php
$provider->on(SomeEvent::class, fn(SomeEvent $e) => print('low'), priority: -10);
$provider->on(SomeEvent::class, fn(SomeEvent $e) => print('high'), priority: 10);
```

