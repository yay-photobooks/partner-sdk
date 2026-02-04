<?php

declare(strict_types=1);

namespace YAY\PartnerSDK\Dto\V1;

final class Customer
{
    public function __construct(
        public string $firstname,
        public string $lastname,
        public string $email,
        public Address $address,
        public ?string $phone = null,
    ) {
        $this->validateName($firstname, 'firstname');
        $this->validateName($lastname, 'lastname');
        $this->validateEmail($email);
        if ($phone !== null) {
            $this->validatePhone($phone);
        }
    }

    private function validateName(string $name, string $field): void
    {
        if (trim($name) === '') {
            throw new \YAY\PartnerSDK\Exception\InvalidArgumentException("Customer {$field} cannot be empty");
        }

        if (mb_strlen($name) > 100) {
            throw new \YAY\PartnerSDK\Exception\InvalidArgumentException("Customer {$field} cannot exceed 100 characters");
        }
    }

    private function validateEmail(string $email): void
    {
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \YAY\PartnerSDK\Exception\InvalidArgumentException("Invalid email address: {$email}");
        }

        if ($email !== strtolower($email)) {
            throw new \YAY\PartnerSDK\Exception\InvalidArgumentException("Email address must be lowercase: {$email}");
        }
    }

    private function validatePhone(string $phone): void
    {
        if ($phone != null && $phone != "" && ! preg_match('/^\+[0-9]{7,15}$/', $phone)) {
            throw new \YAY\PartnerSDK\Exception\InvalidArgumentException("Phone must be in E.164 format (e.g. +4917612345678): {$phone}");
        }
    }
}
