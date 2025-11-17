# Mit - PSR-14 Event Dispatcher (Type-safe)

A lightweight, type-safe, PSR-14 compliant event dispatcher for modern PHP.

Designed for projects that need a small, well-typed event/message system with first-class callable support, priority ordering, one-time listeners, and propagation control.

[![CI](https://github.com/juststeveking/mit/actions/workflows/ci.yml/badge.svg)](https://github.com/juststeveking/mit/actions/workflows/ci.yml) [![Release](https://github.com/juststeveking/mit/actions/workflows/release.yml/badge.svg)](https://github.com/juststeveking/mit/actions/workflows/release.yml) [![Packagist](https://img.shields.io/packagist/v/juststeveking/mit)](https://packagist.org/packages/juststeveking/mit) [![Codecov](https://codecov.io/gh/juststeveking/mit/branch/main/graph/badge.svg?token=)](https://codecov.io/gh/juststeveking/mit) [![PHP](https://img.shields.io/badge/php-%5E8.1-8892BF)](https://www.php.net/)

Quick links
- Documentation: README (this file)
- Source: src/
- Tests: tests/
- Examples: examples/

## Requirements
- PHP 8.1 or later
- Composer

## Installation

Install via Composer:

```bash
composer require juststeveking/mit
```

## Quick start

```php
use JustSteveKing\Mit\EventDispatcher;
use JustSteveKing\Mit\ListenerProvider;
use JustSteveKing\Mit\Event;

final class UserRegistered extends Event
{
    public function __construct(
        public readonly string $userId,
        public readonly string $email,
    ) {
        parent::__construct();
    }
}

$provider = new ListenerProvider();
$dispatcher = new EventDispatcher($provider);

// Register a listener using a first-class callable
$provider->listen(function (UserRegistered $event): void {
    echo "Welcome {$event->email}!\n";
});

$dispatcher->dispatch(new UserRegistered('123', 'user@example.com'));
```

Examples
- `examples/task-scheduler-demo.php` — small demo showing scheduling and dispatch.

## Core features
- PSR-14 compliant Event Dispatcher and Listener Provider
- Type-safe: listeners are matched by type-hinted event parameter
- First-class callable support (e.g. `$object->method(...)`)
- Priority-based listener ordering
- One-time listeners (`once`) and removal (`off` / `clear`)
- Respect for stoppable propagation (PSR `StoppableEventInterface`)
- Type hierarchy support: listeners registered for a parent class or interface receive subtype events

## Usage examples

### Priority ordering

```php
$provider->on(SomeEvent::class, fn(SomeEvent $e) => print('low'), priority: -10);
$provider->on(SomeEvent::class, fn(SomeEvent $e) => print('high'), priority: 10);
// high runs before low
```

### One-time listeners

```php
$provider->once(SomeEvent::class, fn(SomeEvent $e) => print('called once'));
```

### First-class callables

```php
$handler = new class {
    public function handle(SomeEvent $e): void { /* ... */ }
};

$provider->listen($handler->handle(...));
```

### Stopping propagation

Implement `Psr\EventDispatcher\StoppableEventInterface` (or extend the provided `StoppableEvent` helper) and stop propagation in a listener to prevent later listeners from being invoked.

## API summary

- `JustSteveKing\Mit\EventDispatcher` — PSR-14 dispatcher; `dispatch(object $event): object`
- `JustSteveKing\Mit\ListenerProvider` — type-safe listener registry
  - `on(string $eventType, callable $listener, int $priority = 0): void` — register listener for type
  - `listen(callable $listener, int $priority = 0): void` — detect event type from callable parameter
  - `once(string $eventType, callable $listener, int $priority = 0): void` — one-time listener
  - `off(string $eventType, callable $listener): void` — remove listener
  - `clear(?string $eventType = null): void` — clear listeners
- `JustSteveKing\Mit\Event` — simple event base class
- `JustSteveKing\Mit\StoppableEvent` — event that implements stoppable propagation

## Testing and quality

Run the test suite and static analysis locally:

```bash
# Install dependencies
composer install --no-interaction --prefer-dist

# Run phpunit
vendor/bin/phpunit

# Run phpstan (static analysis)
vendor/bin/phpstan analyse

# Run pint (style test)
vendor/bin/pint --test
```

## Contributing

Contributions welcome. Please open issues for bugs or feature requests and submit pull requests with tests. Follow the repository coding standards (PSR-12), include unit tests for new behavior, and keep changes small and focused.

## Versioning & Releases

This project follows semantic versioning. Releases and changelogs appear on GitHub.

Automated releases

- Tags following the `vX.Y.Z` pattern will trigger the release workflow which runs tests and publishes a GitHub Release.
- To enable automatic Packagist updates on release, add a repository secret named `PACKAGIST_TOKEN` containing your Packagist API token. The release workflow will notify Packagist when the secret is present.

## Security

If you discover a security vulnerability, please open a private issue or contact the maintainer directly. Do not disclose sensitive details publicly until a fix is available.

## License

MIT — see the LICENSE file for details.

## Support

If you need help using the library, open an issue with a minimal reproducible example and the PHP version you're using.
