<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\ValueObjects;

use Domain\Contact\Exceptions\InvalidScoreException;
use Domain\Contact\ValueObjects\Score;
use PHPUnit\Framework\TestCase;

final class ScoreTest extends TestCase
{
    public function test_creates_score_with_zero(): void
    {
        $score = new Score(0);
        $this->assertEquals(0, $score->value());
    }

    public function test_creates_score_with_positive_value(): void
    {
        $score = new Score(60);
        $this->assertEquals(60, $score->value());
    }

    public function test_throws_exception_for_negative_score(): void
    {
        $this->expectException(InvalidScoreException::class);
        new Score(-1);
    }

    public function test_add_returns_new_instance(): void
    {
        $score = new Score(20);
        $newScore = $score->add(10);

        $this->assertEquals(20, $score->value()); // original unchanged
        $this->assertEquals(30, $newScore->value());
    }

    public function test_add_multiple_times(): void
    {
        $score = new Score(0);
        $score = $score->add(20)->add(10)->add(10)->add(20);

        $this->assertEquals(60, $score->value());
    }

    public function test_equality(): void
    {
        $score1 = new Score(40);
        $score2 = new Score(40);
        $this->assertTrue($score1->equals($score2));
    }

    public function test_inequality(): void
    {
        $score1 = new Score(40);
        $score2 = new Score(50);
        $this->assertFalse($score1->equals($score2));
    }

    public function test_to_string(): void
    {
        $score = new Score(55);
        $this->assertEquals('55', (string) $score);
    }
}
