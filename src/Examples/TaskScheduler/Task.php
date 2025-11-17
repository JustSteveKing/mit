<?php

declare(strict_types=1);

namespace JustSteveKing\Mit\Examples\TaskScheduler;

use DateTimeImmutable;

/**
 * Represents a scheduled task.
 */
final readonly class Task
{
    /**
     * @param string $id
     * @param string $name
     * @param string $handler
     * @param DateTimeImmutable $scheduledAt
     * @param array<string, mixed> $payload
     * @param int $maxRetries
     */
    public function __construct(
        public string $id,
        public string $name,
        public string $handler,
        public DateTimeImmutable $scheduledAt,
        public array $payload = [],
        public int $maxRetries = 3,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        return $this->payload;
    }

    public function withRetry(): self
    {
        return new self(
            id: $this->id,
            name: $this->name,
            handler: $this->handler,
            scheduledAt: $this->scheduledAt,
            payload: $this->payload,
            maxRetries: $this->maxRetries - 1,
        );
    }

    public function canRetry(): bool
    {
        return $this->maxRetries > 0;
    }
}
