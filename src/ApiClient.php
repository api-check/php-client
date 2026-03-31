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
// Sub-clients
use ApiCheck\Api\Lookup\LookupClient;
use ApiCheck\Api\Search\SearchClient;
use ApiCheck\Api\Verify\VerifyClient;

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
    const VERSION = '2.0.0';

    /**
     * @var ClientInterface
     */
    protected $httpClient;

    /**
     * @var string
     */
    protected $apiEndpoint = self::ENDPOINT;

    /**
     * @var string
     */
    protected $apiVersion = self::API_VERSION;

    /**
     * @var string
     */
    protected $apiKey;

    /**
     * @var string|null
     */
    protected $referer;

    /**
     * @var LookupClient
     */
    protected $lookupClient;

    /**
     * @var SearchClient
     */
    protected $searchClient;

    /**
     * @var VerifyClient
     */
    protected $verifyClient;

    /**
     * @param ClientInterface|null $httpClient
     */
    public function __construct(?ClientInterface $httpClient = null)
    {
        $this->httpClient = $httpClient ? $httpClient : new Client();
        $this->lookupClient = new LookupClient($this);
        $this->searchClient = new SearchClient($this);
        $this->verifyClient = new VerifyClient($this);
    }

    /**
     * Set the ApiCheck API key.
     *
     * @param string $apiKey
     * @return ApiClient
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = trim($apiKey);
        return $this;
    }

    /**
     * Set an optional Referer header sent with every request.
     * Required when your API key has Allowed Hosts configured.
     *
     * @param string $referer
     * @return ApiClient
     */
    public function setReferer($referer)
    {
        $this->referer = $referer;
        return $this;
    }

    /**
     * Get the API version.
     *
     * @return string
     */
    public function getApiVersion()
    {
        return $this->apiVersion;
    }

    // -------------------------------------------------------------------------
    // Lookup API
    // -------------------------------------------------------------------------

    /**
     * Look up an address by postal code and house number.
     *
     * Supported countries: nl, lu
     *
     * Optional keys in $query:
     *   fields     (array)  – return only specific response fields
     *   aliasses   (bool)   – include subaddress (nevenadres) relationships
     *   shortening (bool)   – include streetShort field in response
     *
     * @param string $country
     * @param array  $query
     * @return stdClass
     * @throws UnsupportedCountryException
     * @throws ValidationException
     * @throws ApiException
     */
    public function lookup($country, $query = [])
    {
        return $this->lookupClient->lookup($country, $query);
    }

    /**
     * Retrieve available number additions for a postal code + number combination.
     *
     * @param string     $country
     * @param string     $postalcode
     * @param string|int $number
     * @return stdClass
     * @throws UnsupportedCountryException
     * @throws ValidationException
     * @throws ApiException
     */
    public function getNumberAdditions($country, $postalcode, $number)
    {
        return $this->lookupClient->getNumberAdditions($country, $postalcode, $number);
    }

    // -------------------------------------------------------------------------
    // Search API
    // -------------------------------------------------------------------------

    /**
     * Search for cities, streets, postal codes, or addresses within a country.
     *
     * Supported types: city, street, postalcode, address, locality, municipality
     * Supported countries: 18 European countries (see SEARCH_COUNTRIES)
     *
     * @param string $country
     * @param string $type
     * @param array  $query  Pass 'name' (required for most types) plus optional filters
     * @return stdClass
     * @throws UnsupportedCountryException
     * @throws ApiException
     */
    public function search($country, $type, $query = [])
    {
        return $this->searchClient->search($country, $type, $query);
    }

    /**
     * Global search across all scopes (streets, cities, postal codes, residences).
     *
     * @param string $country  ISO 3166-1 alpha-2 country code
     * @param string $query    Search term (use "*" to disable keyword search)
     * @param array  $options  Optional: limit, city_id, locality_id, street_id,
     *                         postalcode_id, municipality_id, fields, translations
     * @return stdClass
     * @throws UnsupportedCountryException
     * @throws ApiException
     */
    public function globalSearch($country, $query, $options = [])
    {
        return $this->searchClient->globalSearch($country, $query, $options);
    }

    /**
     * Search for localities (deelgemeenten) by name. Primarily relevant for Belgium.
     *
     * @param string $country
     * @param string $name
     * @param array  $options  Optional: limit, locality_id, postalcode_id, municipality_id, translations
     * @return stdClass
     * @throws UnsupportedCountryException
     * @throws ApiException
     */
    public function searchLocality($country, $name, $options = [])
    {
        return $this->searchClient->searchLocality($country, $name, $options);
    }

    /**
     * Search for municipalities (gemeenten) by name. Primarily relevant for Belgium.
     *
     * @param string $country
     * @param string $name
     * @param array  $options  Optional: limit, translations
     * @return stdClass
     * @throws UnsupportedCountryException
     * @throws ApiException
     */
    public function searchMunicipality($country, $name, $options = [])
    {
        return $this->searchClient->searchMunicipality($country, $name, $options);
    }

    /**
     * Resolve a full address using IDs returned from city/street/postalcode searches.
     *
     * @param string $country
     * @param array  $params  Optional: number, numberAddition, street_id, city_id,
     *                        locality_id, postalcode_id, municipality_id, translations, limit
     * @return stdClass
     * @throws UnsupportedCountryException
     * @throws ApiException
     */
    public function searchAddress($country, $params = [])
    {
        return $this->searchClient->searchAddress($country, $params);
    }

    /**
     * Retrieve the live list of countries supported by the Search API.
     *
     * @return stdClass
     * @throws ApiException
     */
    public function getSupportedSearchCountries()
    {
        return $this->searchClient->getSupportedSearchCountries();
    }

    // -------------------------------------------------------------------------
    // Verify API
    // -------------------------------------------------------------------------

    /**
     * Verify an email address.
     *
     * Returns an object with: disposable_email (bool), greylisted (bool),
     * status ("valid" | "invalid" | "unknown")
     *
     * @param string $email
     * @return stdClass
     * @throws ApiException
     */
    public function verifyEmail($email)
    {
        return $this->verifyClient->verifyEmail($email);
    }

    /**
     * Verify a phone number.
     *
     * Returns an object with: valid (bool), and on success details including
     * country_code, area_code, international_formatted, number_type, etc.
     *
     * @param string $number  Phone number including country code (e.g. +31612345678)
     * @return stdClass
     * @throws ApiException
     */
    public function verifyPhone($number)
    {
        return $this->verifyClient->verifyPhone($number);
    }

    // -------------------------------------------------------------------------
    // Internal helpers
    // -------------------------------------------------------------------------

    /**
     * Execute an HTTP request to ApiCheck.
     *
     * @param string      $httpMethod
     * @param string      $method
     * @param array       $httpParams
     * @param string|null $httpBody
     * @return stdClass
     * @throws ApiException
     */
    public function doRequest($httpMethod, $method, $httpParams = [], $httpBody = null)
    {
        if (empty($this->apiKey)) {
            throw ApiException::create('No API-key has been set.');
        }

        $url = $this->apiEndpoint . '/' . $method;

        $headers = [
            'Accept'       => 'application/json',
            'Content-Type' => 'application/json',
            'X-API-KEY'    => $this->apiKey,
        ];

        if (!empty($this->referer)) {
            $headers['Referer'] = $this->referer;
        }

        $url .= '?' . urldecode(http_build_query($httpParams));
        $request = new Request($httpMethod, $url, $headers, $httpBody);

        try {
            $response = $this->httpClient->send($request, [RequestOptions::HTTP_ERRORS => false]);
        } catch (GuzzleException $exception) {
            throw new ApiException($exception->getMessage(), 0, $exception);
        }

        return $this->parseResponse($response);
    }

    /**
     * Parse the HTTP response and return the data object.
     *
     * @param ResponseInterface $response
     * @return stdClass
     * @throws ApiException
     */
    private function parseResponse(ResponseInterface $response)
    {
        $statusCode   = $response->getStatusCode();
        $responseBody = $response->getBody();

        if (empty($responseBody)) {
            throw ApiException::create('The ApiCheck response has no body');
        }

        $jsonObject = json_decode($responseBody);

        if ($statusCode >= 400) {
            throw ApiException::createFromResponse($response);
        }

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw ApiException::create("Unable to JSON decode the ApiCheck response: {$responseBody}");
        }

        // Handle API-level errors returned with a 2xx status (e.g. no_exact_match)
        if (isset($jsonObject->error) && $jsonObject->error === true) {
            throw ApiException::createFromResponse($response);
        }

        return $jsonObject->data;
    }
}
