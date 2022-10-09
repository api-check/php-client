<?php

namespace ApiCheck\Api;

use ApiCheck\Api\Exceptions\UnsupportedCountryException;
use ApiCheck\Api\Exceptions\ValidationException;

class Validations
{
    /**
     * @var array
     */
    private static $regexPostalCode = [
        'nl' => '/^[1-9][0-9]{3}\s*[a-z]{2}$/i',
        'be' => '/^[1-9][0-9]{3}\s*$/m',
        'lu' => '/^[1-9][0-9]{3}\s*$/m'
    ];

    /**
     * @var array
     */
    private static $regexStreetNumber = [
        'nl' => '/^([1-9][0-9]{0,4})\s?(?:[a-z])?\s?(?:[a-z0-9]{1,4})?$/',
        'be' => '/^([1-9][0-9]{0,4})\s?(?:[a-z])?\s?(?:[a-z0-9]{1,4})?$/',
        'lu' => '/^([1-9][0-9]{0,4})\s?(?:[a-z])?\s?(?:[a-z0-9]{1,4})?$/'
    ];

    /**
     * @var array
     */
    private static $regexStreetNumberSuffix = [
        'nl' => '/^(?:[a-z])?\s?(?:[a-z0-9]{1,4})?$/i',
        'be' => '/^(?:[a-z])?\s?(?:[a-z0-9]{1,4})?$/i',
        'lu' => '/^(?:[a-z])?\s?(?:[a-z0-9]{1,4})?$/i'
    ];

    /**
     * @var array
     */
    private static $supportedCountries = ['nl', 'be', 'lu'];

    /**
     * @var string
     */
    public $countryCode;

    /**
     * Validations constructor
     *
     * @param $countryCode
     * @throws ValidationException
     */
    public function __construct($countryCode)
    {
        $this->validatecountryCode($countryCode);
        $this->countryCode = strtolower($countryCode);
    }

    /**
     * Validate the postalcode based on country
     *
     * @param string $postalCode
     * @return string
     * @throws ValidationException
     */
    public function validatePostalCode($postalCode)
    {
        if (!preg_match(static::$regexPostalCode[$this->countryCode], $postalCode)) {
            throw new ValidationException("Invalid postalcode provided ({$postalCode}) for country: ({$this->countryCode})");
        } else {
            return strtoupper($postalCode);
        }
    }

    /**
     * Validate streetNumber based on country
     * 
     * @param string|int $streetNumber
     * @return array
     * @throws ValidationException
     */
    public function validateStreetNumber($streetNumber)
    {
        if (!preg_match(static::$regexStreetNumber[$this->countryCode], $streetNumber)) {
            throw new ValidationException("Invalid streetnumber provided ({$streetNumber}) for country: ({$this->countryCode})");
        } else {
            return $streetNumber;
        }
    }

    /**
     * Validate streetNumberSuffix based on country
     * 
     * @param string $streetNumberSuffix
     * @return string
     * @throws ValidationException
     */
    public function validateStreetNumberSuffix($streetNumberSuffix)
    {
        if (!preg_match(static::$regexStreetNumberSuffix[$this->countryCode], $streetNumberSuffix)) {
            throw new ValidationException("Invalid streetnumber suffix provided ({$streetNumberSuffix}) for country: ({$this->countryCode})");
        } else {
            return trim($streetNumberSuffix);
        }
    }

    /**
     * Validate country-code.
     *
     * @param $countryCode
     * @return void
     * @throws UnsupportedCountryException
     */
    protected function validatecountryCode($countryCode)
    {
        if (in_array($countryCode, static::$supportedCountries)) {
            $this->countryCode = strtoupper($countryCode);
        } else {
            throw new UnsupportedCountryException("Unsupported country-code provided: ({$countryCode})");
        }
    }
}