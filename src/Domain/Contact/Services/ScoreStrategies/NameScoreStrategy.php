<?php

declare(strict_types=1);

namespace Domain\Contact\Services\ScoreStrategies;

use Domain\Contact\Entities\Contact;

final class NameScoreStrategy implements ScoreStrategyInterface
{
    public function calculate(Contact $contact): int
    {
        if ($contact->name()->isFullName()) {
            return 10;
        }

        return 0;
    }
}
