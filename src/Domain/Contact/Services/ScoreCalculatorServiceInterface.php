<?php

declare(strict_types=1);

namespace Domain\Contact\Services;

use Domain\Contact\Entities\Contact;
use Domain\Contact\ValueObjects\Score;

interface ScoreCalculatorServiceInterface
{
    public function calculate(Contact $contact): Score;
}
