<?php

declare(strict_types=1);

namespace JustSteveKing\Mit;

/**
 * Trait for classes that emit events.
 *
 * Provides convenient methods for dispatching events.
 */
trait EmitsEvents
{
    private ?EventDispatcher $eventDispatcher = null;

    public function setEventDispatcher(EventDispatcher $dispatcher): void
    {
        $this->eventDispatcher = $dispatcher;
    }

    /**
     * Dispatch an event if a dispatcher is configured.
     *
     * @template T of object
     *
     * @param  T  $event
     * @return T
     */
    protected function emit(object $event): object
    {
        if (null === $this->eventDispatcher) {
            return $event;
        }

        return $this->eventDispatcher->dispatch($event);
    }

    /**
     * Check if an event dispatcher is configured.
     */
    protected function hasEventDispatcher(): bool
    {
        return null !== $this->eventDispatcher;
    }
}
