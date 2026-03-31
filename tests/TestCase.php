<?php

namespace ApiCheck\Api\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use ApiCheck\Api\ApiClient;
use Psr\Http\Message\ResponseInterface;

/**
 * Base test case class with common testing utilities.
 */
abstract class TestCase extends BaseTestCase
{
    /**
     * Create ApiClient with mocked HTTP client.
     *
     * @param array $responses Array of Response objects to return in sequence
     * @return ApiClient
     */
    protected function createApiClientWithMock(array $responses = []): ApiClient
    {
        $mock = new MockHandler($responses);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack, 'verify' => false]);

        $client = new ApiClient($httpClient);
        $client->setApiKey($_ENV['API_CHECK_API_KEY'] ?? 'test-api-key');

        return $client;
    }

    /**
     * Load fixture JSON file.
     *
     * @param string $path Relative path from tests/Fixtures/
     * @return array
     */
    protected function loadFixture(string $path): array
    {
        $fullPath = __DIR__ . '/Fixtures/' . $path;

        if (!file_exists($fullPath)) {
            throw new \RuntimeException("Fixture not found: {$fullPath}");
        }

        $content = file_get_contents($fullPath);
        $decoded = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException("Invalid JSON in fixture: {$fullPath}");
        }

        return $decoded;
    }

    /**
     * Create mock HTTP response.
     *
     * @param int $statusCode HTTP status code
     * @param array $data Response data
     * @param array $headers Response headers
     * @return ResponseInterface
     */
    protected function createMockResponse(
        int $statusCode = 200,
        array $data = [],
        array $headers = []
    ): ResponseInterface {
        $body = json_encode($data);

        return new Response($statusCode, $headers, $body);
    }

    /**
     * Create success response with data wrapper.
     *
     * @param array $data Data to wrap in {"data": ...}
     * @param int $statusCode HTTP status code
     * @return ResponseInterface
     */
    protected function createSuccessResponse(array $data, int $statusCode = 200): ResponseInterface
    {
        return $this->createMockResponse($statusCode, ['data' => $data]);
    }

    /**
     * Create error response.
     *
     * @param string $errorName Error type/name
     * @param string $message Error message
     * @param int $statusCode HTTP status code
     * @param array $additionalFields Additional error fields
     * @return ResponseInterface
     */
    protected function createErrorResponse(
        string $errorName,
        string $message = 'An error occurred',
        int $statusCode = 400,
        array $additionalFields = []
    ): ResponseInterface {
        $data = array_merge([
            'error' => true,
            'name' => $errorName,
            'message' => $message,
        ], $additionalFields);

        return $this->createMockResponse($statusCode, $data);
    }

    /**
     * Assert that a stdClass object has expected properties.
     *
     * @param \stdClass $object Object to check
     * @param array $expectedProps Array of property names
     */
    protected function assertObjectHasProperties(\stdClass $object, array $expectedProps): void
    {
        foreach ($expectedProps as $prop) {
            $this->assertObjectHasProperty($prop, $object);
        }
    }

    /**
     * Assert that a stdClass object has specific property value.
     *
     * @param string $property Property name
     * @param mixed $expected Expected value
     * @param \stdClass $object Object to check
     */
    protected function assertObjectHasPropertyWithValue(string $property, $expected, \stdClass $object): void
    {
        $this->assertObjectHasProperty($property, $object);
        $this->assertEquals($expected, $object->$property);
    }
}
