<?php

namespace ApiCheck\Api\Lookup;

use stdClass;
use ApiCheck\Api\ApiClient;
use ApiCheck\Api\Exceptions\UnsupportedCountryException;
use ApiCheck\Api\Exceptions\ValidationException;
use ApiCheck\Api\Validations;

class LookupClient
{
    /**
     * @var ApiClient
     */
    protected $client;

    /**
     * Countries supported by the Lookup API.
     */
    const LOOKUP_COUNTRIES = ['nl', 'lu'];

    public function __construct(ApiClient $client)
    {
        $this->client = $client;
    }

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
     * @throws \ApiCheck\Api\Exceptions\ApiException
     */
    public function lookup($country, $query = [])
    {
        if (!in_array(strtolower($country), self::LOOKUP_COUNTRIES)) {
            throw new UnsupportedCountryException("No lookup action available for country: ({$country})");
        }

        $postalCode     = $this->validateQueryField($country, $query, 'postalcode', true);
        $number         = $this->validateQueryField($country, $query, 'number', true);
        $numberAddition = $this->validateQueryField($country, $query, 'numberAddition');

        $params = array_filter([
            'postalcode'     => $postalCode,
            'number'         => $number,
            'numberAddition' => $numberAddition,
        ], function ($v) {
            return $v !== null;
        });

        foreach (['fields', 'aliasses', 'shortening'] as $opt) {
            if (isset($query[$opt])) {
                $params[$opt] = $query[$opt];
            }
        }

        return $this->client->doRequest('GET', "lookup/{$this->client->getApiVersion()}/postalcode/{$country}", $params);
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
     * @throws \ApiCheck\Api\Exceptions\ApiException
     */
    public function getNumberAdditions($country, $postalcode, $number)
    {
        if (!in_array(strtolower($country), self::LOOKUP_COUNTRIES)) {
            throw new UnsupportedCountryException("No lookup action available for country: ({$country})");
        }

        $validator  = new Validations($country);
        $postalcode = $validator->validatePostalCode($postalcode);
        $number     = $validator->validateStreetNumber($number);

        return $this->client->doRequest('GET', "lookup/{$this->client->getApiVersion()}/address/{$country}", [
            'postalcode' => $postalcode,
            'number'     => $number,
            'fields'     => json_encode(['numberAdditions']),
        ]);
    }

    /**
     * Validate a single field from the query array.
     *
     * @param string  $country
     * @param array   $query
     * @param string  $fieldName
     * @param boolean $required
     * @param boolean $validation
     * @return mixed|null
     * @throws ValidationException
     */
    private function validateQueryField($country, $query, $fieldName, $required = false, $validation = true)
    {
        $validator = new Validations($country);
        if (isset($query[$fieldName])) {
            if ($validation == true) {
                switch ($fieldName) {
                    case 'number':
                        return $validator->validateStreetNumber($query[$fieldName]);
                    case 'numberAddition':
                        return $validator->validateStreetNumberSuffix($query[$fieldName]);
                    case 'postalcode':
                        return $validator->validatePostalCode($query[$fieldName]);
                    default:
                        return $query[$fieldName];
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
}
