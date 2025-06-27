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
    ) {
        $this->validateName($firstname, 'firstname');
        $this->validateName($lastname, 'lastname');
        $this->validateEmail($email);
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
}
