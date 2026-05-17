<?php

declare(strict_types=1);

namespace Application\DTOs;

final class UpdateContactDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $email,
        public readonly string $phone,
    ) {
    }
}
