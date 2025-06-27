<?php

declare(strict_types=1);

namespace YAY\PartnerSDK\Tests\Unit;

use Hamcrest\Matchers;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpClient\TraceableHttpClient;
use Webforge\ObjectAsserter\AssertionsTrait;
use YAY\PartnerSDK\Client;
use YAY\PartnerSDK\Configuration;
use YAY\PartnerSDK\Dto\V1;
use YAY\PartnerSDK\Exception\ServerErrorException;
use YAY\PartnerSDK\Result\CreatedProjectResponse;
use YAY\PartnerSDK\Tests\Fixtures;

final class ClientTest extends TestCase
{
    use AssertionsTrait;

    private Client $client;

    private MockHttpClient $mockHttpClient;
    private TraceableHttpClient $innerClient;

    /**
     * @param list<MockResponse> $mockedResponses
     */
    public function setupClient(array $mockedResponses, Configuration $config = null): Client
    {
        $this->mockHttpClient = new MockHttpClient($mockedResponses);
        $this->innerClient = new TraceableHttpClient($this->mockHttpClient);
        $this->client = new Client(
            $config ?? new Configuration(
                username: 'test_user',
                password: 'test_pass',
                userAgent: 'TestApp/1.0',
                environment: 'sandbox'
            ),
            $this->innerClient
        );
        return $this->client;
    }

    public function testCreateProjectSuccess(): void
    {
        $expectedResponseData = [
            'projectId' => '550e8400-e29b-41d4-a716-446655440000',
            'redirectUrl' => 'https://checkout.yaymemories.com/projects/550e8400-e29b-41d4-a716-446655440000/select'
        ];

        $mockResponse = new MockResponse(
            body: json_encode($expectedResponseData, JSON_THROW_ON_ERROR),
            info: [
                'http_code' => 201,
                'url' => 'https://sandbox.yaymemories.com/papi/projects',
                'debug' => 'Request successful'
            ]
        );

        $client = $this->setupClient([$mockResponse]);

        $result = $client->createProject($this->createValidProjectRequest());

        //$this->assertInstanceOf(CreatedProjectResponse::class, $result);
        $this->assertSame('550e8400-e29b-41d4-a716-446655440000', $result->result->projectId);
        $this->assertSame(
            'https://checkout.yaymemories.com/projects/550e8400-e29b-41d4-a716-446655440000/select',
            $result->result->redirectUrl
        );
    }

    public function testCreateProjectWithValidationError(): void
    {
        $errorResponseData = [
            'type' => 'https://httpstatus.es/400',
            'title' => 'Bad Request',
            'detail' => 'Invalid customer email format',
            'status' => 400,
            'instance' => '/projects',
            'errors' => [
                'customer.email' => ['The email field must be a valid email address.']
            ]
        ];

        $mockResponse = new MockResponse(
            body: json_encode($errorResponseData, JSON_THROW_ON_ERROR),
            info: [
                'http_code' => 400,
                'url' => 'https://sandbox.yaymemories.com/papi/projects'
            ]
        );

        $client = $this->setupClient([$mockResponse]);

        $this->expectException(ServerErrorException::class);
        try {
            $client->createProject($this->createValidProjectRequest());
        } catch (ServerErrorException $e) {
            $this->assertSame('https://httpstatus.es/400', $e->problem->type);
            $this->assertSame('Bad Request', $e->problem->title);
            $this->assertSame('Invalid customer email format', $e->problem->detail);
            $this->assertSame(400, $e->problem->status);
            $this->assertSame('/projects', $e->problem->instance);

            throw $e;
        }
    }

    public function testCreateProjectSendsCorrectRequestData(): void
    {
        $client = $this->setupClient([new MockResponse(
            body: json_encode([
                'projectId' => '550e8400-e29b-41d4-a716-446655440000',
                'redirectUrl' => 'https://checkout.yaymemories.com/projects/test/select'
            ], JSON_THROW_ON_ERROR),
            info: ['http_code' => 201]
        )]);

        $client->createProject($this->createValidProjectRequest());

        $this->assertThatArray($this->innerClient->getTracedRequests())
            ->length(1)
            ->key(0)
                ->key('url', 'https://sandbox.yaymemories.com/papi/projects')->end()
                ->key('method', 'POST')->end()
                ->key('options')
                    ->key('json')
                        ->key('title', "Sarah & Mike's Wedding Album")->end()
                        ->key('customer')
                            ->key('firstname', 'Sarah')->end()
                            ->key('lastname', 'Mueller')->end()
                            ->key('email', 'sarah.mueller@gmail.com')->end()
                            ->key('address')
                                ->key('line1', 'Musterstraße 123')->end()
                                ->key('line2', 'Apartment 4B')->end()
                                ->key('city', 'Berlin')->end()
                                ->key('postal_code', '10115')->end()
                                ->key('country', 'DE')->end()
                            ->end()
                        ->end()
                        ->key('upload')
                            ->key('numberOfImages', 150)->end()
                            ->key('coverUrl', 'https://my-photo-app.example.com/images/wedding-cover.jpg')->end()
                            ->key('photoUrls', [
                                'https://my-photo-app.example.com/photos/img001.jpg',
                                'https://my-photo-app.example.com/photos/img002.jpg'
                            ])->end()
                        ->end()
                        ->key('locale', 'de_DE')->end()
                    ->end()
                ->end()
            ->end();
    }

    public function testCreateProjectWithoutPhotoUrls(): void
    {
        $client = $this->setupClient([
            new MockResponse(
                body: json_encode([
                    'projectId' => '550e8400-e29b-41d4-a716-446655440000',
                    'redirectUrl' => 'https://checkout.yaymemories.com/projects/test/select'
                ], JSON_THROW_ON_ERROR),
                info: ['http_code' => 201]
            )
        ]);

        $upload = new V1\Upload(
            numberOfImages: 150,
            coverUrl: 'https://my-photo-app.example.com/images/wedding-cover.jpg'
        );

        $client->createProject(
            new V1\CreateProjectRequest(
                title: "Test Project",
                customer: Fixtures::createValidCustomer(),
                upload: $upload,
                locale: 'de_DE'
            )
        );

        $requestsMade = $this->innerClient->getTracedRequests();
        $this->assertThatArray($requestsMade)->length(1)
            ->key(0)
                ->key('options')
                    ->key('json')
                        ->key('upload', Matchers::not(Matchers::hasKey('photoUrls')));
    }

    public function testCreateProjectWithProductionEnvironment(): void
    {
        $productionConfig = new Configuration(
            username: 'prod_user',
            password: 'prod_pass',
            userAgent: 'ProdApp/1.0',
            environment: 'production'
        );

        $client = $this->setupClient([
            new MockResponse(
                body: json_encode([
                    'projectId' => '550e8400-e29b-41d4-a716-446655440000',
                    'redirectUrl' => 'https://checkout.yaymemories.com/projects/test/select',
                ], JSON_THROW_ON_ERROR),
                info: ['http_code' => 201],
            )],
            $productionConfig
        );

        $client->createProject($this->createValidProjectRequest());

        $this->assertThatArray($this->innerClient->getTracedRequests())
            ->length(1)
            ->key(0)
                ->key('url', 'https://portal.yaymemories.com/papi/projects')->end()
        ;
    }

    private function createValidProjectRequest(): V1\CreateProjectRequest
    {
        return new V1\CreateProjectRequest(
            title: "Sarah & Mike's Wedding Album",
            customer: Fixtures::createValidCustomer(),
            upload: new V1\Upload(
                numberOfImages: 150,
                coverUrl: 'https://my-photo-app.example.com/images/wedding-cover.jpg',
                photoUrls: [
                    'https://my-photo-app.example.com/photos/img001.jpg',
                    'https://my-photo-app.example.com/photos/img002.jpg'
                ]
            ),
            locale: 'de_DE'
        );
    }

}
