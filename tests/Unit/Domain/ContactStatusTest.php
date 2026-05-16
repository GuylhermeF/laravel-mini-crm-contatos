<?php

declare(strict_types=1);

namespace Tests\Unit\Domain;

use Domain\Contact\Enums\ContactStatus;
use PHPUnit\Framework\TestCase;

final class ContactStatusTest extends TestCase
{
    public function test_pending_can_transition_to_processing(): void
    {
        $this->assertTrue(ContactStatus::Pending->canTransitionTo(ContactStatus::Processing));
    }

    public function test_pending_cannot_transition_to_active(): void
    {
        $this->assertFalse(ContactStatus::Pending->canTransitionTo(ContactStatus::Active));
    }

    public function test_processing_can_transition_to_active(): void
    {
        $this->assertTrue(ContactStatus::Processing->canTransitionTo(ContactStatus::Active));
    }

    public function test_processing_can_transition_to_failed(): void
    {
        $this->assertTrue(ContactStatus::Processing->canTransitionTo(ContactStatus::Failed));
    }

    public function test_active_cannot_transition_to_any_state(): void
    {
        $this->assertFalse(ContactStatus::Active->canTransitionTo(ContactStatus::Processing));
        $this->assertFalse(ContactStatus::Active->canTransitionTo(ContactStatus::Failed));
        $this->assertFalse(ContactStatus::Active->canTransitionTo(ContactStatus::Pending));
    }

    public function test_failed_can_transition_to_processing(): void
    {
        $this->assertTrue(ContactStatus::Failed->canTransitionTo(ContactStatus::Processing));
    }

    public function test_status_labels_are_correct(): void
    {
        $this->assertEquals('Pendente', ContactStatus::Pending->label());
        $this->assertEquals('Processando', ContactStatus::Processing->label());
        $this->assertEquals('Ativo', ContactStatus::Active->label());
        $this->assertEquals('Falhou', ContactStatus::Failed->label());
    }

    public function test_enum_values(): void
    {
        $this->assertEquals('pending', ContactStatus::Pending->value);
        $this->assertEquals('processing', ContactStatus::Processing->value);
        $this->assertEquals('active', ContactStatus::Active->value);
        $this->assertEquals('failed', ContactStatus::Failed->value);
    }

    public function test_creates_from_string(): void
    {
        $this->assertEquals(ContactStatus::Active, ContactStatus::from('active'));
        $this->assertEquals(ContactStatus::Failed, ContactStatus::from('failed'));
    }
}
