<?php

declare(strict_types=1);

namespace YAY\PartnerSDK\Tests\Unit\Dto\V1;

use PHPUnit\Framework\TestCase;
use YAY\PartnerSDK\Dto\V1;
use YAY\PartnerSDK\Exception\InvalidArgumentException;

final class CustomerTest extends TestCase
{
    public function testCanCreateValidCustomer(): void
    {
        $address = new V1\Address(
            line1: "Musterstraße 123",
            line2: "Apartment 4B",
            city: "Berlin",
            postalCode: "10115",
            country: "DE",
        );

        $customer = new V1\Customer(
            firstname: "Sarah",
            lastname: "Mueller",
            email: "sarah.mueller@gmail.com",
            address: $address,
        );

        $this->assertSame("Sarah", $customer->firstname);
        $this->assertSame("Mueller", $customer->lastname);
        $this->assertSame("sarah.mueller@gmail.com", $customer->email);
        $this->assertSame($address, $customer->address);
    }

    public function testThrowsExceptionForEmptyFirstname(): void
    {
        $address = new V1\Address(
            line1: "Test Street 1",
            line2: "",
            city: "Berlin",
            postalCode: "10115",
            country: "DE",
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Customer firstname cannot be empty');

        new V1\Customer(
            firstname: "",
            lastname: "Doe",
            email: "john.doe@example.com",
            address: $address,
        );
    }

    public function testThrowsExceptionForEmptyLastname(): void
    {
        $address = new V1\Address(
            line1: "Test Street 1",
            line2: "",
            city: "Berlin",
            postalCode: "10115",
            country: "DE",
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Customer lastname cannot be empty');

        new V1\Customer(
            firstname: "John",
            lastname: "",
            email: "john.doe@example.com",
            address: $address,
        );
    }

    public function testThrowsExceptionForInvalidEmail(): void
    {
        $address = new V1\Address(
            line1: "Test Street 1",
            line2: "",
            city: "Berlin",
            postalCode: "10115",
            country: "DE",
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid email address: invalid-email');

        new V1\Customer(
            firstname: "John",
            lastname: "Doe",
            email: "invalid-email",
            address: $address,
        );
    }

    public function testThrowsExceptionForTooLongFirstname(): void
    {
        $address = new V1\Address(
            line1: "Test Street 1",
            line2: "",
            city: "Berlin",
            postalCode: "10115",
            country: "DE",
        );

        $longName = str_repeat("a", 101);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Customer firstname cannot exceed 100 characters');

        new V1\Customer(
            firstname: $longName,
            lastname: "Doe",
            email: "john.doe@example.com",
            address: $address,
        );
    }

    public function testAcceptsMaxLengthNames(): void
    {
        $address = new V1\Address(
            line1: "Test Street 1",
            line2: "",
            city: "Berlin",
            postalCode: "10115",
            country: "DE",
        );

        $maxLengthName = str_repeat("a", 100);

        $customer = new V1\Customer(
            firstname: $maxLengthName,
            lastname: $maxLengthName,
            email: "test@example.com",
            address: $address,
        );

        $this->assertSame($maxLengthName, $customer->firstname);
        $this->assertSame($maxLengthName, $customer->lastname);
    }

    public function testThrowsExceptionForUppercaseEmail(): void
    {
        $address = new V1\Address(
            line1: "Test Street 1",
            line2: "",
            city: "Berlin",
            postalCode: "10115",
            country: "DE",
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Email address must be lowercase: John.Doe@Example.Com');

        new V1\Customer(
            firstname: "John",
            lastname: "Doe",
            email: "John.Doe@Example.Com",
            address: $address,
        );
    }

    public function testThrowsExceptionForFullyUppercaseEmail(): void
    {
        $address = new V1\Address(
            line1: "Test Street 1",
            line2: "",
            city: "Berlin",
            postalCode: "10115",
            country: "DE",
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Email address must be lowercase: USER@DOMAIN.COM');

        new V1\Customer(
            firstname: "John",
            lastname: "Doe",
            email: "USER@DOMAIN.COM",
            address: $address,
        );
    }
}
