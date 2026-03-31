<?php

namespace ApiCheck\Api\Tests\Search;

use ApiCheck\Api\Tests\TestCase;
use ApiCheck\Api\Search\SearchClient;
use ApiCheck\Api\Exceptions\UnsupportedCountryException;
use ApiCheck\Api\Tests\Fixtures\TestDataProviders;
use PHPUnit\Framework\Attributes\DataProvider;

class SearchClientTest extends TestCase
{
    // =========================================================================
    // SEARCH METHOD TESTS
    // =========================================================================

    public function testSearchCityWithNameParameter(): void
    {
        $responseData = ['cities' => [['name' => 'Amsterdam']]];
        $response = $this->createSuccessResponse($responseData);
        $client = $this->createApiClientWithMock([$response]);
        $searchClient = new SearchClient($client);

        $result = $searchClient->search('nl', 'city', ['name' => 'Amsterdam']);

        $this->assertIsObject($result);
    }

    public function testSearchStreetWithNameParameter(): void
    {
        $responseData = ['streets' => [['name' => 'Main Street']]];
        $response = $this->createSuccessResponse($responseData);
        $client = $this->createApiClientWithMock([$response]);
        $searchClient = new SearchClient($client);

        $result = $searchClient->search('nl', 'street', ['name' => 'Main']);

        $this->assertIsObject($result);
    }

    public function testSearchWithUnsupportedCountryThrowsException(): void
    {
        $client = $this->createApiClientWithMock([]);
        $searchClient = new SearchClient($client);

        $this->expectException(UnsupportedCountryException::class);
        $searchClient->search('xx', 'city', ['name' => 'Test']);
    }

    #[DataProvider('searchTypesProvider')]
    public function testSearchWithValidTypes(string $type): void
    {
        $responseData = ['results' => []];
        $response = $this->createSuccessResponse($responseData);
        $client = $this->createApiClientWithMock([$response]);
        $searchClient = new SearchClient($client);

        $result = $searchClient->search('nl', $type, ['name' => 'Test']);

        $this->assertIsObject($result);
    }

    public static function searchTypesProvider(): array
    {
        $types = TestDataProviders::searchTypes();
        return array_map(function ($type) {
            return [$type];
        }, $types);
    }

    // =========================================================================
    // GLOBAL SEARCH METHOD TESTS
    // =========================================================================

    public function testGlobalSearchWithQuery(): void
    {
        $responseData = ['results' => []];
        $response = $this->createSuccessResponse($responseData);
        $client = $this->createApiClientWithMock([$response]);
        $searchClient = new SearchClient($client);

        $result = $searchClient->globalSearch('nl', 'Amsterdam');

        $this->assertIsObject($result);
    }

    public function testGlobalSearchWithAsteriskToDisableKeywordSearch(): void
    {
        $responseData = ['results' => []];
        $response = $this->createSuccessResponse($responseData);
        $client = $this->createApiClientWithMock([$response]);
        $searchClient = new SearchClient($client);

        $result = $searchClient->globalSearch('nl', '*');

        $this->assertIsObject($result);
    }

    public function testGlobalSearchWithLimitOption(): void
    {
        $responseData = ['results' => []];
        $response = $this->createSuccessResponse($responseData);
        $client = $this->createApiClientWithMock([$response]);
        $searchClient = new SearchClient($client);

        $result = $searchClient->globalSearch('nl', 'Amsterdam', ['limit' => 10]);

        $this->assertIsObject($result);
    }

    public function testGlobalSearchWithUnsupportedCountryThrowsException(): void
    {
        $client = $this->createApiClientWithMock([]);
        $searchClient = new SearchClient($client);

        $this->expectException(UnsupportedCountryException::class);
        $searchClient->globalSearch('xx', 'Test');
    }

    #[DataProvider('searchCountriesProvider')]
    public function testGlobalSearchWithAllSupportedCountries(string $country): void
    {
        $responseData = ['results' => []];
        $response = $this->createSuccessResponse($responseData);
        $client = $this->createApiClientWithMock([$response]);
        $searchClient = new SearchClient($client);

        $result = $searchClient->globalSearch($country, 'Test');

        $this->assertIsObject($result);
    }

    public static function searchCountriesProvider(): array
    {
        $countries = TestDataProviders::searchCountries();
        return array_map(function ($country) {
            return [$country];
        }, $countries);
    }

    // =========================================================================
    // SPECIALIZED SEARCH METHODS
    // =========================================================================

    public function testSearchLocalityForBelgium(): void
    {
        $responseData = ['localities' => []];
        $response = $this->createSuccessResponse($responseData);
        $client = $this->createApiClientWithMock([$response]);
        $searchClient = new SearchClient($client);

        $result = $searchClient->searchLocality('be', 'Antwerpen');

        $this->assertIsObject($result);
    }

    public function testSearchMunicipalityForBelgium(): void
    {
        $responseData = ['municipalities' => []];
        $response = $this->createSuccessResponse($responseData);
        $client = $this->createApiClientWithMock([$response]);
        $searchClient = new SearchClient($client);

        $result = $searchClient->searchMunicipality('be', 'Antwerpen');

        $this->assertIsObject($result);
    }

    public function testSearchAddressWithIds(): void
    {
        $responseData = ['addresses' => []];
        $response = $this->createSuccessResponse($responseData);
        $client = $this->createApiClientWithMock([$response]);
        $searchClient = new SearchClient($client);

        $result = $searchClient->searchAddress('nl', [
            'street_id' => '123',
            'number' => '1',
        ]);

        $this->assertIsObject($result);
    }

    // =========================================================================
    // GET SUPPORTED SEARCH COUNTRIES
    // =========================================================================

    public function testGetSupportedSearchCountriesReturnsList(): void
    {
        $responseData = [
            'countries' => ['nl', 'be', 'lu', 'de']
        ];
        $response = $this->createSuccessResponse($responseData);
        $client = $this->createApiClientWithMock([$response]);
        $searchClient = new SearchClient($client);

        $result = $searchClient->getSupportedSearchCountries();

        $this->assertIsObject($result);
        $this->assertObjectHasProperty('countries', $result);
    }
}
