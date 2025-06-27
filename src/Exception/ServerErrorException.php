<?php declare(strict_types=1);

namespace YAY\PartnerSDK\Exception;

use Symfony\Contracts\HttpClient\ResponseInterface;
use YAY\PartnerSDK\Dto\V1\ApiProblem;

/**
 * @phpstan-import-type ResponseBodyType from \YAY\PartnerSDK\Types
 */
class ServerErrorException extends \Exception
{
    private function __construct(
        public ApiProblem $problem,
        public ResponseInterface $response,
    ) {
        parent::__construct(
            sprintf('Server Returned: %s with message %s', $this->problem->status, $this->problem->title),
        );
    }

    /**
     * @param ResponseBodyType $responseBody
     */
    public static function fromResponse(
        array $responseBody,
        ResponseInterface $response
    ): self {
        return new self(
            problem: ApiProblem::fromArray($responseBody),
            response: $response,
        );
    }

    public function getDebug(): string
    {
        $debug = $this->response->getInfo('debug');
        assert(is_string($debug));

        return $debug;
    }
}
