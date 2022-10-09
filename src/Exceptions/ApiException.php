<?php

namespace ApiCheck\Api\Exceptions;

use Throwable;
use Exception;
use Psr\Http\Message\ResponseInterface;

class ApiException extends Exception
{
    const NO_RESULTS_STATUS = 'no_match';
    const API_KEY_INVALID = 'api_key_invalid';
    const API_KEY_EXHAUSTED = 'api_key_invalid';
    const HOST_NOT_ALLOWED = 'host_not_allowed';
    const NO_API_KEY_HEADER = 'no_api_key_header';

    /**
     * Create a new instance of the API exception class
     *
     * @param string $message
     * @return Exception
     */
    public static function create($message)
    {
        return new Exception($message);
    }

    /**
     * Create a new instance from the given response
     *
     * @param ResponseInterface $response
     * @param Throwable|null $previous
     * @return ApiException
     * @throws ApiException
     */
    public static function createFromResponse($response, Throwable $previous = null)
    {
        $responseBody = json_decode($response->getBody());

        if (!empty($responseBody)) {
            if (isset($responseBody->error)) {
                if ($responseBody->error == true) {
                    switch ($responseBody->name) {
                        case self::NO_RESULTS_STATUS:
                            return new NotFoundException('No matches found', $response->getStatusCode(), $previous);
                        case self::API_KEY_INVALID:
                            return new ApiKeyInvalidException('The supplied API key is invalid or disabled.', $response->getStatusCode(), $previous);
                        case self::API_KEY_EXHAUSTED:
                            return new ApiKeyExhaustedException('The supplied API key is exhausted. Check your account balance.', $response->getStatusCode(), $previous);
                        case self::HOST_NOT_ALLOWED:
                            return new HostNotAllowedException('This host is not allowed to use ApiCheck', $response->getStatusCode(), $previous);
                        case self::NO_API_KEY_HEADER:
                            return new ApiKeyHeaderException('No X-API-KEY header found', $response->getStatusCode(), $previous);
                    }
                }
            }
        }

        switch ($response->getStatusCode()) {
            case 400:
                return new BadRequestException('Bad request (400)', $response->getStatusCode(), $previous);

            case 401:
                return new UnauthorizedException('Unauthorized (401)', $response->getStatusCode(), $previous);

            case 403:
                return new AccessDeniedException('Access is denied (403)', $response->getStatusCode(), $previous);

            case 404:
                return new PageNotFoundException('This page does not exists (404)', $response->getStatusCode(), $previous);

            case 422:
                return new UnprocessableEntityException("Unprocessable Entity (433): {$responseBody->message}", $response->getStatusCode(), $previous);

            case 500:
                return new InternalServerErrorException('Internal server error (500)', $response->getStatusCode(), $previous);
        }

        return new static('Other ApiCheck API error: ', $response->getStatusCode(), $previous);
    }

    /**
     * Get the response attached to the exception.
     *
     * @return ResponseInterface|null
     */
    public function getResponse()
    {
        return $this->response;
    }
}
