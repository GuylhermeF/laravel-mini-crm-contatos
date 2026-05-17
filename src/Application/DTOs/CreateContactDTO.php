<?php

declare(strict_types=1);

namespace Application\DTOs;

final class CreateContactDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $phone,
    ) {
    }
}
