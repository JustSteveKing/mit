<?php

declare(strict_types=1);

namespace JustSteveKing\Mit;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\EventDispatcher\StoppableEventInterface;

/**
 * PSR-14 compliant event dispatcher.
 *
 * Dispatches events to registered listeners and respects event propagation stopping.
 */
final readonly class EventDispatcher implements EventDispatcherInterface
{
    public function __construct(
        private ListenerProviderInterface $listenerProvider,
    ) {}

    /**
     * Dispatch an event to all registered listeners.
     *
     * @template T of object
     *
     * @param  T  $event
     * @return T
     */
    public function dispatch(object $event): object
    {
        /** @var iterable<callable> $listeners */
        $listeners = $this->listenerProvider->getListenersForEvent($event);

        foreach ($listeners as $listener) {
            if ($event instanceof StoppableEventInterface && $event->isPropagationStopped()) {
                break;
            }

            $listener($event);
        }

        return $event;
    }
}
