<?php

declare(strict_types=1);

namespace Domain\Contact\ValueObjects;

use Domain\Contact\Exceptions\InvalidNameException;

final class ContactName
{
    private readonly string $value;

    public function __construct(string $name)
    {
        $name = trim($name);

        if (empty($name)) {
            throw new InvalidNameException("Name cannot be empty.");
        }

        if (strlen($name) < 2) {
            throw new InvalidNameException("Name must have at least 2 characters.");
        }

        $this->value = $name;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function isFullName(): bool
    {
        $parts = array_filter(explode(' ', $this->value));
        return count($parts) > 1;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
