<?php

declare(strict_types=1);

namespace Domain\Contact\ValueObjects;

use Domain\Contact\Exceptions\InvalidPhoneException;

final class Phone
{
    private readonly string $value;

    private const SAO_PAULO_AREA_CODES = ['11', '12', '13', '14', '15', '16', '17', '18', '19'];

    public function __construct(string $phone)
    {
        $normalized = $this->normalize($phone);

        if (!$this->isValid($normalized)) {
            throw new InvalidPhoneException("Invalid phone number: {$phone}");
        }

        $this->value = $normalized;
    }

    private function normalize(string $phone): string
    {
        return preg_replace('/\D/', '', $phone);
    }

    private function isValid(string $phone): bool
    {
        // Must have 10 or 11 digits (with area code)
        return strlen($phone) >= 10 && strlen($phone) <= 11;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function formatted(): string
    {
        $digits = $this->value;

        if (strlen($digits) === 11) {
            return sprintf(
                '(%s) %s-%s',
                substr($digits, 0, 2),
                substr($digits, 2, 5),
                substr($digits, 7)
            );
        }

        return sprintf(
            '(%s) %s-%s',
            substr($digits, 0, 2),
            substr($digits, 2, 4),
            substr($digits, 6)
        );
    }

    public function areaCode(): string
    {
        return substr($this->value, 0, 2);
    }

    public function isSaoPauloAreaCode(): bool
    {
        return in_array($this->areaCode(), self::SAO_PAULO_AREA_CODES, true);
    }

    public function hasAreaCode(): bool
    {
        return strlen($this->value) >= 10;
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
