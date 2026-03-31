<?php

/**
 * Example: Global search across all address types
 *
 * Global search simultaneously searches across streets, cities,
 * postal codes, and residences. Use "*" to disable keyword matching.
 */

use ApiCheck\Api\ApiClient;
use ApiCheck\Api\Exceptions\ApiException;
use ApiCheck\Api\Exceptions\UnsupportedCountryException;

require __DIR__ . "/../../vendor/autoload.php";

// Initialize the client
$client = new ApiClient();
$client->setApiKey("8weX4nJq29fHBDrQFlOoYVcPEbWTIxCp");

try {
    // Example 1: Search for "Amsterdam" across all types
    echo "=== Global search for 'Amsterdam' ===\n";
    $results = $client->globalSearch('nl', 'Amsterdam', ['limit' => 10]);

    $totalCount = 0;
    if (isset($results->Results)) {
        $streetsCount = isset($results->Results->Streets) ? count($results->Results->Streets) : 0;
        $citiesCount = isset($results->Results->Cities) ? count($results->Results->Cities) : 0;
        $postalcodesCount = isset($results->Results->Postalcodes) ? count($results->Results->Postalcodes) : 0;
        $residencesCount = isset($results->Results->Residences) ? count($results->Results->Residences) : 0;
        $totalCount = $streetsCount + $citiesCount + $postalcodesCount + $residencesCount;

        echo "Found {$totalCount} result(s):\n\n";

        if (isset($results->Results->Streets) && !empty($results->Results->Streets)) {
            echo "Streets:\n";
            foreach (array_slice($results->Results->Streets, 0, 3) as $item) {
                echo "  - {$item->name}\n";
            }
        }

        if (isset($results->Results->Cities) && !empty($results->Results->Cities)) {
            echo "\nCities:\n";
            foreach (array_slice($results->Results->Cities, 0, 3) as $item) {
                echo "  - {$item->name}\n";
            }
        }

        if (isset($results->Results->Residences) && !empty($results->Results->Residences)) {
            echo "\nResidences (showing first 2):\n";
            foreach (array_slice($results->Results->Residences, 0, 2) as $item) {
                echo "  - {$item->formattedAddress}\n";
            }
        }
    }

    // Example 2: Search within a specific city
    echo "\n\n=== Search for 'Hoofd' within Amsterdam ===\n";
    $results = $client->globalSearch('nl', 'Hoofd', [
        'city_id' => 'Amsterdam',
        'limit' => 5
    ]);

    $totalCount = 0;
    if (isset($results->Results)) {
        foreach (['Streets', 'Cities', 'Postalcodes', 'Residences'] as $type) {
            if (isset($results->Results->$type)) {
                $totalCount += count($results->Results->$type);
            }
        }
        echo "Found {$totalCount} result(s)\n";
    }

    // Example 3: Disable keyword search with "*"
    // This returns ALL results without filtering by keywords
    echo "\n=== Get all streets starting with 'Hoofd' ===\n";
    $results = $client->globalSearch('nl', 'Hoofd*', [
        'limit' => 10
    ]);

    if (isset($results->Results->Streets) && !empty($results->Results->Streets)) {
        echo "Found " . count($results->Results->Streets) . " street(s):\n";
        foreach (array_slice($results->Results->Streets, 0, 5) as $item) {
            echo "  - {$item->name}\n";
        }
    }
} catch (UnsupportedCountryException $e) {
    echo "Unsupported country: {$e->getMessage()}\n";
} catch (ApiException $e) {
    echo "API error: {$e->getMessage()}\n";
}
