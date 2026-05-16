<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\ValueObjects;

use Domain\Contact\Exceptions\InvalidPhoneException;
use Domain\Contact\ValueObjects\Phone;
use PHPUnit\Framework\TestCase;

final class PhoneTest extends TestCase
{
    public function test_creates_valid_phone_with_11_digits(): void
    {
        $phone = new Phone('11987654321');
        $this->assertEquals('11987654321', $phone->value());
    }

    public function test_creates_valid_phone_with_10_digits(): void
    {
        $phone = new Phone('1132456789');
        $this->assertEquals('1132456789', $phone->value());
    }

    public function test_normalizes_formatted_phone(): void
    {
        $phone = new Phone('(11) 98765-4321');
        $this->assertEquals('11987654321', $phone->value());
    }

    public function test_normalizes_phone_with_dashes(): void
    {
        $phone = new Phone('11-9876-54321');
        $this->assertEquals('11987654321', $phone->value());
    }

    public function test_throws_exception_for_phone_too_short(): void
    {
        $this->expectException(InvalidPhoneException::class);
        new Phone('1234');
    }

    public function test_identifies_sao_paulo_area_code_11(): void
    {
        $phone = new Phone('11987654321');
        $this->assertTrue($phone->isSaoPauloAreaCode());
    }

    public function test_identifies_sao_paulo_area_code_19(): void
    {
        $phone = new Phone('19987654321');
        $this->assertTrue($phone->isSaoPauloAreaCode());
    }

    public function test_identifies_non_sao_paulo_area_code(): void
    {
        $phone = new Phone('21987654321'); // Rio de Janeiro
        $this->assertFalse($phone->isSaoPauloAreaCode());
    }

    public function test_has_area_code_for_10_digit_number(): void
    {
        $phone = new Phone('1132456789');
        $this->assertTrue($phone->hasAreaCode());
    }

    public function test_extracts_area_code(): void
    {
        $phone = new Phone('11987654321');
        $this->assertEquals('11', $phone->areaCode());
    }

    public function test_formats_11_digit_phone(): void
    {
        $phone = new Phone('11987654321');
        $this->assertEquals('(11) 98765-4321', $phone->formatted());
    }

    public function test_formats_10_digit_phone(): void
    {
        $phone = new Phone('1132456789');
        $this->assertEquals('(11) 3245-6789', $phone->formatted());
    }

    public function test_equality(): void
    {
        $phone1 = new Phone('11987654321');
        $phone2 = new Phone('(11) 98765-4321');
        $this->assertTrue($phone1->equals($phone2));
    }
}
