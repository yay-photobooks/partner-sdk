<?php

declare(strict_types=1);

namespace YAY\PartnerSDK;

use YAY\PartnerSDK\Exception\InvalidArgumentException;
use YAY\PartnerSDK\Exception\RuntimeException;

final class Configuration
{
    private const SANDBOX_BASE_URL = 'https://sandbox.yaymemories.com/papi/';
    private const PRODUCTION_BASE_URL = 'https://portal.yaymemories.com/papi/';

    public function __construct(
        private string $username,
        private string $password,
        private string $userAgent,
        private string $environment,
    ) {
        $this->validateEnvironment($environment);
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

        $environment = getenv('YAY_PARTNER_ENVIRONMENT');
        if ($environment === false) {
            throw new InvalidArgumentException('YAY_PARTNER_ENVIRONMENT environment variable is not set');
        }

        return new self($username, $password, $userAgent, $environment);
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

    public function getEnvironment(): string
    {
        return $this->environment;
    }

    public function getBaseUrl(): string
    {
        return match ($this->environment) {
            'sandbox' => self::SANDBOX_BASE_URL,
            'production' => self::PRODUCTION_BASE_URL,
            default => throw new RuntimeException("Invalid environment: {$this->environment}")
        };
    }

    public function isSandbox(): bool
    {
        return $this->environment === 'sandbox';
    }

    public function isProduction(): bool
    {
        return $this->environment === 'production';
    }

    private function validateEnvironment(string $environment): void
    {
        if (!in_array($environment, ['sandbox', 'production'], true)) {
            throw new RuntimeException(
                "Invalid environment '{$environment}'. Must be 'sandbox' or 'production'."
            );
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
