<?php

declare(strict_types=1);

namespace Domain\Contact\Services;

use Domain\Contact\Entities\Contact;
use Domain\Contact\Services\ScoreStrategies\ScoreStrategyInterface;
use Domain\Contact\ValueObjects\Score;

final class ScoreCalculatorService implements ScoreCalculatorServiceInterface
{
    /** @var ScoreStrategyInterface[] */
    private array $strategies;

    public function __construct(ScoreStrategyInterface ...$strategies)
    {
        $this->strategies = $strategies;
    }

    public function calculate(Contact $contact): Score
    {
        $totalPoints = 0;

        foreach ($this->strategies as $strategy) {
            $totalPoints += $strategy->calculate($contact);
        }

        return new Score($totalPoints);
    }
}
