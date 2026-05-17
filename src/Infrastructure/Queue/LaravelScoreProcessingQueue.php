<?php

declare(strict_types=1);

namespace Infrastructure\Queue;

use Application\Contracts\ScoreProcessingQueueInterface;
use Infrastructure\Queue\Jobs\ProcessContactScoreJob;

final class LaravelScoreProcessingQueue implements ScoreProcessingQueueInterface
{
    public function dispatch(int $contactId): void
    {
        ProcessContactScoreJob::dispatch($contactId);
    }
}
