<?php

declare(strict_types=1);

namespace YAY\PartnerSDK\Dto\V1;

final class CreateProjectRequest
{
    public function __construct(
        public string $title,
        public Customer $customer,
        public Upload $upload,
        public string $locale,
    ) {
        $this->validateTitle($title);
        $this->validateLocale($locale);
    }

    private function validateTitle(string $title): void
    {
        if (trim($title) === '') {
            throw new \YAY\PartnerSDK\Exception\InvalidArgumentException('Project title cannot be empty');
        }
    }

    private function validateLocale(string $locale): void
    {
        // Validate locale format (e.g., "de_DE", "en_US")
        if (! preg_match('/^[a-z]{2}_[A-Z]{2}$/', $locale)) {
            throw new \YAY\PartnerSDK\Exception\InvalidArgumentException(
                "Invalid locale format '{$locale}'. Expected format: 'de_DE' or 'en_US'",
            );
        }
    }
}
