<?php

declare(strict_types=1);

namespace YAY\PartnerSDK\Dto\V1;

/**
 * @phpstan-import-type ResponseBodyType from \YAY\PartnerSDK\Types
 */

final class CreatedProject
{
    public function __construct(
        public string $projectId,
        public string $redirectUrl,
    ) {
        $this->validateProjectId($projectId);
        $this->validateRedirectUrl($redirectUrl);
    }

    /**
     * @param ResponseBodyType $body
     */
    public static function fromArray(array $body): self
    {
        if (!isset($body['projectId']) || !is_string($body['projectId'])) {
            throw new \YAY\PartnerSDK\Exception\InvalidArgumentException('Missing or invalid projectId in response');
        }

        if (!isset($body['redirectUrl']) || !is_string($body['redirectUrl'])) {
            throw new \YAY\PartnerSDK\Exception\InvalidArgumentException('Missing or invalid redirectUrl in response');
        }

        return new self($body['projectId'], $body['redirectUrl']);
    }

    private function validateProjectId(string $projectId): void
    {
        // Validate UUID v4 format
        if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $projectId)) {
            throw new \YAY\PartnerSDK\Exception\InvalidArgumentException("Invalid project ID format: {$projectId}");
        }
    }

    private function validateRedirectUrl(string $redirectUrl): void
    {
        if (!filter_var($redirectUrl, FILTER_VALIDATE_URL)) {
            throw new \YAY\PartnerSDK\Exception\InvalidArgumentException("Invalid redirect URL: {$redirectUrl}");
        }
    }
}
