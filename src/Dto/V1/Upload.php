<?php

declare(strict_types=1);

namespace YAY\PartnerSDK\Dto\V1;

final class Upload
{
    /**
     * @param positive-int $numberOfImages
     * @param non-empty-string $coverUrl
     * @param null|non-empty-list<string> $photoUrls
     */
    public function __construct(
        public int $numberOfImages,
        public string $coverUrl,
        public ?array $photoUrls = null,
    ) {
        $this->validateNumberOfImages($numberOfImages);
        $this->validateCoverUrl($coverUrl);
        $this->validatePhotoUrls($photoUrls);
    }

    private function validateNumberOfImages(int $numberOfImages): void
    {
        if ($numberOfImages <= 0) {
            throw new \YAY\PartnerSDK\Exception\InvalidArgumentException('Number of images must be positive');
        }

        if ($numberOfImages > 1000) {
            throw new \YAY\PartnerSDK\Exception\InvalidArgumentException('Number of images cannot exceed 1000');
        }
    }

    private function validateCoverUrl(string $coverUrl): void
    {
        if (trim($coverUrl) === '') {
            throw new \YAY\PartnerSDK\Exception\InvalidArgumentException('Cover URL cannot be empty');
        }

        if (!filter_var($coverUrl, FILTER_VALIDATE_URL)) {
            throw new \YAY\PartnerSDK\Exception\InvalidArgumentException("Invalid cover URL: {$coverUrl}");
        }
    }

    /**
     * @param null|array<string> $photoUrls
     */
    private function validatePhotoUrls(?array $photoUrls): void
    {
        if ($photoUrls === null) {
            return;
        }

        if (empty($photoUrls)) {
            throw new \YAY\PartnerSDK\Exception\InvalidArgumentException('Photo URLs array cannot be empty (use null instead)');
        }

        if (count($photoUrls) > 1000) {
            throw new \YAY\PartnerSDK\Exception\InvalidArgumentException('Cannot provide more than 1000 photo URLs');
        }

        foreach ($photoUrls as $index => $url) {
            // @phpstan-ignore function.alreadyNarrowedType
            if (!is_string($url)) {
                throw new \YAY\PartnerSDK\Exception\InvalidArgumentException("Photo URL at index {$index} must be a string");
            }

            if (trim($url) === '') {
                throw new \YAY\PartnerSDK\Exception\InvalidArgumentException("Photo URL at index {$index} cannot be empty");
            }

            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                throw new \YAY\PartnerSDK\Exception\InvalidArgumentException("Invalid photo URL at index {$index}: {$url}");
            }
        }
    }
}
