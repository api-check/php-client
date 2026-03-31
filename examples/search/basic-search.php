<?php

/**
 * Example: Basic search for cities, streets, and postal codes
 *
 * The Search API allows you to find cities, streets, postal codes,
 * and addresses across 18 European countries.
 */

use ApiCheck\Api\ApiClient;
use ApiCheck\Api\Exceptions\ApiException;
use ApiCheck\Api\Exceptions\UnsupportedCountryException;

require __DIR__ . "/../../vendor/autoload.php";

// Initialize the client
$client = new ApiClient();
$client->setApiKey("8weX4nJq29fHBDrQFlOoYVcPEbWTIxCp");

try {
    // Example 1: Search for cities
    echo "=== Searching for cities ===\n";
    $cities = $client->search('be', 'city', ['name' => 'Namur']);

    $count = isset($cities->Results) ? count($cities->Results) : 0;
    echo "Found {$count} cit(y/ies):\n";
    if (isset($cities->Results)) {
        foreach ($cities->Results as $city) {
            $country = isset($city->Country) ? $city->Country->name : 'N/A';
            echo "  - {$city->name} ({$country})\n";
        }
    }

    // Example 2: Search for streets
    echo "\n=== Searching for streets ===\n";
    $streets = $client->search('nl', 'street', [
        'name' => 'Hoofdstraat',
        'limit' => 5
    ]);

    $count = isset($streets->Results) ? count($streets->Results) : 0;
    echo "Found {$count} street(s):\n";
    if (isset($streets->Results)) {
        foreach ($streets->Results as $street) {
            echo "  - {$street->name} in {$street->City->name}\n";
        }
    }

    // Example 3: Search for postal codes
    echo "\n=== Searching for postal codes ===\n";
    $postalCodes = $client->search('nl', 'postalcode', [
        'name' => '2513'
    ]);

    $count = isset($postalCodes->Results) ? count($postalCodes->Results) : 0;
    echo "Found {$count} postal code(s):\n";
    if (isset($postalCodes->Results)) {
        foreach ($postalCodes->Results as $pc) {
            echo "  - {$pc->name} ({$pc->City->name})\n";
        }
    }
} catch (UnsupportedCountryException $e) {
    echo "Unsupported country: {$e->getMessage()}\n";
} catch (ApiException $e) {
    echo "API error: {$e->getMessage()}\n";
}
