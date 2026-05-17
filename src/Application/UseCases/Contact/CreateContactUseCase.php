<?php

declare(strict_types=1);

namespace Application\UseCases\Contact;

use Application\DTOs\CreateContactDTO;
use Domain\Contact\Entities\Contact;
use Domain\Contact\Exceptions\InvalidEmailException;
use Domain\Contact\Repositories\ContactRepositoryInterface;
use Domain\Contact\ValueObjects\ContactName;
use Domain\Contact\ValueObjects\Email;
use Domain\Contact\ValueObjects\Phone;

final class CreateContactUseCase
{
    public function __construct(
        private readonly ContactRepositoryInterface $contactRepository,
    ) {
    }

    public function execute(CreateContactDTO $dto): Contact
    {
        $existingContact = $this->contactRepository->findByEmail($dto->email);

        if ($existingContact !== null) {
            throw new \DomainException("A contact with this email already exists.");
        }

        $contact = Contact::create(
            name: new ContactName($dto->name),
            email: new Email($dto->email),
            phone: new Phone($dto->phone),
        );

        return $this->contactRepository->save($contact);
    }
}
