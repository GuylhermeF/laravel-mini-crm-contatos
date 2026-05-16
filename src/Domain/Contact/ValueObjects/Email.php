<?php

declare(strict_types=1);

namespace Domain\Contact\ValueObjects;

use Domain\Contact\Exceptions\InvalidEmailException;

final class Email
{
    private readonly string $value;

    private const PERSONAL_DOMAINS = ['gmail.com', 'hotmail.com', 'yahoo.com'];

    public function __construct(string $email)
    {
        $email = strtolower(trim($email));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidEmailException("Invalid email address: {$email}");
        }

        $this->value = $email;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function domain(): string
    {
        return substr($this->value, strpos($this->value, '@') + 1);
    }

    public function isCorporate(): bool
    {
        return !in_array($this->domain(), self::PERSONAL_DOMAINS, true);
    }

    public function isBrazilian(): bool
    {
        return str_ends_with($this->domain(), '.br');
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
