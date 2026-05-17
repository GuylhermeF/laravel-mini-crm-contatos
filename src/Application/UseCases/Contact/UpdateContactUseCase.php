<?php

declare(strict_types=1);

namespace Application\UseCases\Contact;

use Application\DTOs\UpdateContactDTO;
use Domain\Contact\Entities\Contact;
use Domain\Contact\Exceptions\ContactNotFoundException;
use Domain\Contact\Repositories\ContactRepositoryInterface;
use Domain\Contact\ValueObjects\ContactName;
use Domain\Contact\ValueObjects\Email;
use Domain\Contact\ValueObjects\Phone;

final class UpdateContactUseCase
{
    public function __construct(
        private readonly ContactRepositoryInterface $contactRepository,
    ) {
    }

    public function execute(UpdateContactDTO $dto): Contact
    {
        $contact = $this->contactRepository->findById($dto->id);

        if ($contact === null) {
            throw new ContactNotFoundException($dto->id);
        }

        // Check if email is being changed to one that already exists
        $existingWithEmail = $this->contactRepository->findByEmail($dto->email);
        if ($existingWithEmail !== null && $existingWithEmail->id() !== $dto->id) {
            throw new \DomainException("A contact with this email already exists.");
        }

        $contact->update(
            name: new ContactName($dto->name),
            email: new Email($dto->email),
            phone: new Phone($dto->phone),
        );

        return $this->contactRepository->save($contact);
    }
}
