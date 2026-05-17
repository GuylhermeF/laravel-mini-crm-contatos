<?php

declare(strict_types=1);

namespace Domain\Contact\Exceptions;

use RuntimeException;

class ContactNotFoundException extends RuntimeException
{
    public function __construct(int $id)
    {
        parent::__construct("Contact with ID {$id} not found.");
    }
}
