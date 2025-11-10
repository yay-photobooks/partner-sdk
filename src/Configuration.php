<?php

declare(strict_types=1);

namespace YAY\PartnerSDK;

use YAY\PartnerSDK\Exception\InvalidArgumentException;
use YAY\PartnerSDK\Exception\RuntimeException;

final class Configuration
{
    public function __construct(
        private string $username,
        private string $password,
        private string $userAgent,
        private string $baseUrl,
    ) {
        $this->validateBaseUrl($baseUrl);
        $this->validateUserAgent($userAgent);
    }

    public static function fromEnvironment(): self
    {
        $username = getenv('YAY_PARTNER_USERNAME');
        if ($username === false) {
            throw new InvalidArgumentException('YAY_PARTNER_USERNAME environment variable is not set');
        }

        $password = getenv('YAY_PARTNER_PASSWORD');
        if ($password === false) {
            throw new InvalidArgumentException('YAY_PARTNER_PASSWORD environment variable is not set');
        }

        $userAgent = getenv('YAY_PARTNER_USER_AGENT');
        if ($userAgent === false) {
            throw new InvalidArgumentException('YAY_PARTNER_USER_AGENT environment variable is not set');
        }

        $baseUrl = getenv('YAY_PARTNER_BASE_URL');
        if ($baseUrl === false) {
            throw new InvalidArgumentException('YAY_PARTNER_BASE_URL environment variable is not set');
        }

        return new self($username, $password, $userAgent, $baseUrl);
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getUserAgent(): string
    {
        return $this->userAgent;
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    private function validateBaseUrl(string $baseUrl): void
    {
        if (trim($baseUrl) === '') {
            throw new RuntimeException('Base URL cannot be empty');
        }

        if (!filter_var($baseUrl, FILTER_VALIDATE_URL)) {
            throw new RuntimeException("Invalid base URL format: '{$baseUrl}'");
        }

        if (!str_starts_with($baseUrl, 'https://') && !str_starts_with($baseUrl, 'http://')) {
            throw new RuntimeException("Base URL must start with http:// or https://: '{$baseUrl}'");
        }
    }

    private function validateUserAgent(string $userAgent): void
    {
        if (trim($userAgent) === '') {
            throw new RuntimeException('User agent cannot be empty');
        }

        // Basic user agent format validation (should contain at least one slash)
        if (!str_contains($userAgent, '/')) {
            throw new RuntimeException(
                "Invalid user agent format '{$userAgent}'. Expected format: 'AppName/Version'"
            );
        }
    }
}
