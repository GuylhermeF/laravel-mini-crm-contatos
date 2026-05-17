<?php

declare(strict_types=1);

namespace Application\UseCases\Contact;

use Domain\Contact\Entities\Contact;
use Domain\Contact\Exceptions\ContactNotFoundException;
use Domain\Contact\Repositories\ContactRepositoryInterface;

final class GetContactUseCase
{
    public function __construct(
        private readonly ContactRepositoryInterface $contactRepository,
    ) {
    }

    public function execute(int $id): Contact
    {
        $contact = $this->contactRepository->findById($id);

        if ($contact === null) {
            throw new ContactNotFoundException($id);
        }

        return $contact;
    }
}
