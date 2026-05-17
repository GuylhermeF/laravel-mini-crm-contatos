<?php

declare(strict_types=1);

namespace Domain\Contact\Events;

use Domain\Contact\Entities\Contact;

final class ContactScoreProcessed
{
    public function __construct(
        public readonly Contact $contact,
    ) {
    }
}
