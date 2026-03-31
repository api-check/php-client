<?php

namespace ApiCheck\Api\Search;

use stdClass;
use ApiCheck\Api\ApiClient;
use ApiCheck\Api\Exceptions\UnsupportedCountryException;

class SearchClient
{
    /**
     * @var ApiClient
     */
    protected $client;

    /**
     * Countries supported by the Search API.
     */
    const SEARCH_COUNTRIES = [
        'nl',
        'be',
        'lu',
        'fr',
        'de',
        'cz',
        'fi',
        'it',
        'no',
        'pl',
        'pt',
        'ro',
        'es',
        'ch',
        'at',
        'dk',
        'gb',
        'se',
    ];

    public function __construct(ApiClient $client)
    {
        $this->client = $client;
    }

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
     * @throws \ApiCheck\Api\Exceptions\ApiException
     */
    public function search($country, $type, $query = [])
    {
        $allowed_types = ['city', 'street', 'postalcode', 'address', 'locality', 'municipality'];

        if (!in_array($type, $allowed_types)) {
            throw new UnsupportedCountryException("This type does not exist: ({$type})");
        }

        if (!in_array(strtolower($country), self::SEARCH_COUNTRIES)) {
            throw new UnsupportedCountryException("Given country is not supported: ({$country})");
        }

        return $this->client->doRequest('GET', "search/{$this->client->getApiVersion()}/{$type}/{$country}", $query);
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
     * @throws \ApiCheck\Api\Exceptions\ApiException
     */
    public function globalSearch($country, $query, $options = [])
    {
        if (!in_array(strtolower($country), self::SEARCH_COUNTRIES)) {
            throw new UnsupportedCountryException("Given country is not supported: ({$country})");
        }

        $params = array_merge(['query' => $query], $options);
        return $this->client->doRequest('GET', "search/{$this->client->getApiVersion()}/global/{$country}", $params);
    }

    /**
     * Search for localities (deelgemeenten) by name. Primarily relevant for Belgium.
     *
     * @param string $country
     * @param string $name
     * @param array  $options  Optional: limit, locality_id, postalcode_id, municipality_id, translations
     * @return stdClass
     * @throws UnsupportedCountryException
     * @throws \ApiCheck\Api\Exceptions\ApiException
     */
    public function searchLocality($country, $name, $options = [])
    {
        if (!in_array(strtolower($country), self::SEARCH_COUNTRIES)) {
            throw new UnsupportedCountryException("Given country is not supported: ({$country})");
        }

        $params = array_merge(['name' => $name], $options);
        return $this->client->doRequest('GET', "search/{$this->client->getApiVersion()}/locality/{$country}", $params);
    }

    /**
     * Search for municipalities (gemeenten) by name. Primarily relevant for Belgium.
     *
     * @param string $country
     * @param string $name
     * @param array  $options  Optional: limit, translations
     * @return stdClass
     * @throws UnsupportedCountryException
     * @throws \ApiCheck\Api\Exceptions\ApiException
     */
    public function searchMunicipality($country, $name, $options = [])
    {
        if (!in_array(strtolower($country), self::SEARCH_COUNTRIES)) {
            throw new UnsupportedCountryException("Given country is not supported: ({$country})");
        }

        $params = array_merge(['name' => $name], $options);
        return $this->client->doRequest('GET', "search/{$this->client->getApiVersion()}/municipality/{$country}", $params);
    }

    /**
     * Resolve a full address using IDs returned from city/street/postalcode searches.
     *
     * @param string $country
     * @param array  $params  Optional: number, numberAddition, street_id, city_id,
     *                        locality_id, postalcode_id, municipality_id, translations, limit
     * @return stdClass
     * @throws UnsupportedCountryException
     * @throws \ApiCheck\Api\Exceptions\ApiException
     */
    public function searchAddress($country, $params = [])
    {
        if (!in_array(strtolower($country), self::SEARCH_COUNTRIES)) {
            throw new UnsupportedCountryException("Given country is not supported: ({$country})");
        }

        return $this->client->doRequest('GET', "search/{$this->client->getApiVersion()}/address/{$country}", $params);
    }

    /**
     * Retrieve the live list of countries supported by the Search API.
     *
     * @return stdClass
     * @throws \ApiCheck\Api\Exceptions\ApiException
     */
    public function getSupportedSearchCountries()
    {
        return $this->client->doRequest('GET', "search/{$this->client->getApiVersion()}/country");
    }
}
