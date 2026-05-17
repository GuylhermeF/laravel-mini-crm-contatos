<?php

declare(strict_types=1);

namespace Domain\Contact\Services\ScoreStrategies;

use Domain\Contact\Entities\Contact;

final class PhoneScoreStrategy implements ScoreStrategyInterface
{
    public function calculate(Contact $contact): int
    {
        $phone = $contact->phone();

        if (!$phone->hasAreaCode()) {
            return 0;
        }

        if ($phone->isSaoPauloAreaCode()) {
            return 20;
        }

        return 10;
    }
}
