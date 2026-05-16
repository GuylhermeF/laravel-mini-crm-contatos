<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\ValueObjects;

use Domain\Contact\Exceptions\InvalidNameException;
use Domain\Contact\ValueObjects\ContactName;
use PHPUnit\Framework\TestCase;

final class ContactNameTest extends TestCase
{
    public function test_creates_valid_name(): void
    {
        $name = new ContactName('João Silva');
        $this->assertEquals('João Silva', $name->value());
    }

    public function test_trims_whitespace(): void
    {
        $name = new ContactName('  João Silva  ');
        $this->assertEquals('João Silva', $name->value());
    }

    public function test_throws_exception_for_empty_name(): void
    {
        $this->expectException(InvalidNameException::class);
        new ContactName('');
    }

    public function test_throws_exception_for_single_character(): void
    {
        $this->expectException(InvalidNameException::class);
        new ContactName('J');
    }

    public function test_identifies_full_name(): void
    {
        $name = new ContactName('João Silva');
        $this->assertTrue($name->isFullName());
    }

    public function test_identifies_single_name(): void
    {
        $name = new ContactName('João');
        $this->assertFalse($name->isFullName());
    }

    public function test_identifies_name_with_multiple_spaces(): void
    {
        $name = new ContactName('Maria Aparecida Santos');
        $this->assertTrue($name->isFullName());
    }

    public function test_equality(): void
    {
        $name1 = new ContactName('João Silva');
        $name2 = new ContactName('João Silva');
        $this->assertTrue($name1->equals($name2));
    }

    public function test_to_string(): void
    {
        $name = new ContactName('Ana Paula');
        $this->assertEquals('Ana Paula', (string) $name);
    }
}
