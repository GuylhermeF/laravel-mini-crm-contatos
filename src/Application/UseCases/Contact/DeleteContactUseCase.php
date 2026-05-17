<?php

declare(strict_types=1);

namespace Application\UseCases\Contact;

use Domain\Contact\Exceptions\ContactNotFoundException;
use Domain\Contact\Repositories\ContactRepositoryInterface;

final class DeleteContactUseCase
{
    public function __construct(
        private readonly ContactRepositoryInterface $contactRepository,
    ) {
    }

    public function execute(int $id): void
    {
        $contact = $this->contactRepository->findById($id);

        if ($contact === null) {
            throw new ContactNotFoundException($id);
        }

        $this->contactRepository->delete($id);
    }
}
