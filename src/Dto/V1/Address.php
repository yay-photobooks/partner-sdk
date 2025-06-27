<?php

declare(strict_types=1);

namespace YAY\PartnerSDK\Dto\V1;

final class Address
{
    public function __construct(
        public string $line1,
        public string $line2,
        public string $city,
        public string $postalCode,
        public string $country,
    ) {
        $this->validateLine1($line1);
        $this->validateCity($city);
        $this->validateCountry($country);
    }

    private function validateLine1(string $line1): void
    {
        if (trim($line1) === '') {
            throw new \YAY\PartnerSDK\Exception\InvalidArgumentException('Address line1 cannot be empty');
        }
    }

    private function validateCity(string $city): void
    {
        if (trim($city) === '') {
            throw new \YAY\PartnerSDK\Exception\InvalidArgumentException('City cannot be empty');
        }

        if (mb_strlen($city) > 100) {
            throw new \YAY\PartnerSDK\Exception\InvalidArgumentException('City cannot exceed 100 characters');
        }
    }

    private function validateCountry(string $country): void
    {
        // Validate ISO 3166-1 alpha-2 country code
        if (!preg_match('/^[A-Z]{2}$/', $country)) {
            throw new \YAY\PartnerSDK\Exception\InvalidArgumentException(
                "Invalid country code '{$country}'. Must be ISO 3166-1 alpha-2 format (e.g., 'DE', 'US')"
            );
        }
    }
}
