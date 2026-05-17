<?php

declare(strict_types=1);

namespace Domain\Contact\Repositories;

use Domain\Contact\Entities\Contact;

interface ContactRepositoryInterface
{
    public function findById(int $id): ?Contact;

    public function findByEmail(string $email): ?Contact;

    public function save(Contact $contact): Contact;

    public function delete(int $id): void;

    /**
     * @return array{data: Contact[], meta: array{total: int, per_page: int, current_page: int, last_page: int}}
     */
    public function paginate(int $page = 1, int $perPage = 15): array;
}
