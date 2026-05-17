<?php

declare(strict_types=1);

namespace Tests\Unit\Application;

use Application\Contracts\EventDispatcherInterface;
use Application\UseCases\Contact\ProcessContactScoreUseCase;
use Domain\Contact\Entities\Contact;
use Domain\Contact\Enums\ContactStatus;
use Domain\Contact\Exceptions\ContactNotFoundException;
use Domain\Contact\Repositories\ContactRepositoryInterface;
use Domain\Contact\Services\ScoreCalculatorServiceInterface;
use Domain\Contact\ValueObjects\ContactName;
use Domain\Contact\ValueObjects\Email;
use Domain\Contact\ValueObjects\Phone;
use Domain\Contact\ValueObjects\Score;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ProcessContactScoreUseCaseTest extends TestCase
{
    private MockObject $repository;
    private MockObject $scoreCalculator; // mocked via ScoreCalculatorServiceInterface
    private MockObject $eventDispatcher;
    private ProcessContactScoreUseCase $useCase;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(ContactRepositoryInterface::class);
        $this->scoreCalculator = $this->createMock(ScoreCalculatorServiceInterface::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->useCase = new ProcessContactScoreUseCase(
            $this->repository,
            $this->scoreCalculator,
            $this->eventDispatcher,
        );
    }

    private function makeContact(int $id = 1): Contact
    {
        return Contact::reconstitute(
            id: $id,
            name: new ContactName('João Silva'),
            email: new Email('joao@empresa.com.br'),
            phone: new Phone('11987654321'),
            score: new Score(0),
            status: ContactStatus::Pending,
            processedAt: null,
        );
    }

    public function test_throws_exception_when_contact_not_found(): void
    {
        $this->expectException(ContactNotFoundException::class);

        $this->repository->method('findById')->willReturn(null);

        $this->useCase->execute(999);
    }

    public function test_processes_contact_score_successfully(): void
    {
        $contact = $this->makeContact();

        $this->repository->method('findById')->willReturn($contact);
        $this->scoreCalculator->expects($this->once())->method('calculate')->willReturn(new Score(60));
        $this->repository->method('save')->willReturnCallback(function (Contact $c) {
            return Contact::reconstitute(1, $c->name(), $c->email(), $c->phone(), $c->score(), $c->status(), $c->processedAt());
        });
        $this->eventDispatcher->expects($this->once())->method('dispatch');

        $result = $this->useCase->execute(1);

        $this->assertEquals(ContactStatus::Active, $result->status());
        $this->assertEquals(60, $result->score()->value());
        $this->assertNotNull($result->processedAt());
    }

    public function test_contact_starts_in_processing_state(): void
    {
        $contact = $this->makeContact();

        $this->repository->method('findById')->willReturn($contact);
        $this->scoreCalculator->method('calculate')->willReturn(new Score(40));
        $this->eventDispatcher->method('dispatch');

        $processingCapture = null;
        $this->repository->method('save')->willReturnCallback(function (Contact $c) use (&$processingCapture) {
            if ($processingCapture === null) {
                $processingCapture = $c->status();
            }
            return Contact::reconstitute(1, $c->name(), $c->email(), $c->phone(), $c->score(), $c->status(), $c->processedAt());
        });

        $this->useCase->execute(1);

        $this->assertEquals(ContactStatus::Processing, $processingCapture);
    }

    public function test_sets_failed_status_when_calculation_throws(): void
    {
        $contact = $this->makeContact();

        $this->repository->method('findById')->willReturn($contact);
        $this->scoreCalculator->method('calculate')->willThrowException(new \RuntimeException('Calculation error'));

        $savedStatuses = [];
        $this->repository->method('save')->willReturnCallback(function (Contact $c) use (&$savedStatuses) {
            $savedStatuses[] = $c->status()->value;
            return Contact::reconstitute(1, $c->name(), $c->email(), $c->phone(), $c->score(), $c->status(), $c->processedAt());
        });

        try {
            $this->useCase->execute(1);
        } catch (\RuntimeException) {
            // expected — the use case re-throws
        }

        $this->assertContains('processing', $savedStatuses);
        $this->assertContains('failed', $savedStatuses);
    }
}
