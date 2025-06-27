<?php

declare(strict_types=1);

namespace YAY\PartnerSDK\Result;

use Symfony\Contracts\HttpClient\ResponseInterface;
use YAY\PartnerSDK\Dto\V1\CreatedProject;

/**
 * @phpstan-import-type ResponseBodyType from \YAY\PartnerSDK\Types
 */
final class CreatedProjectResponse
{
    /**
     * @param true $success
     */
    private function __construct(
        public bool $success,
        public CreatedProject $result,
        public ResponseInterface $internal,
    ) {}

    /** @param ResponseBodyType $responseBody */
    public static function fromResponse(
        array $responseBody,
        ResponseInterface $response
    ): self {
        return new self(
            success: true,
            result: CreatedProject::fromArray($responseBody),
            internal: $response,
        );
    }

    public function getDebug(): string
    {
        $debug = $this->internal->getInfo('debug');
        assert(is_string($debug));

        return $debug;
    }
}
