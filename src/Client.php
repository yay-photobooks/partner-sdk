<?php

declare(strict_types=1);

namespace YAY\PartnerSDK;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use YAY\PartnerSDK\Dto\V1\CreateProjectRequest;
use YAY\PartnerSDK\Exception\ServerErrorException;
use YAY\PartnerSDK\Result\CreatedProjectResponse;

/**
 * @phpstan-import-type ResponseBodyType from Types
 */

final class Client
{
    private HttpClientInterface $httpClient;
    private Configuration $config;

    public function __construct(Configuration $config, ?HttpClientInterface $httpClient = null)
    {
        $this->config = $config;
        $this->httpClient = $httpClient ?? HttpClient::create([
            'auth_basic' => [$this->config->getUsername(), $this->config->getPassword()],
            'headers' => [
                'User-Agent' => $this->config->getUserAgent(),
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);
    }

    public function createProject(CreateProjectRequest $project): CreatedProjectResponse
    {
        $requestData = [
            'title' => $project->title,
            'customer' => [
                'firstname' => $project->customer->firstname,
                'lastname' => $project->customer->lastname,
                'email' => $project->customer->email,
                'address' => [
                    'line1' => $project->customer->address->line1,
                    'line2' => $project->customer->address->line2,
                    'city' => $project->customer->address->city,
                    'postal_code' => $project->customer->address->postalCode,
                    'country' => $project->customer->address->country,
                ],
            ],
            'upload' => [
                'numberOfImages' => $project->upload->numberOfImages,
                'coverUrl' => $project->upload->coverUrl,
                // see photoUrls below
            ],
            'locale' => $project->locale,
        ];

        if ($project->upload->photoUrls !== null) {
            $requestData['upload']['photoUrls'] = $project->upload->photoUrls;
        }

        $response = $this->httpClient->request('POST', $this->config->getBaseUrl() . 'projects', [
            'json' => $requestData
        ]);

        if ($response->getStatusCode() === 401) {
            throw ServerErrorException::unauthorized($response);
        }

        if ($response->getStatusCode() !== 201) {
            $raw = $response->getContent(false);

            if (empty($raw)) {
                throw ServerErrorException::unknownHttpProblem($response);
            }

            $body = $response->toArray(false);
            throw ServerErrorException::fromResponse($body, $response);
        }

        $body = $response->toArray();
        return CreatedProjectResponse::fromResponse($body, $response);
    }
}
