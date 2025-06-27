<?php

declare(strict_types=1);

namespace YAY\PartnerSDK\Tests\Unit\Dto\V1;

use PHPUnit\Framework\TestCase;
use YAY\PartnerSDK\Dto\V1;
use YAY\PartnerSDK\Exception\InvalidArgumentException;

final class UploadTest extends TestCase
{
    public function testCanCreateUploadWithoutPhotoUrls(): void
    {
        $upload = new V1\Upload(
            numberOfImages: 50,
            coverUrl: "https://example.com/cover.jpg"
        );

        $this->assertSame(50, $upload->numberOfImages);
        $this->assertSame("https://example.com/cover.jpg", $upload->coverUrl);
        $this->assertNull($upload->photoUrls);
    }

    public function testCanCreateUploadWithPhotoUrls(): void
    {
        $photoUrls = [
            "https://example.com/photo1.jpg",
            "https://example.com/photo2.jpg",
            "https://example.com/photo3.jpg",
        ];

        $upload = new V1\Upload(
            numberOfImages: 3,
            coverUrl: "https://example.com/cover.jpg",
            photoUrls: $photoUrls
        );

        $this->assertSame(3, $upload->numberOfImages);
        $this->assertSame("https://example.com/cover.jpg", $upload->coverUrl);
        $this->assertSame($photoUrls, $upload->photoUrls);
    }

    public function testThrowsExceptionForZeroImages(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Number of images must be positive');

        new V1\Upload(
            numberOfImages: 0, // @phpstan-ignore argument.type
            coverUrl: "https://example.com/cover.jpg"
        );
    }

    public function testThrowsExceptionForNegativeImages(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Number of images must be positive');

        new V1\Upload(
            numberOfImages: -5, // @phpstan-ignore argument.type
            coverUrl: "https://example.com/cover.jpg"
        );
    }

    public function testThrowsExceptionForTooManyImages(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Number of images cannot exceed 1000');

        new V1\Upload(
            numberOfImages: 1001,
            coverUrl: "https://example.com/cover.jpg"
        );
    }

    public function testThrowsExceptionForEmptyCoverUrl(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cover URL cannot be empty');

        new V1\Upload(
            numberOfImages: 10,
            coverUrl: "" // @phpstan-ignore argument.type
        );
    }

    public function testThrowsExceptionForInvalidCoverUrl(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid cover URL: not-a-url');

        new V1\Upload(
            numberOfImages: 10,
            coverUrl: "not-a-url"
        );
    }

    public function testThrowsExceptionForEmptyPhotoUrlsArray(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Photo URLs array cannot be empty (use null instead)');

        new V1\Upload(
            numberOfImages: 10,
            coverUrl: "https://example.com/cover.jpg",
            photoUrls: [] // @phpstan-ignore argument.type
        );
    }

    public function testThrowsExceptionForInvalidPhotoUrl(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid photo URL at index 1: not-a-url');

        new V1\Upload(
            numberOfImages: 10,
            coverUrl: "https://example.com/cover.jpg",
            photoUrls: [
                "https://example.com/photo1.jpg",
                "not-a-url", // Invalid URL at index 1
                "https://example.com/photo3.jpg",
            ]
        );
    }

    public function testAcceptsMaximumValidValues(): void
    {
        $maxPhotoUrls = array_fill(0, 1000, "https://example.com/photo.jpg");

        $upload = new V1\Upload(
            numberOfImages: 1000,
            coverUrl: "https://example.com/cover.jpg",
            photoUrls: $maxPhotoUrls
        );

        $this->assertSame(1000, $upload->numberOfImages);
        $this->assertNotNull($upload->photoUrls);
        $this->assertCount(1000, $upload->photoUrls);
    }
}
