<?php

declare(strict_types=1);

namespace Application\Contracts;

interface ScoreProcessingQueueInterface
{
    public function dispatch(int $contactId): void;
}
