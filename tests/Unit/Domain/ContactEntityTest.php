<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use Domain\Contact\Entities\Contact;
use Domain\Contact\Enums\ContactStatus;
use Domain\Contact\Exceptions\InvalidStatusTransitionException;
use Domain\Contact\ValueObjects\ContactName;
use Domain\Contact\ValueObjects\Email;
use Domain\Contact\ValueObjects\Phone;
use Domain\Contact\ValueObjects\Score;
use PHPUnit\Framework\TestCase;

final class ContactEntityTest extends TestCase
{
    private function makeContact(): Contact
    {
        return Contact::create(
            name: new ContactName('João Silva'),
            email: new Email('joao@empresa.com.br'),
            phone: new Phone('11987654321'),
        );
    }

    public function test_creates_contact_with_pending_status_and_zero_score(): void
    {
        $contact = $this->makeContact();

        $this->assertNull($contact->id());
        $this->assertEquals(ContactStatus::Pending, $contact->status());
        $this->assertEquals(0, $contact->score()->value());
        $this->assertNull($contact->processedAt());
    }

    public function test_transitions_to_processing(): void
    {
        $contact = $this->makeContact();
        $contact->startProcessing();

        $this->assertEquals(ContactStatus::Processing, $contact->status());
        $this->assertTrue($contact->isProcessing());
    }

    public function test_cannot_transition_from_pending_to_active(): void
    {
        $this->expectException(InvalidStatusTransitionException::class);

        $contact = $this->makeContact();
        $contact->completeProcessing(new Score(50));
    }

    public function test_completes_processing_with_score(): void
    {
        $contact = $this->makeContact();
        $contact->startProcessing();
        $contact->completeProcessing(new Score(50));

        $this->assertEquals(ContactStatus::Active, $contact->status());
        $this->assertEquals(50, $contact->score()->value());
        $this->assertNotNull($contact->processedAt());
    }

    public function test_fails_processing(): void
    {
        $contact = $this->makeContact();
        $contact->startProcessing();
        $contact->failProcessing();

        $this->assertEquals(ContactStatus::Failed, $contact->status());
        $this->assertNotNull($contact->processedAt());
    }

    public function test_cannot_start_processing_when_already_processing(): void
    {
        $this->expectException(InvalidStatusTransitionException::class);

        $contact = $this->makeContact();
        $contact->startProcessing();
        $contact->startProcessing(); // Cannot start again
    }

    public function test_reconstitutes_from_persistence(): void
    {
        $contact = Contact::reconstitute(
            id: 1,
            name: new ContactName('Maria Santos'),
            email: new Email('maria@corp.com'),
            phone: new Phone('21987654321'),
            score: new Score(30),
            status: ContactStatus::Active,
            processedAt: new \DateTimeImmutable(),
        );

        $this->assertEquals(1, $contact->id());
        $this->assertEquals('Maria Santos', $contact->name()->value());
        $this->assertEquals(30, $contact->score()->value());
        $this->assertEquals(ContactStatus::Active, $contact->status());
    }

    public function test_updates_contact_data(): void
    {
        $contact = $this->makeContact();
        $contact->update(
            name: new ContactName('João Silva Updated'),
            email: new Email('joao.updated@empresa.com'),
            phone: new Phone('19987654321'),
        );

        $this->assertEquals('João Silva Updated', $contact->name()->value());
        $this->assertEquals('joao.updated@empresa.com', $contact->email()->value());
    }
}
