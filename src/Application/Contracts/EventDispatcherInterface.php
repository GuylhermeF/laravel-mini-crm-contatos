<?php

declare(strict_types=1);

namespace Application\Contracts;

interface EventDispatcherInterface
{
    public function dispatch(object $event): void;
}
