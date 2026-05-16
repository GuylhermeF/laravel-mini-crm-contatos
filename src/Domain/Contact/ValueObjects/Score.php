<?php

declare(strict_types=1);

namespace Domain\Contact\ValueObjects;

use Domain\Contact\Exceptions\InvalidScoreException;

final class Score
{
    private readonly int $value;

    public function __construct(int $value = 0)
    {
        if ($value < 0) {
            throw new InvalidScoreException("Score cannot be negative.");
        }

        $this->value = $value;
    }

    public function value(): int
    {
        return $this->value;
    }

    public function add(int $points): self
    {
        return new self($this->value + $points);
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }
}
