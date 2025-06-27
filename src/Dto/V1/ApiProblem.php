<?php

declare(strict_types=1);

namespace YAY\PartnerSDK\Dto\V1;

/**
 * RFC 7807 Problem Details for HTTP APIs
 * @see https://tools.ietf.org/html/rfc7807
 * @phpstan-import-type ResponseBodyType from \YAY\PartnerSDK\Types
 */
final class ApiProblem
{
    public function __construct(
        public string $type,
        public string $title,
        public string $detail,
        public int $status,
        public ?string $instance = null,
        /** @var ResponseBodyType */
        public array $additional = [],
    ) {
        $this->validateStatus($status);
    }

    /**
     * @param ResponseBodyType $body
     */
    public static function fromArray(array $body): self
    {
        if (!is_string($body['type'] ?? null)) {
            $body['type'] = 'unknown';
        }
        if (!is_string($body['title'] ?? null)) {
            $body['title'] = 'Unknown Error';
        }
        if (!is_string($body['detail'] ?? null)) {
            $body['detail'] = 'An unknown error occurred';
        }
        if (!is_int($body['status'] ?? null)) {
            $body['status'] = 99999;
        }
        if (!is_string($body['instance'] ?? null)) {
            $body['instance'] = null;
        }

        return new self(
            type: $body['type'],
            title: $body['title'],
            detail: $body['detail'],
            status: $body['status'],
            instance: $body['instance'],
            additional: array_filter($body, fn ($key) => !in_array($key, [
                'type', 'title', 'detail', 'status', 'instance'
            ], true), ARRAY_FILTER_USE_KEY)
        );
    }

    private function validateStatus(int $status): void
    {
        if ($status < 100 || $status > 599) {
            throw new \YAY\PartnerSDK\Exception\InvalidArgumentException("Invalid HTTP status code: {$status}");
        }
    }

    /**
     * Check if this is a client error (4xx)
     */
    public function isClientError(): bool
    {
        return $this->status >= 400 && $this->status < 500;
    }

    /**
     * Check if this is a server error (5xx)
     */
    public function isServerError(): bool
    {
        return $this->status >= 500;
    }

    /**
     * Check if this is a validation error
     */
    public function isValidationError(): bool
    {
        return $this->status === 400 || str_contains($this->type, 'validation');
    }

    /**
     * Check if this is an authentication error
     */
    public function isAuthenticationError(): bool
    {
        return $this->status === 401;
    }
}
