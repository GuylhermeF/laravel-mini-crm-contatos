<?php

declare(strict_types=1);

namespace Application\UseCases\Contact;

use Application\Contracts\ScoreProcessingQueueInterface;
use Domain\Contact\Enums\ContactStatus;
use Domain\Contact\Exceptions\ContactNotFoundException;
use Domain\Contact\Exceptions\ContactNotProcessableException;
use Domain\Contact\Repositories\ContactRepositoryInterface;

final class TriggerScoreProcessingUseCase
{
    public function __construct(
        private readonly ContactRepositoryInterface $contactRepository,
        private readonly ScoreProcessingQueueInterface $queue,
    ) {
    }

    public function execute(int $contactId): void
    {
        $contact = $this->contactRepository->findById($contactId);

        if ($contact === null) {
            throw new ContactNotFoundException($contactId);
        }

        if (!$contact->status()->canTransitionTo(ContactStatus::Processing)) {
            throw new ContactNotProcessableException(
                "Contact {$contactId} cannot be processed in status: {$contact->status()->value}"
            );
        }

        $this->queue->dispatch($contactId);
    }
}
