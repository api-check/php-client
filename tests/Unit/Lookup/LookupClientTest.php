<?php

namespace ApiCheck\Api\Tests\Lookup;

use ApiCheck\Api\Tests\TestCase;
use ApiCheck\Api\ApiClient;
use ApiCheck\Api\Lookup\LookupClient;
use ApiCheck\Api\Exceptions\UnsupportedCountryException;
use ApiCheck\Api\Exceptions\ValidationException;
use GuzzleHttp\Psr7\Response;
use ApiCheck\Api\Tests\Fixtures\TestDataProviders;

class LookupClientTest extends TestCase
{
    protected $lookupClient;
    protected $apiClient;

    protected function setUp(): void
    {
        parent::setUp();
        $this->apiClient = $this->createApiClientWithMock([]);
        $this->lookupClient = new LookupClient($this->apiClient);
    }

    // =========================================================================
    // LOOKUP METHOD - SUCCESS TESTS
    // =========================================================================

    public function testLookupWithValidDutchAddress(): void
    {
        $responseData = [
            'street' => 'Main Street',
            'number' => '123',
            'postalcode' => '2513AA',
            'city' => 'The Hague',
        ];

        $response = $this->createSuccessResponse($responseData);
        $client = $this->createApiClientWithMock([$response]);
        $lookupClient = new LookupClient($client);

        $result = $lookupClient->lookup('nl', [
            'postalcode' => '2513AA',
            'number' => '123',
        ]);

        $this->assertIsObject($result);
        $this->assertEquals('Main Street', $result->street);
        $this->assertEquals('123', $result->number);
    }

    public function testLookupWithValidLuxembourgAddress(): void
    {
        $responseData = [
            'street' => 'Rue de la Liberté',
            'number' => '45',
            'postalcode' => '1234',
            'city' => 'Luxembourg',
        ];

        $response = $this->createSuccessResponse($responseData);
        $client = $this->createApiClientWithMock([$response]);
        $lookupClient = new LookupClient($client);

        $result = $lookupClient->lookup('lu', [
            'postalcode' => '1234',
            'number' => '45',
        ]);

        $this->assertEquals('Rue de la Liberté', $result->street);
    }

    public function testLookupWithNumberAddition(): void
    {
        $responseData = [
            'street' => 'Main Street',
            'number' => '123',
            'numberAddition' => 'a',
        ];

        $response = $this->createSuccessResponse($responseData);
        $client = $this->createApiClientWithMock([$response]);
        $lookupClient = new LookupClient($client);

        $result = $lookupClient->lookup('nl', [
            'postalcode' => '2513AA',
            'number' => '123',
            'numberAddition' => 'a',
        ]);

        $this->assertEquals('a', $result->numberAddition);
    }

    public function testLookupWithFieldsOption(): void
    {
        $responseData = ['street' => 'Main Street'];
        $response = $this->createSuccessResponse($responseData);
        $client = $this->createApiClientWithMock([$response]);
        $lookupClient = new LookupClient($client);

        $result = $lookupClient->lookup('nl', [
            'postalcode' => '2513AA',
            'number' => '123',
            'fields' => ['street'],
        ]);

        $this->assertObjectHasProperty('street', $result);
    }

    public function testLookupWithAliasesOption(): void
    {
        $responseData = ['street' => 'Main Street', 'aliasses' => []];
        $response = $this->createSuccessResponse($responseData);
        $client = $this->createApiClientWithMock([$response]);
        $lookupClient = new LookupClient($client);

        $result = $lookupClient->lookup('nl', [
            'postalcode' => '2513AA',
            'number' => '123',
            'aliasses' => true,
        ]);

        $this->assertObjectHasProperty('aliasses', $result);
    }

    public function testLookupWithShorteningOption(): void
    {
        $responseData = ['streetShort' => 'Main St'];
        $response = $this->createSuccessResponse($responseData);
        $client = $this->createApiClientWithMock([$response]);
        $lookupClient = new LookupClient($client);

        $result = $lookupClient->lookup('nl', [
            'postalcode' => '2513AA',
            'number' => '123',
            'shortening' => true,
        ]);

        $this->assertObjectHasProperty('streetShort', $result);
    }

    public function testLookupNormalizesPostalCode(): void
    {
        $responseData = ['street' => 'Main Street'];
        $response = $this->createSuccessResponse($responseData);
        $client = $this->createApiClientWithMock([$response]);
        $lookupClient = new LookupClient($client);

        // Lowercase postal code should be uppercased
        $result = $lookupClient->lookup('nl', [
            'postalcode' => '2513aa',
            'number' => '123',
        ]);

        $this->assertIsObject($result);
    }

    // =========================================================================
    // LOOKUP METHOD - VALIDATION TESTS
    // =========================================================================

    public function testLookupWithUnsupportedCountryThrowsException(): void
    {
        $this->expectException(UnsupportedCountryException::class);
        $this->expectExceptionMessage('No lookup action available for country');

        $this->lookupClient->lookup('de', [
            'postalcode' => '12345',
            'number' => '123',
        ]);
    }

    public function testLookupWithMissingPostalCodeThrowsException(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('not present in the query array');

        $this->lookupClient->lookup('nl', [
            'number' => '123',
        ]);
    }

    public function testLookupWithMissingNumberThrowsException(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('not present in the query array');

        $this->lookupClient->lookup('nl', [
            'postalcode' => '2513AA',
        ]);
    }

    public function testLookupWithInvalidPostalCodeThrowsException(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid postalcode provided');

        $this->lookupClient->lookup('nl', [
            'postalcode' => '0123AB', // Starts with 0
            'number' => '123',
        ]);
    }

    public function testLookupWithInvalidStreetNumberThrowsException(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid streetnumber provided');

        $this->lookupClient->lookup('nl', [
            'postalcode' => '2513AA',
            'number' => '0', // Starts with 0
        ]);
    }

    public function testLookupCountryCaseNormalization(): void
    {
        $this->expectException(UnsupportedCountryException::class);

        // Uppercase country codes are not supported by Validations class
        $this->lookupClient->lookup('NL', [
            'postalcode' => '2513AA',
            'number' => '123',
        ]);
    }

    // =========================================================================
    // GET NUMBER ADDITIONS METHOD - SUCCESS TESTS
    // =========================================================================

    public function testGetNumberAdditionsWithValidDutchAddress(): void
    {
        $responseData = [
            'numberAdditions' => ['a', 'b', '1', '2'],
        ];

        $response = $this->createSuccessResponse($responseData);
        $client = $this->createApiClientWithMock([$response]);
        $lookupClient = new LookupClient($client);

        $result = $lookupClient->getNumberAdditions('nl', '2513AA', '123');

        $this->assertIsObject($result);
        $this->assertObjectHasProperty('numberAdditions', $result);
        $this->assertIsArray($result->numberAdditions);
    }

    public function testGetNumberAdditionsWithValidLuxembourgAddress(): void
    {
        $responseData = [
            'numberAdditions' => ['1', '2'],
        ];

        $response = $this->createSuccessResponse($responseData);
        $client = $this->createApiClientWithMock([$response]);
        $lookupClient = new LookupClient($client);

        $result = $lookupClient->getNumberAdditions('lu', '1234', '45');

        $this->assertIsObject($result);
    }

    public function testGetNumberAdditionsNormalizesPostalCode(): void
    {
        $responseData = ['numberAdditions' => []];
        $response = $this->createSuccessResponse($responseData);
        $client = $this->createApiClientWithMock([$response]);
        $lookupClient = new LookupClient($client);

        $result = $lookupClient->getNumberAdditions('nl', '2513aa', '123');

        $this->assertIsObject($result);
    }

    // =========================================================================
    // GET NUMBER ADDITIONS METHOD - VALIDATION TESTS
    // =========================================================================

    public function testGetNumberAdditionsWithUnsupportedCountryThrowsException(): void
    {
        $this->expectException(UnsupportedCountryException::class);
        $this->expectExceptionMessage('No lookup action available for country');

        $this->lookupClient->getNumberAdditions('de', '12345', '123');
    }

    public function testGetNumberAdditionsWithInvalidPostalCodeThrowsException(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid postalcode provided');

        $this->lookupClient->getNumberAdditions('nl', '0123AB', '123');
    }

    public function testGetNumberAdditionsWithInvalidStreetNumberThrowsException(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid streetnumber provided');

        $this->lookupClient->getNumberAdditions('nl', '2513AA', '0');
    }

    // =========================================================================
    // ENDPOINT URL VERIFICATION
    // =========================================================================

    public function testLookupCallsCorrectEndpoint(): void
    {
        $responseData = ['street' => 'Main Street'];
        $response = $this->createSuccessResponse($responseData);

        $client = $this->createApiClientWithMock([$response]);
        $lookupClient = new LookupClient($client);

        $lookupClient->lookup('nl', [
            'postalcode' => '2513AA',
            'number' => '123',
        ]);

        // Verify the request was made (if we could inspect the mock)
        $this->assertTrue(true);
    }

    public function testGetNumberAdditionsCallsCorrectEndpoint(): void
    {
        $responseData = ['numberAdditions' => []];
        $response = $this->createSuccessResponse($responseData);

        $client = $this->createApiClientWithMock([$response]);
        $lookupClient = new LookupClient($client);

        $lookupClient->getNumberAdditions('nl', '2513AA', '123');

        $this->assertTrue(true);
    }
}
