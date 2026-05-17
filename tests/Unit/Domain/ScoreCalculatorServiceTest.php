<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use Domain\Contact\Entities\Contact;
use Domain\Contact\Services\ScoreCalculatorService;
use Domain\Contact\Services\ScoreStrategies\EmailScoreStrategy;
use Domain\Contact\Services\ScoreStrategies\NameScoreStrategy;
use Domain\Contact\Services\ScoreStrategies\PhoneScoreStrategy;
use Domain\Contact\ValueObjects\ContactName;
use Domain\Contact\ValueObjects\Email;
use Domain\Contact\ValueObjects\Phone;
use PHPUnit\Framework\TestCase;

final class ScoreCalculatorServiceTest extends TestCase
{
    private ScoreCalculatorService $calculator;

    protected function setUp(): void
    {
        $this->calculator = new ScoreCalculatorService(
            new EmailScoreStrategy(),
            new NameScoreStrategy(),
            new PhoneScoreStrategy(),
        );
    }

    private function makeContact(string $name, string $email, string $phone): Contact
    {
        return Contact::create(
            name: new ContactName($name),
            email: new Email($email),
            phone: new Phone($phone),
        );
    }

    public function test_maximum_score_for_ideal_contact(): void
    {
        // Corporate .br email (+20+10), full name (+10), SP area code (+20) = 60
        $contact = $this->makeContact(
            name: 'João Silva',
            email: 'joao@empresa.com.br',
            phone: '11987654321',
        );

        $score = $this->calculator->calculate($contact);
        $this->assertEquals(60, $score->value());
    }

    public function test_corporate_email_adds_20_points(): void
    {
        $contact = $this->makeContact('Ana', 'ana@empresa.com', '21987654321');
        $strategy = new EmailScoreStrategy();

        $this->assertEquals(20, $strategy->calculate($contact));
    }

    public function test_gmail_email_adds_zero_points(): void
    {
        $contact = $this->makeContact('Ana', 'ana@gmail.com', '21987654321');
        $strategy = new EmailScoreStrategy();

        $this->assertEquals(0, $strategy->calculate($contact));
    }

    public function test_br_domain_adds_10_points(): void
    {
        $contact = $this->makeContact('Ana', 'ana@empresa.com.br', '21987654321');
        $strategy = new EmailScoreStrategy();

        // Corporate (+20) + .br (+10) = 30
        $this->assertEquals(30, $strategy->calculate($contact));
    }

    public function test_full_name_adds_10_points(): void
    {
        $contact = $this->makeContact('João Silva', 'j@gmail.com', '21987654321');
        $strategy = new NameScoreStrategy();

        $this->assertEquals(10, $strategy->calculate($contact));
    }

    public function test_single_name_adds_zero_points(): void
    {
        $contact = $this->makeContact('João', 'j@gmail.com', '21987654321');
        $strategy = new NameScoreStrategy();

        $this->assertEquals(0, $strategy->calculate($contact));
    }

    public function test_sao_paulo_ddd_adds_20_points(): void
    {
        $contact = $this->makeContact('Ana', 'ana@gmail.com', '11987654321');
        $strategy = new PhoneScoreStrategy();

        $this->assertEquals(20, $strategy->calculate($contact));
    }

    public function test_other_state_ddd_adds_10_points(): void
    {
        $contact = $this->makeContact('Ana', 'ana@gmail.com', '21987654321'); // RJ
        $strategy = new PhoneScoreStrategy();

        $this->assertEquals(10, $strategy->calculate($contact));
    }

    public function test_zero_score_for_personal_email_single_name_non_sp(): void
    {
        // gmail (0), single name (0), non-SP (+10) = 10
        $contact = $this->makeContact(
            name: 'João',
            email: 'joao@gmail.com',
            phone: '21987654321',
        );

        $score = $this->calculator->calculate($contact);
        $this->assertEquals(10, $score->value());
    }

    public function test_score_with_sp_area_code_19(): void
    {
        $contact = $this->makeContact('Ana', 'ana@gmail.com', '19987654321');
        $strategy = new PhoneScoreStrategy();

        $this->assertEquals(20, $strategy->calculate($contact));
    }
}
