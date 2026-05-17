<?php

declare(strict_types=1);

namespace Tests\Unit\Application;

use Application\Contracts\ScoreProcessingQueueInterface;
use Application\UseCases\Contact\TriggerScoreProcessingUseCase;
use Domain\Contact\Entities\Contact;
use Domain\Contact\Enums\ContactStatus;
use Domain\Contact\Exceptions\ContactNotFoundException;
use Domain\Contact\Exceptions\ContactNotProcessableException;
use Domain\Contact\Repositories\ContactRepositoryInterface;
use Domain\Contact\ValueObjects\ContactName;
use Domain\Contact\ValueObjects\Email;
use Domain\Contact\ValueObjects\Phone;
use Domain\Contact\ValueObjects\Score;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class TriggerScoreProcessingUseCaseTest extends TestCase
{
    private MockObject $repository;
    private MockObject $queue;
    private TriggerScoreProcessingUseCase $useCase;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(ContactRepositoryInterface::class);
        $this->queue = $this->createMock(ScoreProcessingQueueInterface::class);
        $this->useCase = new TriggerScoreProcessingUseCase($this->repository, $this->queue);
    }

    private function makeContactWithStatus(ContactStatus $status): Contact
    {
        return Contact::reconstitute(
            id: 1,
            name: new ContactName('João Silva'),
            email: new Email('joao@empresa.com.br'),
            phone: new Phone('11987654321'),
            score: new Score(0),
            status: $status,
            processedAt: null,
        );
    }

    public function test_throws_when_contact_not_found(): void
    {
        $this->expectException(ContactNotFoundException::class);

        $this->repository->method('findById')->willReturn(null);

        $this->useCase->execute(99);
    }

    public function test_throws_when_contact_is_active(): void
    {
        $this->expectException(ContactNotProcessableException::class);

        $contact = $this->makeContactWithStatus(ContactStatus::Active);
        $this->repository->method('findById')->willReturn($contact);
        $this->queue->expects($this->never())->method('dispatch');

        $this->useCase->execute(1);
    }

    public function test_throws_when_contact_is_already_processing(): void
    {
        $this->expectException(ContactNotProcessableException::class);

        $contact = $this->makeContactWithStatus(ContactStatus::Processing);
        $this->repository->method('findById')->willReturn($contact);
        $this->queue->expects($this->never())->method('dispatch');

        $this->useCase->execute(1);
    }

    public function test_dispatches_queue_for_pending_contact(): void
    {
        $contact = $this->makeContactWithStatus(ContactStatus::Pending);
        $this->repository->method('findById')->willReturn($contact);
        $this->queue->expects($this->once())->method('dispatch')->with(1);

        $this->useCase->execute(1);
    }

    public function test_dispatches_queue_for_failed_contact_retry(): void
    {
        $contact = $this->makeContactWithStatus(ContactStatus::Failed);
        $this->repository->method('findById')->willReturn($contact);
        $this->queue->expects($this->once())->method('dispatch')->with(1);

        $this->useCase->execute(1);
    }
}
