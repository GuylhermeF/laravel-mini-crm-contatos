<?php

declare(strict_types=1);

namespace Application\UseCases\Contact;

use Domain\Contact\Repositories\ContactRepositoryInterface;

final class ListContactsUseCase
{
    public function __construct(
        private readonly ContactRepositoryInterface $contactRepository,
    ) {
    }

    public function execute(int $page = 1, int $perPage = 15): array
    {
        return $this->contactRepository->paginate($page, $perPage);
    }
}
