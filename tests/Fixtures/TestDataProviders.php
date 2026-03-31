<?php

namespace ApiCheck\Api\Tests\Fixtures;

/**
 * Centralized test data providers for all test classes.
 */
class TestDataProviders
{
    // =========================================================================
    // POSTAL CODE DATA
    // =========================================================================

    /**
     * Valid postal codes by country.
     * Returns: [country, input, expected_output]
     */
    public static function validPostalCodes(): array
    {
        return [
            'Netherlands standard' => ['nl', '2513AA', '2513AA'],
            'Netherlands with space' => ['nl', '2513 AA', '2513 AA'],
            'Netherlands lowercase' => ['nl', '2513aa', '2513AA'],
            'Netherlands with space lowercase' => ['nl', '2513 aa', '2513 AA'],
            'Netherlands Amsterdam' => ['nl', '1012AB', '1012AB'],
            'Netherlands rural' => ['nl', '9999XX', '9999XX'],

            'Luxembourg standard' => ['lu', '1234', '1234'],
            'Luxembourg with space' => ['lu', '1234 ', '1234 '],
            'Luxembourg Luxembourg City' => ['lu', '1111', '1111'],
        ];
    }

    /**
     * Invalid postal codes by country.
     * Returns: [country, input]
     */
    public static function invalidPostalCodes(): array
    {
        return [
            'NL starts with zero' => ['nl', '0123AB'],
            'NL missing letters' => ['nl', '2513'],
            'NL missing numbers' => ['nl', 'AA'],
            'NL too short' => ['nl', '123A'],
            'NL invalid format' => ['nl', 'ABCDE'],

            'BE too short' => ['be', '123'],
            'BE too long' => ['be', '10000'],
            'BE with letters' => ['be', '100A'],

            'LU too short' => ['lu', '123'],
            'LU too long' => ['lu', '12345'],
            'LU with letters' => ['lu', '123A'],

            'Unsupported country code' => ['de', '12345'],
        ];
    }

    // =========================================================================
    // STREET NUMBER DATA
    // =========================================================================

    /**
     * Valid street numbers.
     * Returns: [input, expected_output]
     */
    public static function validStreetNumbers(): array
    {
        return [
            'simple' => ['123', '123'],
            'single digit' => ['1', '1'],
            'large number' => ['99999', '99999'],
            'with letter after space' => ['123 a', '123 a'],
        ];
    }

    /**
     * Invalid street numbers.
     * Returns: [input]
     */
    public static function invalidStreetNumbers(): array
    {
        return [
            'starts with zero' => ['0123'],
            'zero' => ['0'],
            'only letters' => ['ABC'],
            'empty' => [''],
            'only space' => [' '],
            'special chars' => ['123#'],
        ];
    }

    // =========================================================================
    // STREET NUMBER SUFFIX DATA
    // =========================================================================

    /**
     * Valid street number suffixes.
     * Returns: [input, expected_output]
     */
    public static function validStreetNumberSuffixes(): array
    {
        return [
            'letter uppercase' => ['A', 'A'],
            'letter lowercase' => ['a', 'a'],
            'number' => ['1', '1'],
            'alphanumeric' => ['A1', 'A1'],
            'with space' => [' A', 'A'],
            'letter and number' => ['B12', 'B12'],
            'empty string' => ['', ''],
        ];
    }

    /**
     * Invalid street number suffixes (if validation should reject them).
     * Note: Currently the validation allows most suffixes, so these may pass.
     */
    public static function invalidStreetNumberSuffixes(): array
    {
        return [
            'special chars' => ['#'],
            'only spaces' => ['   '],
        ];
    }

    // =========================================================================
    // COUNTRY CODE DATA
    // =========================================================================

    /**
     * Countries supported by Lookup API.
     */
    public static function lookupCountries(): array
    {
        return ['nl', 'lu'];
    }

    /**
     * Countries supported by Search API.
     */
    public static function searchCountries(): array
    {
        return [
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
            'se'
        ];
    }

    /**
     * Invalid country codes.
     */
    public static function invalidCountryCodes(): array
    {
        return [
            'XX' => 'XX',
            'US' => 'US',
            'CN' => 'CN',
            '123' => '123',
            '' => '',
        ];
    }

    // =========================================================================
    // EMAIL DATA
    // =========================================================================

    /**
     * Valid email addresses.
     */
    public static function validEmails(): array
    {
        return [
            'simple' => 'test@example.com',
            'with dots' => 'first.last@example.com',
            'with plus' => 'user+tag@example.com',
            'with numbers' => 'user123@example.com',
            'subdomain' => 'user@mail.example.com',
            'Dutch domain' => 'user@example.nl',
            'German domain' => 'user@example.de',
        ];
    }

    /**
     * Invalid email addresses.
     */
    public static function invalidEmails(): array
    {
        return [
            'missing at' => 'userexample.com',
            'missing domain' => 'user@',
            'missing user' => '@example.com',
            'double at' => 'user@@example.com',
            'space in email' => 'user @example.com',
            'empty' => '',
        ];
    }

    /**
     * Disposable email domains.
     */
    public static function disposableEmails(): array
    {
        return [
            'tempmail' => 'user@temp-mail.org',
            'guerrillamail' => 'user@guerrillamail.com',
            '10minutemail' => 'user@10minutemail.com',
        ];
    }

    // =========================================================================
    // PHONE NUMBER DATA
    // =========================================================================

    /**
     * Valid phone numbers with country codes.
     */
    public static function validPhoneNumbers(): array
    {
        return [
            'Dutch mobile' => '+31612345678',
            'Dutch landline' => '+3112345678',
            'Belgian mobile' => '+32471123456',
            'Luxembourg number' => '+352123456',
            'German number' => '+49123456789',
            'UK mobile' => '+447123456789',
            'with spaces' => '+31 6 12345678',
            'with dashes' => '+31-6-12345678',
        ];
    }

    /**
     * Invalid phone numbers.
     */
    public static function invalidPhoneNumbers(): array
    {
        return [
            'missing country code' => '0612345678',
            'too short' => '+31123',
            'letters' => '+3161234abcd',
            'empty' => '',
            'only plus' => '+',
        ];
    }

    // =========================================================================
    // HTTP ERROR RESPONSE DATA
    // =========================================================================

    /**
     * HTTP status codes and corresponding exception types.
     * Returns: [status_code, error_name, expected_exception_class]
     */
    public static function httpErrorResponses(): array
    {
        return [
            [400, 'bad_request', 'ApiCheck\Api\Exceptions\BadRequestException'],
            [401, 'unauthorized', 'ApiCheck\Api\Exceptions\UnauthorizedException'],
            [403, 'forbidden', 'ApiCheck\Api\Exceptions\AccessDeniedException'],
            [404, 'not_found', 'ApiCheck\Api\Exceptions\PageNotFoundException'],
            [422, 'validation', 'ApiCheck\Api\Exceptions\UnprocessableEntityException'],
            [500, 'internal_server_error', 'ApiCheck\Api\Exceptions\InternalServerErrorException'],
        ];
    }

    /**
     * API-specific error types.
     * Returns: [error_name, expected_exception_class]
     */
    public static function apiErrorTypes(): array
    {
        return [
            ['api_key_invalid', 'ApiCheck\Api\Exceptions\ApiKeyInvalidException'],
            ['api_key_exhausted', 'ApiCheck\Api\Exceptions\ApiKeyExhaustedException'],
            ['host_not_allowed', 'ApiCheck\Api\Exceptions\HostNotAllowedException'],
            ['no_api_key_header', 'ApiCheck\Api\Exceptions\ApiKeyHeaderException'],
            ['no_match', 'ApiCheck\Api\Exceptions\NotFoundException'],
        ];
    }

    // =========================================================================
    // SEARCH TYPES
    // =========================================================================

    /**
     * Valid search types for Search API.
     */
    public static function searchTypes(): array
    {
        return [
            'city' => 'city',
            'street' => 'street',
            'postalcode' => 'postalcode',
            'address' => 'address',
            'locality' => 'locality',
            'municipality' => 'municipality',
        ];
    }

    // =========================================================================
    // LOOKUP OPTIONS
    // =========================================================================

    /**
     * Valid options for lookup API.
     */
    public static function lookupOptions(): array
    {
        return [
            'fields option' => ['fields' => ['street', 'city']],
            'aliasses option' => ['aliasses' => true],
            'shortening option' => ['shortening' => true],
            'all options' => [
                'fields' => ['street', 'city', 'number'],
                'aliasses' => true,
                'shortening' => true,
            ],
        ];
    }
}
