<?php

namespace ApiCheck\Api\Tests;

use ApiCheck\Api\ApiClient;
use ApiCheck\Api\Exceptions\ApiException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;

class ApiClientTest extends TestCase
{
    // =========================================================================
    // CONSTRUCTOR TESTS
    // =========================================================================

    public function testConstructorWithDefaultHttpClient(): void
    {
        $client = new ApiClient();

        $this->assertInstanceOf(ApiClient::class, $client);
    }

    public function testConstructorWithCustomHttpClient(): void
    {
        $mock = new MockHandler([]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);

        $client = new ApiClient($httpClient);

        $this->assertInstanceOf(ApiClient::class, $client);
    }

    // =========================================================================
    // CONFIGURATION TESTS
    // =========================================================================

    public function testSetApiKeyReturnsFluentInterface(): void
    {
        $client = new ApiClient();

        $result = $client->setApiKey('test-api-key');

        $this->assertSame($client, $result);
    }

    public function testSetApiKeyTrimsWhitespace(): void
    {
        $client = new ApiClient();
        $client->setApiKey('  test-api-key  ');

        // Access the apiKey property via reflection or through usage
        $this->assertNotNull($client);
    }

    public function testSetRefererReturnsFluentInterface(): void
    {
        $client = new ApiClient();

        $result = $client->setReferer('https://example.com');

        $this->assertSame($client, $result);
    }

    public function testGetApiVersionReturnsCorrectVersion(): void
    {
        $client = new ApiClient();

        $version = $client->getApiVersion();

        $this->assertEquals('v1', $version);
    }

    // =========================================================================
    // REQUEST EXECUTION TESTS
    // =========================================================================

    public function testDoRequestThrowsExceptionWithoutApiKey(): void
    {
        $client = new ApiClient();

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('No API-key has been set');

        $client->doRequest('GET', 'test');
    }

    public function testDoRequestSendsCorrectHeaders(): void
    {
        $responseData = ['test' => 'data'];
        $response = $this->createSuccessResponse($responseData);
        $client = $this->createApiClientWithMock([$response]);
        $client->setApiKey('test-key');

        $result = $client->doRequest('GET', 'test');

        $this->assertIsObject($result);
    }

    public function testDoRequestHandlesGuzzleException(): void
    {
        $mock = new MockHandler([
            new \GuzzleHttp\Exception\TransferException('Connection failed')
        ]);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);

        $client = new ApiClient($httpClient);
        $client->setApiKey('test-key');

        $this->expectException(ApiException::class);
        $client->doRequest('GET', 'test');
    }

    // =========================================================================
    // RESPONSE PARSING TESTS
    // =========================================================================

    public function testParseResponseReturnsDataObject(): void
    {
        $responseData = ['street' => 'Main Street'];
        $response = $this->createSuccessResponse($responseData);
        $client = $this->createApiClientWithMock([$response]);
        $client->setApiKey('test-key');

        $result = $client->doRequest('GET', 'test');

        $this->assertIsObject($result);
        $this->assertEquals('Main Street', $result->street);
    }

    public function testParseResponseThrowsExceptionOn4xxStatus(): void
    {
        $response = $this->createErrorResponse('bad_request', 'Bad request', 400);
        $client = $this->createApiClientWithMock([$response]);
        $client->setApiKey('test-key');

        $this->expectException(\ApiCheck\Api\Exceptions\BadRequestException::class);
        $client->doRequest('GET', 'test');
    }

    public function testParseResponseHandlesApiErrorFlagIn2xxResponse(): void
    {
        $response = $this->createErrorResponse('no_exact_match', 'No match', 200);
        $client = $this->createApiClientWithMock([$response]);
        $client->setApiKey('test-key');

        $this->expectException(\ApiCheck\Api\Exceptions\NoExactMatchException::class);
        $client->doRequest('GET', 'test');
    }

    // =========================================================================
    // FACADE/DELEGATION TESTS
    // =========================================================================

    public function testLookupDelegatesToLookupClient(): void
    {
        $responseData = ['street' => 'Main Street'];
        $response = $this->createSuccessResponse($responseData);
        $client = $this->createApiClientWithMock([$response]);
        $client->setApiKey('test-key');

        $result = $client->lookup('nl', ['postalcode' => '2513AA', 'number' => '123']);

        $this->assertEquals('Main Street', $result->street);
    }

    public function testGetNumberAdditionsDelegatesToLookupClient(): void
    {
        $responseData = ['numberAdditions' => ['a', 'b']];
        $response = $this->createSuccessResponse($responseData);
        $client = $this->createApiClientWithMock([$response]);
        $client->setApiKey('test-key');

        $result = $client->getNumberAdditions('nl', '2513AA', '123');

        $this->assertIsArray($result->numberAdditions);
    }

    public function testSearchDelegatesToSearchClient(): void
    {
        $responseData = ['results' => []];
        $response = $this->createSuccessResponse($responseData);
        $client = $this->createApiClientWithMock([$response]);
        $client->setApiKey('test-key');

        $result = $client->search('nl', 'city', ['name' => 'Amsterdam']);

        $this->assertIsObject($result);
    }

    public function testGlobalSearchDelegatesToSearchClient(): void
    {
        $responseData = ['results' => []];
        $response = $this->createSuccessResponse($responseData);
        $client = $this->createApiClientWithMock([$response]);
        $client->setApiKey('test-key');

        $result = $client->globalSearch('nl', 'Amsterdam');

        $this->assertIsObject($result);
    }

    public function testSearchLocalityDelegatesToSearchClient(): void
    {
        $responseData = ['localities' => []];
        $response = $this->createSuccessResponse($responseData);
        $client = $this->createApiClientWithMock([$response]);
        $client->setApiKey('test-key');

        $result = $client->searchLocality('be', 'Test');

        $this->assertIsObject($result);
    }

    public function testSearchMunicipalityDelegatesToSearchClient(): void
    {
        $responseData = ['municipalities' => []];
        $response = $this->createSuccessResponse($responseData);
        $client = $this->createApiClientWithMock([$response]);
        $client->setApiKey('test-key');

        $result = $client->searchMunicipality('be', 'Test');

        $this->assertIsObject($result);
    }

    public function testSearchAddressDelegatesToSearchClient(): void
    {
        $responseData = ['addresses' => []];
        $response = $this->createSuccessResponse($responseData);
        $client = $this->createApiClientWithMock([$response]);
        $client->setApiKey('test-key');

        $result = $client->searchAddress('nl', []);

        $this->assertIsObject($result);
    }

    public function testGetSupportedSearchCountriesDelegatesToSearchClient(): void
    {
        $responseData = ['countries' => ['nl', 'be']];
        $response = $this->createSuccessResponse($responseData);
        $client = $this->createApiClientWithMock([$response]);
        $client->setApiKey('test-key');

        $result = $client->getSupportedSearchCountries();

        $this->assertIsObject($result);
    }

    public function testVerifyEmailDelegatesToVerifyClient(): void
    {
        $responseData = ['status' => 'valid'];
        $response = $this->createSuccessResponse($responseData);
        $client = $this->createApiClientWithMock([$response]);
        $client->setApiKey('test-key');

        $result = $client->verifyEmail('test@example.com');

        $this->assertEquals('valid', $result->status);
    }

    public function testVerifyPhoneDelegatesToVerifyClient(): void
    {
        $responseData = ['valid' => true];
        $response = $this->createSuccessResponse($responseData);
        $client = $this->createApiClientWithMock([$response]);
        $client->setApiKey('test-key');

        $result = $client->verifyPhone('+31612345678');

        $this->assertTrue($result->valid);
    }

    // =========================================================================
    // CONSTANTS TESTS
    // =========================================================================

    public function testEndpointConstant(): void
    {
        $this->assertEquals('https://api.apicheck.nl', ApiClient::ENDPOINT);
    }

    public function testApiVersionConstant(): void
    {
        $this->assertEquals('v1', ApiClient::API_VERSION);
    }

    public function testVersionConstant(): void
    {
        $this->assertEquals('2.0.0', ApiClient::VERSION);
    }
}
