<?php

declare(strict_types=1);

namespace Domain\Contact\Services\ScoreStrategies;

use Domain\Contact\Entities\Contact;

final class EmailScoreStrategy implements ScoreStrategyInterface
{
    public function calculate(Contact $contact): int
    {
        $points = 0;
        $email = $contact->email();

        if ($email->isCorporate()) {
            $points += 20;
        }

        if ($email->isBrazilian()) {
            $points += 10;
        }

        return $points;
    }
}
