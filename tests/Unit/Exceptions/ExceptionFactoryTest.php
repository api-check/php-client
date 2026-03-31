<?php

namespace ApiCheck\Api\Tests\Exceptions;

use ApiCheck\Api\Tests\TestCase;
use ApiCheck\Api\Exceptions\ApiException;
use ApiCheck\Api\Exceptions\BadRequestException;
use ApiCheck\Api\Exceptions\UnauthorizedException;
use ApiCheck\Api\Exceptions\AccessDeniedException;
use ApiCheck\Api\Exceptions\PageNotFoundException;
use ApiCheck\Api\Exceptions\NotFoundException;
use ApiCheck\Api\Exceptions\UnprocessableEntityException;
use ApiCheck\Api\Exceptions\InternalServerErrorException;
use ApiCheck\Api\Exceptions\NoExactMatchException;
use ApiCheck\Api\Exceptions\ApiKeyInvalidException;
use ApiCheck\Api\Exceptions\ApiKeyExhaustedException;
use ApiCheck\Api\Exceptions\HostNotAllowedException;
use ApiCheck\Api\Exceptions\ApiKeyHeaderException;
use GuzzleHttp\Psr7\Response;

class ExceptionFactoryTest extends TestCase
{
    // =========================================================================
    // APIEXCEPTION::CREATE() TESTS
    // =========================================================================

    public function testCreateReturnsApiExceptionInstance(): void
    {
        $exception = ApiException::create('Test error message');
        $this->assertInstanceOf(ApiException::class, $exception);
        $this->assertEquals('Test error message', $exception->getMessage());
    }

    // =========================================================================
    // APIEXCEPTION::CREATEFROMRESPONSE() - HTTP STATUS CODE TESTS
    // =========================================================================

    public function testCreateFromResponseWith400ReturnsBadRequestException(): void
    {
        $response = $this->createErrorResponse('bad_request', 'Bad request', 400);
        $exception = ApiException::createFromResponse($response);

        $this->assertInstanceOf(BadRequestException::class, $exception);
        $this->assertEquals(400, $exception->getCode());
        $this->assertStringContainsString('Bad request', $exception->getMessage());
    }

    public function testCreateFromResponseWith401ReturnsUnauthorizedException(): void
    {
        $response = $this->createErrorResponse('unauthorized', 'Unauthorized', 401);
        $exception = ApiException::createFromResponse($response);

        $this->assertInstanceOf(UnauthorizedException::class, $exception);
        $this->assertEquals(401, $exception->getCode());
        $this->assertStringContainsString('Unauthorized', $exception->getMessage());
    }

    public function testCreateFromResponseWith403ReturnsAccessDeniedException(): void
    {
        $response = $this->createErrorResponse('forbidden', 'Access denied', 403);
        $exception = ApiException::createFromResponse($response);

        $this->assertInstanceOf(AccessDeniedException::class, $exception);
        $this->assertEquals(403, $exception->getCode());
        $this->assertStringContainsString('Access is denied', $exception->getMessage());
    }

    public function testCreateFromResponseWith404ReturnsPageNotFoundException(): void
    {
        $response = $this->createErrorResponse('not_found', 'Not found', 404);
        $exception = ApiException::createFromResponse($response);

        $this->assertInstanceOf(PageNotFoundException::class, $exception);
        $this->assertEquals(404, $exception->getCode());
        $this->assertStringContainsString('does not exists', $exception->getMessage());
    }

    public function testCreateFromResponseWith422ReturnsUnprocessableEntityException(): void
    {
        $response = $this->createErrorResponse('validation', 'Validation failed', 422);
        $exception = ApiException::createFromResponse($response);

        $this->assertInstanceOf(UnprocessableEntityException::class, $exception);
        $this->assertEquals(422, $exception->getCode());
        $this->assertStringContainsString('Unprocessable Entity', $exception->getMessage());
    }

    public function testCreateFromResponseWith422ExtractsDescription(): void
    {
        $response = $this->createErrorResponse(
            'validation',
            'Validation failed',
            422,
            ['description' => 'The postalcode field is required.']
        );
        $exception = ApiException::createFromResponse($response);

        $this->assertInstanceOf(UnprocessableEntityException::class, $exception);
        $this->assertStringContainsString('The postalcode field is required', $exception->getMessage());
    }

    public function testCreateFromResponseWith422FallsBackToMessage(): void
    {
        $response = $this->createErrorResponse(
            'validation',
            'Custom validation message',
            422,
            ['message' => 'The postalcode field is required.']
        );
        $exception = ApiException::createFromResponse($response);

        $this->assertInstanceOf(UnprocessableEntityException::class, $exception);
        // The code prioritizes 'description' over 'message', so if description is missing,
        // it falls back to message
        $this->assertStringContainsString('The postalcode field is required', $exception->getMessage());
    }

    public function testCreateFromResponseWith500ReturnsInternalServerErrorException(): void
    {
        $response = $this->createErrorResponse('internal_server_error', 'Server error', 500);
        $exception = ApiException::createFromResponse($response);

        $this->assertInstanceOf(InternalServerErrorException::class, $exception);
        $this->assertEquals(500, $exception->getCode());
        $this->assertStringContainsString('Internal server error', $exception->getMessage());
    }

    public function testCreateFromResponseWithUnknownStatusReturnsGenericApiException(): void
    {
        $response = $this->createErrorResponse('unknown', 'Unknown error', 418);
        $exception = ApiException::createFromResponse($response);

        $this->assertInstanceOf(ApiException::class, $exception);
        $this->assertNotInstanceOf(BadRequestException::class, $exception);
        $this->assertEquals(418, $exception->getCode());
    }

    // =========================================================================
    // API-SPECIFIC ERROR TYPES
    // =========================================================================

    public function testCreateFromResponseWithNoMatchReturnsNotFoundException(): void
    {
        $response = $this->createErrorResponse('no_match', 'No matches found', 404);
        $exception = ApiException::createFromResponse($response);

        $this->assertInstanceOf(NotFoundException::class, $exception);
        $this->assertEquals(404, $exception->getCode());
        $this->assertStringContainsString('No matches found', $exception->getMessage());
    }

    public function testCreateFromResponseWithNoExactMatchReturnsNoExactMatchException(): void
    {
        $numberAdditions = ['a', 'b', '1', '2'];
        $response = $this->createErrorResponse(
            'no_exact_match',
            'No exact match found',
            200,
            ['numberAdditions' => $numberAdditions]
        );
        $exception = ApiException::createFromResponse($response);

        $this->assertInstanceOf(NoExactMatchException::class, $exception);
        $this->assertEquals(200, $exception->getCode());
        $this->assertEquals($numberAdditions, $exception->getNumberAdditions());
    }

    public function testCreateFromResponseWithNoExactMatchWithoutNumberAdditions(): void
    {
        $response = $this->createErrorResponse('no_exact_match', 'No exact match found', 200);
        $exception = ApiException::createFromResponse($response);

        $this->assertInstanceOf(NoExactMatchException::class, $exception);
        $this->assertEquals(200, $exception->getCode());
        $this->assertEquals([], $exception->getNumberAdditions());
    }

    public function testCreateFromResponseWithApiKeyInvalidReturnsApiKeyInvalidException(): void
    {
        $response = $this->createErrorResponse('api_key_invalid', 'Invalid API key', 401);
        $exception = ApiException::createFromResponse($response);

        $this->assertInstanceOf(ApiKeyInvalidException::class, $exception);
        $this->assertEquals(401, $exception->getCode());
        $this->assertStringContainsString('invalid or disabled', $exception->getMessage());
    }

    public function testCreateFromResponseWithApiKeyExhaustedReturnsApiKeyExhaustedException(): void
    {
        $response = $this->createErrorResponse('api_key_exhausted', 'API key exhausted', 402);
        $exception = ApiException::createFromResponse($response);

        $this->assertInstanceOf(ApiKeyExhaustedException::class, $exception);
        $this->assertEquals(402, $exception->getCode());
        $this->assertStringContainsString('exhausted', $exception->getMessage());
    }

    public function testCreateFromResponseWithHostNotAllowedReturnsHostNotAllowedException(): void
    {
        $response = $this->createErrorResponse('host_not_allowed', 'Host not allowed', 403);
        $exception = ApiException::createFromResponse($response);

        $this->assertInstanceOf(HostNotAllowedException::class, $exception);
        $this->assertEquals(403, $exception->getCode());
        $this->assertStringContainsString('not allowed', $exception->getMessage());
    }

    public function testCreateFromResponseWithNoApiKeyHeaderReturnsApiKeyHeaderException(): void
    {
        $response = $this->createErrorResponse('no_api_key_header', 'No API key header', 401);
        $exception = ApiException::createFromResponse($response);

        $this->assertInstanceOf(ApiKeyHeaderException::class, $exception);
        $this->assertEquals(401, $exception->getCode());
        $this->assertStringContainsString('X-API-KEY header', $exception->getMessage());
    }

    // =========================================================================
    // EDGE CASES
    // =========================================================================

    public function testCreateFromResponseWithEmptyBody(): void
    {
        $response = new Response(404, [], '');
        $exception = ApiException::createFromResponse($response);

        $this->assertInstanceOf(PageNotFoundException::class, $exception);
    }

    public function testCreateFromResponseWithInvalidJson(): void
    {
        $response = new Response(404, [], 'invalid json');
        $exception = ApiException::createFromResponse($response);

        // Falls back to HTTP status code handling
        $this->assertInstanceOf(PageNotFoundException::class, $exception);
    }

    public function testCreateFromResponseWithErrorFlagFalse(): void
    {
        $response = $this->createSuccessResponse(['data' => 'value'], 404);
        $exception = ApiException::createFromResponse($response);

        // Should fall back to HTTP status code
        $this->assertInstanceOf(PageNotFoundException::class, $exception);
    }

    public function testCreateFromResponseWithPreviousException(): void
    {
        $previous = new \Exception('Previous error');
        $response = $this->createErrorResponse('bad_request', 'Bad request', 400);
        $exception = ApiException::createFromResponse($response, $previous);

        $this->assertInstanceOf(BadRequestException::class, $exception);
        $this->assertSame($previous, $exception->getPrevious());
    }

    // =========================================================================
    // 2XX STATUS WITH API ERROR FLAG
    // =========================================================================

    public function testCreateFromResponseWith200AndApiErrorReturnsSpecificException(): void
    {
        $response = $this->createErrorResponse('no_match', 'No matches found', 200);
        $exception = ApiException::createFromResponse($response);

        $this->assertInstanceOf(NotFoundException::class, $exception);
        $this->assertEquals(200, $exception->getCode());
    }

    public function testCreateFromResponseWith200AndNoExactMatchReturnsNoExactMatchException(): void
    {
        $response = $this->createErrorResponse(
            'no_exact_match',
            'No exact match found',
            200,
            ['numberAdditions' => ['a', 'b']]
        );
        $exception = ApiException::createFromResponse($response);

        $this->assertInstanceOf(NoExactMatchException::class, $exception);
        $this->assertEquals(200, $exception->getCode());
        $this->assertEquals(['a', 'b'], $exception->getNumberAdditions());
    }
}
