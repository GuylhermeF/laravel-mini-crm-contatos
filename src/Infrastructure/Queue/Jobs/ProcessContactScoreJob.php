<?php

declare(strict_types=1);

namespace Infrastructure\Queue\Jobs;

use Application\UseCases\Contact\ProcessContactScoreUseCase;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

final class ProcessContactScoreJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;
    public int $timeout = 60;

    public function __construct(
        public readonly int $contactId,
    ) {
    }

    public function handle(ProcessContactScoreUseCase $useCase): void
    {
        try {
            $useCase->execute($this->contactId);
        } catch (\Throwable $e) {
            Log::channel('contact')->error("Failed to process score for contact {$this->contactId}: {$e->getMessage()}");
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::channel('contact')->error(
            "Job failed for contact {$this->contactId}: {$exception->getMessage()}"
        );
    }
}
