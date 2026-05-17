<?php

declare(strict_types=1);

namespace Domain\Contact\Services\ScoreStrategies;

use Domain\Contact\Entities\Contact;

interface ScoreStrategyInterface
{
    public function calculate(Contact $contact): int;
}
