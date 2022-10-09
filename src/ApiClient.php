<?php

namespace ApiCheck\Api;

// General
use stdClass;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\RequestOptions;

// Exceptions
use ApiCheck\Api\Exceptions\ApiException;
use ApiCheck\Api\Exceptions\UnsupportedCountryException;
use ApiCheck\Api\Exceptions\ValidationException;

class ApiClient
{
    /**
     * Endpoint of the remote API.
     */
    const ENDPOINT = 'https://api.apicheck.nl';

    /**
     * Version of the ApiCheck API to use
     */
    const API_VERSION = 'v1';

    /**
     * Version of the ApiCheck Client
     */
    const VERSION = '1.0.0';

    /**
     * What Client do we need to use for requests?
     *
     * @var ClientInterface
     */
    protected $httpClient;

    /**
     * The default ApiCheck endpoint
     *
     * @var string
     */
    protected $apiEndpoint = self::ENDPOINT;

    /**
     * The ApiCheck API version to use
     *
     * @var string
     */
    protected $apiVersion = self::API_VERSION;

    /**
     *  Set the default ApiCheck ApiKey
     *
     * @var string
     */
    protected $apiKey;


    /**
     * ApiClient constructor function
     *
     * @param ClientInterface|null $httpClient
     * @throws ApiCheckException
     */
    public function __construct(ClientInterface $httpClient = null)
    {
        $this->httpClient = $httpClient ?  $httpClient : new Client();
    }

    /**
     * Set the APiCheck API key
     *
     * @param string $apiKey
     * @return ApiClient
     * @throws ApiCheckException
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = trim($apiKey);
        return $this;
    }

    /**
     * Execute calls with the lookup endpoint for each country
     * 
     * @param string $country
     * @param array $query
     * @return stdClass
     * @throws ApiCheckException
     */
    public function lookup($country, $query = [])
    {
        switch ($country) {
            case 'nl':
            case 'lu':
                // Do validations
                $postalCode = $this->validateQueryField($country, $query, 'postalcode', true);
                $number = $this->validateQueryField($country, $query, 'number', true);
                $numberAddition = $this->validateQueryField($country, $query, 'numberAddition');
                // Execute API call
                $response = $this->doRequest('GET', "lookup/{$this->apiVersion}/postalcode/{$country}", ['postalcode' => $postalCode, 'number' => $number, 'numberAddition' => $numberAddition]);
                break;
            default:
                throw new UnsupportedCountryException("No lookup action available for country: ({$country})");
                break;
        }
        return $response;
    }

    /**
     * Execute calls with the search endpoint for each country
     * 
     * @param string $country
     * @param string $type
     * @param array $query
     * @return stdClass
     * @throws ApiCheckException
     */
    public function search($country, $type, $query = [])
    {
        $allowed_types = ['city', 'street', 'postalcode', 'address'];

        if (in_array($type, $allowed_types)) {
            switch ($country) {
                case 'nl':
                case 'lu':
                case 'fr':
                case 'be':
                    // Execute API call
                    $response = $this->doRequest('GET', "search/{$this->apiVersion}/{$type}/{$country}", $query);
                    break;
                default:
                    throw new UnsupportedCountryException("Given country is not supported: ({$country})");
                    break;
            }
            return $response;
        } else {
            throw new UnsupportedCountryException("This type does not exist: ({$type})");
        }
    }

    /**
     * Validate the inputs given in the query array
     * 
     * @param string $country
     * @param array $query
     * @param string $fieldName
     * @param boolean $required
     * @param boolean $validation
     * @return stdClass|null
     * @throws ValidationException
     */
    private function validateQueryField($country, $query, $fieldName, $required = false, $validation = true)
    {
        $validator = new Validations($country);
        if (isset($query[$fieldName])) {
            if ($validation == true) {
                switch ($fieldName) {
                        // Some fields also have custom validators. We check them here.
                    case 'number':
                        return $validator->validateStreetNumber($query[$fieldName]);
                        break;
                    case 'numberAddition':
                        return $validator->validateStreetNumberSuffix($query[$fieldName]);
                        break;
                    case 'postalcode':
                        return $validator->validatePostalCode($query[$fieldName]);
                        break;
                    default:
                        return $query[$fieldName];
                        break;
                }
            } else {
                return $query[$fieldName];
            }
        } else {
            if ($required == true) {
                throw new ValidationException("The field '{$fieldName}' is not present in the query array for country: ({$country})");
            } else {
                return null;
            }
        }
    }

    /**
     * Execute the HTTP request to APiCheck
     *
     * @param string $httpMethod
     * @param string $method
     * @param array $httpParams
     * @param string|null $httpBody
     * @return stdClass
     * @throws ApiCheckException
     */
    private function doRequest($httpMethod, $method, $httpParams = [], $httpBody = null)
    {
        if (empty($this->apiKey)) {
            throw ApiCheckException::create('No API-key has been set.');
        }

        $url = $this->apiEndpoint . '/' . $method;

        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'X-API-KEY' => $this->apiKey
        ];

        $url .= '?' . urldecode(http_build_query($httpParams));
        $request = new Request($httpMethod, $url, $headers, $httpBody);

        try {
            $response = $this->httpClient->send($request, [RequestOptions::HTTP_ERRORS => false]);
        } catch (GuzzleException $exception) {
            throw ApiCheckException::createFromGuzzleException($exception);
        }

        return $this->parseResponse($response);
    }

    /**
     * Parse, and return the request as object
     *
     * @param ResponseInterface $response
     * @return stdClass|null
     * @throws ApiCheckException
     */
    private function parseResponse(ResponseInterface $response)
    {
        $statusCode = $response->getStatusCode();
        $responseBody = $response->getBody();

        if (empty($responseBody)) {
            throw ApiCheckException::create('The ApiCheck response has no body');
        }

        $jsonObject = json_decode($responseBody);

        if ($statusCode >= 400) {
            throw ApiException::createFromResponse($response);
        }

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw ApiCheckException::create("Unable to JSON decode the ApiCheck response: {$responseBody}");
        }

        // Result was ok
        return $jsonObject->data;
    }
}
