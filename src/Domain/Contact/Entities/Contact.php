<?php

declare(strict_types=1);

namespace Domain\Contact\Entities;

use DateTimeImmutable;
use Domain\Contact\Enums\ContactStatus;
use Domain\Contact\Exceptions\InvalidStatusTransitionException;
use Domain\Contact\ValueObjects\ContactName;
use Domain\Contact\ValueObjects\Email;
use Domain\Contact\ValueObjects\Phone;
use Domain\Contact\ValueObjects\Score;

final class Contact
{
    private function __construct(
        private readonly ?int $id,
        private ContactName $name,
        private Email $email,
        private Phone $phone,
        private Score $score,
        private ContactStatus $status,
        private ?DateTimeImmutable $processedAt,
    ) {
    }

    public static function create(
        ContactName $name,
        Email $email,
        Phone $phone,
    ): self {
        return new self(
            id: null,
            name: $name,
            email: $email,
            phone: $phone,
            score: new Score(0),
            status: ContactStatus::Pending,
            processedAt: null,
        );
    }

    public static function reconstitute(
        int $id,
        ContactName $name,
        Email $email,
        Phone $phone,
        Score $score,
        ContactStatus $status,
        ?DateTimeImmutable $processedAt,
    ): self {
        return new self(
            id: $id,
            name: $name,
            email: $email,
            phone: $phone,
            score: $score,
            status: $status,
            processedAt: $processedAt,
        );
    }

    public function startProcessing(): void
    {
        if (!$this->status->canTransitionTo(ContactStatus::Processing)) {
            throw new InvalidStatusTransitionException(
                "Cannot transition from {$this->status->value} to processing."
            );
        }

        $this->status = ContactStatus::Processing;
    }

    public function completeProcessing(Score $score): void
    {
        if (!$this->status->canTransitionTo(ContactStatus::Active)) {
            throw new InvalidStatusTransitionException(
                "Cannot transition from {$this->status->value} to active."
            );
        }

        $this->score = $score;
        $this->status = ContactStatus::Active;
        $this->processedAt = new DateTimeImmutable();
    }

    public function failProcessing(): void
    {
        if (!$this->status->canTransitionTo(ContactStatus::Failed)) {
            throw new InvalidStatusTransitionException(
                "Cannot transition from {$this->status->value} to failed."
            );
        }

        $this->status = ContactStatus::Failed;
        $this->processedAt = new DateTimeImmutable();
    }

    public function update(
        ContactName $name,
        Email $email,
        Phone $phone,
    ): void {
        $this->name = $name;
        $this->email = $email;
        $this->phone = $phone;
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function name(): ContactName
    {
        return $this->name;
    }

    public function email(): Email
    {
        return $this->email;
    }

    public function phone(): Phone
    {
        return $this->phone;
    }

    public function score(): Score
    {
        return $this->score;
    }

    public function status(): ContactStatus
    {
        return $this->status;
    }

    public function processedAt(): ?DateTimeImmutable
    {
        return $this->processedAt;
    }

    public function isProcessing(): bool
    {
        return $this->status === ContactStatus::Processing;
    }
}
