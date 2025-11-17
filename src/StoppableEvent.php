<?php

declare(strict_types=1);

namespace JustSteveKing\Mit;

use Psr\EventDispatcher\StoppableEventInterface;

/**
 * An event that can stop propagation to subsequent listeners.
 *
 * @template TPayload
 *
 * @extends Event<TPayload>
 */
abstract class StoppableEvent extends Event implements StoppableEventInterface {}
