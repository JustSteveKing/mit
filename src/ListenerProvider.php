<?php

declare(strict_types=1);

namespace JustSteveKing\Mit;

use InvalidArgumentException;
use Override;
use Psr\EventDispatcher\ListenerProviderInterface;
use ReflectionException;
use ReflectionFunction;
use ReflectionNamedType;

/**
 * Default implementation of PSR-14 Listener Provider.
 *
 * Supports type-safe listener registration using first-class callables.
 */
final class ListenerProvider implements ListenerProviderInterface
{
    /**
     * @var array<class-string, array<array{callable, int}>>
     */
    private array $listeners = [];

    /**
     * @var array<class-string, list<class-string>>
     */
    private array $typeHierarchyCache = [];

    /**
     * Register a listener for a specific event type.
     *
     * @param  class-string  $eventType
     * @param  callable(object): void  $listener
     * @param  int  $priority  Higher priorities are called first (default: 0)
     */
    public function on(string $eventType, callable $listener, int $priority = 0): void
    {
        $this->listeners[$eventType] ??= [];
        $this->listeners[$eventType][] = [$listener, $priority];

        // Sort by priority (descending)
        usort($this->listeners[$eventType], static fn($a, $b): int => $b[1] <=> $a[1]);
    }

    /**
     * Register a listener using first-class callable syntax.
     * Automatically detects event type from callable signature.
     *
     * @param  callable(object): void  $listener
     */
    public function listen(callable $listener, int $priority = 0): void
    {
        $eventType = $this->detectEventType($listener);
        $this->on($eventType, $listener, $priority);
    }

    /**
     * Register a one-time listener that will be called only once.
     *
     * @param  class-string  $eventType
     * @param  callable(object): void  $listener
     */
    public function once(string $eventType, callable $listener, int $priority = 0): void
    {
        $wrapper = function (object $event) use ($eventType, $listener, &$wrapper): void {
            $listener($event);
            $this->off($eventType, $wrapper);
        };

        $this->on($eventType, $wrapper, $priority);
    }

    /**
     * Remove a listener for a specific event type.
     *
     * @param  class-string  $eventType
     * @param  callable(object): void  $listener
     */
    public function off(string $eventType, callable $listener): void
    {
        if ( ! isset($this->listeners[$eventType])) {
            return;
        }

        $this->listeners[$eventType] = array_filter(
            $this->listeners[$eventType],
            static fn($item): bool => $item[0] !== $listener,
        );
    }

    /**
     * Remove all listeners for a specific event type.
     *
     * @param  class-string|null  $eventType  If null, removes all listeners
     */
    public function clear(?string $eventType = null): void
    {
        if (null === $eventType) {
            $this->listeners = [];
            $this->typeHierarchyCache = [];

            return;
        }

        unset($this->listeners[$eventType]);
    }

    /**
     * Get all listeners for an event.
     *
     * @return iterable<callable>
     */
    #[Override]
    public function getListenersForEvent(object $event): iterable
    {
        $eventClass = $event::class;

        foreach ($this->getTypeHierarchy($eventClass) as $type) {
            if ( ! isset($this->listeners[$type])) {
                continue;
            }

            foreach ($this->listeners[$type] as [$listener, $_priority]) {
                yield $listener;
            }
        }
    }

    /**
     * Detect an event type from a callable signature using reflection.
     *
     * @return class-string
     *
     * @throws ReflectionException
     */
    private function detectEventType(callable $listener): string
    {
        $reflection = new ReflectionFunction($listener(...));
        $parameters = $reflection->getParameters();

        if (1 !== count($parameters)) {
            throw new InvalidArgumentException(
                'Listener must have exactly one parameter.',
            );
        }

        $type = $parameters[0]->getType();

        if ( ! $type instanceof ReflectionNamedType || $type->isBuiltin()) {
            throw new InvalidArgumentException(
                'Listener parameter must be a typed object.',
            );
        }

        /** @var class-string */
        return $type->getName();
    }

    /**
     * Get type hierarchy for an event class (class + all parent classes + interfaces).
     *
     * @param  class-string  $eventClass
     * @return list<class-string>
     */
    private function getTypeHierarchy(string $eventClass): array
    {
        if (isset($this->typeHierarchyCache[$eventClass])) {
            return $this->typeHierarchyCache[$eventClass];
        }

        $hierarchy = [$eventClass];

        // Add parent classes
        $parent = $eventClass;
        while ($parent = get_parent_class($parent)) {
            $hierarchy[] = $parent;
        }

        // Add interfaces
        foreach (class_implements($eventClass) ?: [] as $interface) {
            $hierarchy[] = $interface;
        }

        $this->typeHierarchyCache[$eventClass] = $hierarchy;

        return $hierarchy;
    }
}
