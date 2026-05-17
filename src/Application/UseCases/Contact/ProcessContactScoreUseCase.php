<?php

declare(strict_types=1);

namespace Application\UseCases\Contact;

use Application\Contracts\EventDispatcherInterface;
use Domain\Contact\Entities\Contact;
use Domain\Contact\Events\ContactScoreProcessed;
use Domain\Contact\Exceptions\ContactNotFoundException;
use Domain\Contact\Repositories\ContactRepositoryInterface;
use Domain\Contact\Services\ScoreCalculatorServiceInterface;

final class ProcessContactScoreUseCase
{
    public function __construct(
        private readonly ContactRepositoryInterface $contactRepository,
        private readonly ScoreCalculatorServiceInterface $scoreCalculator,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function execute(int $contactId): Contact
    {
        $contact = $this->contactRepository->findById($contactId);

        if ($contact === null) {
            throw new ContactNotFoundException($contactId);
        }

        try {
            $contact->startProcessing();
            $this->contactRepository->save($contact);

            // Simulate processing load
            sleep(1);

            $score = $this->scoreCalculator->calculate($contact);
            $contact->completeProcessing($score);

            $savedContact = $this->contactRepository->save($contact);

            $this->eventDispatcher->dispatch(new ContactScoreProcessed($savedContact));

            return $savedContact;
        } catch (\Throwable $e) {
            if ($contact->isProcessing()) {
                $contact->failProcessing();
                $this->contactRepository->save($contact);
            }

            throw $e;
        }
    }
}
