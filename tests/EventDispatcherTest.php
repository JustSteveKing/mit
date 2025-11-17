<?php

declare(strict_types=1);

namespace JustSteveKing\Mit\Tests;

use JustSteveKing\Mit\Event;
use JustSteveKing\Mit\EventDispatcher;
use JustSteveKing\Mit\ListenerProvider;
use JustSteveKing\Mit\StoppableEvent;
use PHPUnit\Framework\TestCase;

final class EventDispatcherTest extends TestCase
{
    private ListenerProvider $provider;

    private EventDispatcher $dispatcher;

    protected function setUp(): void
    {
        $this->provider = new ListenerProvider();
        $this->dispatcher = new EventDispatcher($this->provider);
    }

    public function test_dispatches_event_to_registered_listeners(): void
    {
        $called = false;

        $this->provider->on(TestEvent::class, static function (TestEvent $event) use (&$called): void {
            $called = true;
        });

        $event = new TestEvent('test');
        $this->dispatcher->dispatch($event);

        $this->assertTrue($called);
    }

    public function test_returns_same_event_instance(): void
    {
        $this->provider->on(TestEvent::class, static function (): void {});

        $event = new TestEvent('test');
        $returned = $this->dispatcher->dispatch($event);

        $this->assertSame($event, $returned);
    }

    public function test_listeners_called_in_priority_order(): void
    {
        $order = [];

        $this->provider->on(TestEvent::class, static function () use (&$order): void {
            $order[] = 'low';
        }, priority: -10);

        $this->provider->on(TestEvent::class, static function () use (&$order): void {
            $order[] = 'high';
        }, priority: 10);

        $this->provider->on(TestEvent::class, static function () use (&$order): void {
            $order[] = 'medium';
        }, priority: 0);

        $this->dispatcher->dispatch(new TestEvent('test'));

        $this->assertSame(['high', 'medium', 'low'], $order);
    }

    public function test_stops_propagation_for_stoppable_events(): void
    {
        $calls = 0;

        $this->provider->on(TestStoppableEvent::class, static function (TestStoppableEvent $event) use (&$calls): void {
            $calls++;
            $event->stopPropagation();
        }, priority: 10);

        $this->provider->on(TestStoppableEvent::class, static function () use (&$calls): void {
            $calls++;
        }, priority: 0);

        $event = new TestStoppableEvent('test');
        $this->dispatcher->dispatch($event);

        $this->assertSame(1, $calls);
        $this->assertTrue($event->isPropagationStopped());
    }

    public function test_first_class_callable_syntax(): void
    {
        $listener = new class () {
            public int $callCount = 0;

            public function handle(TestEvent $event): void
            {
                $this->callCount++;
            }
        };

        $this->provider->listen($listener->handle(...));
        $this->dispatcher->dispatch(new TestEvent('test'));

        $this->assertSame(1, $listener->callCount);
    }

    public function test_once_listener_called_only_once(): void
    {
        $calls = 0;

        $this->provider->once(TestEvent::class, static function () use (&$calls): void {
            $calls++;
        });

        $this->dispatcher->dispatch(new TestEvent('first'));
        $this->dispatcher->dispatch(new TestEvent('second'));

        $this->assertSame(1, $calls);
    }

    public function test_off_removes_listener(): void
    {
        $calls = 0;

        $listener = static function () use (&$calls): void {
            $calls++;
        };

        $this->provider->on(TestEvent::class, $listener);
        $this->dispatcher->dispatch(new TestEvent('first'));

        $this->provider->off(TestEvent::class, $listener);
        $this->dispatcher->dispatch(new TestEvent('second'));

        $this->assertSame(1, $calls);
    }

    public function test_clear_removes_all_listeners(): void
    {
        $calls = 0;

        $this->provider->on(TestEvent::class, static function () use (&$calls): void {
            $calls++;
        });

        $this->provider->on(TestEvent::class, static function () use (&$calls): void {
            $calls++;
        });

        $this->dispatcher->dispatch(new TestEvent('first'));
        $this->assertSame(2, $calls);

        $this->provider->clear(TestEvent::class);
        $this->dispatcher->dispatch(new TestEvent('second'));

        $this->assertSame(2, $calls); // No new calls
    }

    public function test_listeners_receive_parent_type_events(): void
    {
        $called = false;

        $this->provider->on(TestEvent::class, static function (TestEvent $event) use (&$called): void {
            $called = true;
        });

        $this->dispatcher->dispatch(new TestChildEvent('child'));

        $this->assertTrue($called);
    }
}

/**
 * @extends Event<string>
 */
class TestEvent extends Event
{
    public function __construct(public readonly string $value)
    {
        parent::__construct($value);
    }
}

/**
 * @extends StoppableEvent<string>
 */
class TestStoppableEvent extends StoppableEvent
{
    public function __construct(public readonly string $value)
    {
        parent::__construct($value);
    }
}

final class TestChildEvent extends TestEvent {}
