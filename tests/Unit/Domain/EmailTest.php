<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\ValueObjects;

use Domain\Contact\Exceptions\InvalidEmailException;
use Domain\Contact\ValueObjects\Email;
use PHPUnit\Framework\TestCase;

final class EmailTest extends TestCase
{
    public function test_creates_valid_email(): void
    {
        $email = new Email('user@example.com');
        $this->assertEquals('user@example.com', $email->value());
    }

    public function test_normalizes_email_to_lowercase(): void
    {
        $email = new Email('USER@EXAMPLE.COM');
        $this->assertEquals('user@example.com', $email->value());
    }

    public function test_throws_exception_for_invalid_email(): void
    {
        $this->expectException(InvalidEmailException::class);
        new Email('not-an-email');
    }

    public function test_identifies_corporate_email(): void
    {
        $email = new Email('user@company.com.br');
        $this->assertTrue($email->isCorporate());
    }

    public function test_identifies_gmail_as_non_corporate(): void
    {
        $email = new Email('user@gmail.com');
        $this->assertFalse($email->isCorporate());
    }

    public function test_identifies_hotmail_as_non_corporate(): void
    {
        $email = new Email('user@hotmail.com');
        $this->assertFalse($email->isCorporate());
    }

    public function test_identifies_yahoo_as_non_corporate(): void
    {
        $email = new Email('user@yahoo.com');
        $this->assertFalse($email->isCorporate());
    }

    public function test_identifies_brazilian_domain(): void
    {
        $email = new Email('user@empresa.com.br');
        $this->assertTrue($email->isBrazilian());
    }

    public function test_identifies_non_brazilian_domain(): void
    {
        $email = new Email('user@company.com');
        $this->assertFalse($email->isBrazilian());
    }

    public function test_extracts_domain(): void
    {
        $email = new Email('user@example.com');
        $this->assertEquals('example.com', $email->domain());
    }

    public function test_equality(): void
    {
        $email1 = new Email('user@example.com');
        $email2 = new Email('user@example.com');
        $this->assertTrue($email1->equals($email2));
    }

    public function test_inequality(): void
    {
        $email1 = new Email('user@example.com');
        $email2 = new Email('other@example.com');
        $this->assertFalse($email1->equals($email2));
    }
}
