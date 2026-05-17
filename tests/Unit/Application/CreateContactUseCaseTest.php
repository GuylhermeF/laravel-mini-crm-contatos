<?php

declare(strict_types=1);

namespace Tests\Unit\Application;

use Application\DTOs\CreateContactDTO;
use Application\UseCases\Contact\CreateContactUseCase;
use Domain\Contact\Entities\Contact;
use Domain\Contact\Repositories\ContactRepositoryInterface;
use Domain\Contact\ValueObjects\ContactName;
use Domain\Contact\ValueObjects\Email;
use Domain\Contact\ValueObjects\Phone;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class CreateContactUseCaseTest extends TestCase
{
    private MockObject $repository;
    private CreateContactUseCase $useCase;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(ContactRepositoryInterface::class);
        $this->useCase = new CreateContactUseCase($this->repository);
    }

    public function test_creates_contact_successfully(): void
    {
        $dto = new CreateContactDTO(
            name: 'João Silva',
            email: 'joao@empresa.com.br',
            phone: '11987654321',
        );

        $savedContact = Contact::reconstitute(
            id: 1,
            name: new ContactName('João Silva'),
            email: new Email('joao@empresa.com.br'),
            phone: new Phone('11987654321'),
            score: new \Domain\Contact\ValueObjects\Score(0),
            status: \Domain\Contact\Enums\ContactStatus::Pending,
            processedAt: null,
        );

        $this->repository
            ->expects($this->once())
            ->method('findByEmail')
            ->with('joao@empresa.com.br')
            ->willReturn(null);

        $this->repository
            ->expects($this->once())
            ->method('save')
            ->willReturn($savedContact);

        $result = $this->useCase->execute($dto);

        $this->assertEquals(1, $result->id());
        $this->assertEquals('João Silva', $result->name()->value());
    }

    public function test_throws_exception_when_email_already_exists(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('A contact with this email already exists.');

        $dto = new CreateContactDTO(
            name: 'João Silva',
            email: 'joao@empresa.com.br',
            phone: '11987654321',
        );

        $existingContact = Contact::reconstitute(
            id: 99,
            name: new ContactName('Other Person'),
            email: new Email('joao@empresa.com.br'),
            phone: new Phone('11987654321'),
            score: new \Domain\Contact\ValueObjects\Score(0),
            status: \Domain\Contact\Enums\ContactStatus::Pending,
            processedAt: null,
        );

        $this->repository
            ->method('findByEmail')
            ->willReturn($existingContact);

        $this->repository->expects($this->never())->method('save');

        $this->useCase->execute($dto);
    }

    public function test_does_not_save_when_validation_fails(): void
    {
        $this->expectException(\Domain\Contact\Exceptions\InvalidEmailException::class);

        $dto = new CreateContactDTO(
            name: 'João',
            email: 'not-valid-email',
            phone: '11987654321',
        );

        $this->repository->method('findByEmail')->willReturn(null);
        $this->repository->expects($this->never())->method('save');

        $this->useCase->execute($dto);
    }
}
