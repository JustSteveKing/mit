<?php

declare(strict_types=1);

namespace JustSteveKing\Mit;

/**
 * Base event class providing common functionality for events.
 *
 * @template TPayload
 */
abstract class Event
{
    private bool $propagationStopped = false;

    /**
     * @param  TPayload  $payload
     */
    public function __construct(
        private readonly mixed $payload = null,
    ) {}

    /**
     * @return TPayload
     */
    public function payload(): mixed
    {
        return $this->payload;
    }

    public function stopPropagation(): void
    {
        $this->propagationStopped = true;
    }

    public function isPropagationStopped(): bool
    {
        return $this->propagationStopped;
    }
}
