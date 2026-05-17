<?php

declare(strict_types=1);

namespace Infrastructure\Events;

use Application\Contracts\EventDispatcherInterface;
use Illuminate\Contracts\Events\Dispatcher;

final class LaravelEventDispatcher implements EventDispatcherInterface
{
    public function __construct(
        private readonly Dispatcher $dispatcher,
    ) {
    }

    public function dispatch(object $event): void
    {
        $this->dispatcher->dispatch($event);
    }
}
