<?php

declare(strict_types=1);

namespace YAY\PartnerSDK\Tests\Unit\Dto\V1;

use PHPUnit\Framework\TestCase;
use YAY\PartnerSDK\Dto\V1;
use YAY\PartnerSDK\Exception\InvalidArgumentException;

final class CreateProjectRequestTest extends TestCase
{
    public function testCanCreateValidProjectRequest(): void
    {
        $address = new V1\Address(
            line1: "Musterstraße 123",
            line2: "Apartment 4B",
            city: "Berlin",
            postalCode: "10115",
            country: "DE"
        );

        $customer = new V1\Customer(
            firstname: "Sarah",
            lastname: "Mueller",
            email: "sarah.mueller@gmail.com",
            address: $address
        );

        $upload = new V1\Upload(
            numberOfImages: 150,
            coverUrl: "https://my-photo-app.example.com/images/wedding-cover.jpg",
            photoUrls: [
                "https://my-photo-app.example.com/photos/img001.jpg",
                "https://my-photo-app.example.com/photos/img002.jpg",
                "https://my-photo-app.example.com/photos/img003.jpg",
            ]
        );

        $project = new V1\CreateProjectRequest(
            title: "Sarah & Mike's Wedding Album",
            customer: $customer,
            upload: $upload,
            locale: "de_DE"
        );

        $this->assertSame("Sarah & Mike's Wedding Album", $project->title);
        $this->assertSame($customer, $project->customer);
        $this->assertSame($upload, $project->upload);
        $this->assertSame("de_DE", $project->locale);
    }

    public function testCanCreateProjectRequestWithMinimalData(): void
    {
        $address = new V1\Address(
            line1: "Test Street 1",
            line2: "",
            city: "Munich",
            postalCode: "80331",
            country: "DE"
        );

        $customer = new V1\Customer(
            firstname: "John",
            lastname: "Doe",
            email: "john.doe@example.com",
            address: $address
        );

        $upload = new V1\Upload(
            numberOfImages: 10,
            coverUrl: "https://example.com/cover.jpg"
            // photoUrls is optional and defaults to null
        );

        $project = new V1\CreateProjectRequest(
            title: "My Simple Project",
            customer: $customer,
            upload: $upload,
            locale: "en_US"
        );

        $this->assertSame("My Simple Project", $project->title);
        $this->assertSame("John", $project->customer->firstname);
        $this->assertSame("Doe", $project->customer->lastname);
        $this->assertSame("john.doe@example.com", $project->customer->email);
        $this->assertSame("Test Street 1", $project->customer->address->line1);
        $this->assertSame("", $project->customer->address->line2);
        $this->assertSame("Munich", $project->customer->address->city);
        $this->assertSame("80331", $project->customer->address->postalCode);
        $this->assertSame("DE", $project->customer->address->country);
        $this->assertSame(10, $project->upload->numberOfImages);
        $this->assertSame("https://example.com/cover.jpg", $project->upload->coverUrl);
        $this->assertNull($project->upload->photoUrls);
        $this->assertSame("en_US", $project->locale);
    }

    public function testThrowsExceptionForEmptyTitle(): void
    {
        $address = new V1\Address(
            line1: "Test Street 1",
            line2: "",
            city: "Berlin",
            postalCode: "10115",
            country: "DE"
        );

        $customer = new V1\Customer(
            firstname: "Test",
            lastname: "User",
            email: "test@example.com",
            address: $address
        );

        $upload = new V1\Upload(
            numberOfImages: 5,
            coverUrl: "https://example.com/cover.jpg"
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Project title cannot be empty');

        new V1\CreateProjectRequest(
            title: "",
            customer: $customer,
            upload: $upload,
            locale: "de_DE"
        );
    }

    public function testThrowsExceptionForInvalidLocale(): void
    {
        $address = new V1\Address(
            line1: "Test Street 1",
            line2: "",
            city: "Berlin",
            postalCode: "10115",
            country: "DE"
        );

        $customer = new V1\Customer(
            firstname: "Test",
            lastname: "User",
            email: "test@example.com",
            address: $address
        );

        $upload = new V1\Upload(
            numberOfImages: 5,
            coverUrl: "https://example.com/cover.jpg"
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid locale format 'invalid'. Expected format: 'de_DE' or 'en_US'");
        new V1\CreateProjectRequest(
            title: "Test Project",
            customer: $customer,
            upload: $upload,
            locale: "invalid"
        );
    }

    public function testAcceptsVariousValidLocales(): void
    {
        $address = new V1\Address(
            line1: "Test Street 1",
            line2: "",
            city: "Berlin",
            postalCode: "10115",
            country: "DE"
        );

        $customer = new V1\Customer(
            firstname: "Test",
            lastname: "User",
            email: "test@example.com",
            address: $address
        );

        $upload = new V1\Upload(
            numberOfImages: 5,
            coverUrl: "https://example.com/cover.jpg"
        );

        $validLocales = ["de_DE", "en_US", "fr_FR", "es_ES", "it_IT", "nl_NL"];

        foreach ($validLocales as $locale) {
            $project = new V1\CreateProjectRequest(
                title: "Test Project",
                customer: $customer,
                upload: $upload,
                locale: $locale
            );

            $this->assertSame($locale, $project->locale);
        }
    }

    public function testCustomerValidationPropagates(): void
    {
        $address = new V1\Address(
            line1: "Test Street 1",
            line2: "",
            city: "Berlin",
            postalCode: "10115",
            country: "DE"
        );

        $upload = new V1\Upload(
            numberOfImages: 5,
            coverUrl: "https://example.com/cover.jpg"
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Customer firstname cannot be empty');

        $customer = new V1\Customer(
            firstname: "",
            lastname: "User",
            email: "test@example.com",
            address: $address
        );

        new V1\CreateProjectRequest(
            title: "Test Project",
            customer: $customer,
            upload: $upload,
            locale: "de_DE"
        );
    }

    public function testUploadValidationPropagates(): void
    {
        $address = new V1\Address(
            line1: "Test Street 1",
            line2: "",
            city: "Berlin",
            postalCode: "10115",
            country: "DE"
        );

        $customer = new V1\Customer(
            firstname: "Test",
            lastname: "User",
            email: "test@example.com",
            address: $address
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Number of images must be positive');

        $upload = new V1\Upload(
            numberOfImages: 0, // @phpstan-ignore argument.type
            coverUrl: "https://example.com/cover.jpg"
        );

        new V1\CreateProjectRequest(
            title: "Test Project",
            customer: $customer,
            upload: $upload,
            locale: "de_DE"
        );
    }
}
