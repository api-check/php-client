<?php

/**
 * Example: Resolve a full address using IDs from searches
 *
 * After searching for cities, streets, or postal codes, you get IDs
 * that can be used to resolve a complete address with house numbers.
 *
 * This is useful when building autocomplete forms or when users
 * select options from dropdown menus.
 */

use ApiCheck\Api\ApiClient;
use ApiCheck\Api\Exceptions\ApiException;
use ApiCheck\Api\Exceptions\NoExactMatchException;
use ApiCheck\Api\Exceptions\UnsupportedCountryException;

require __DIR__ . "/../../vendor/autoload.php";

// Initialize the client
$client = new ApiClient();
$client->setApiKey("8weX4nJq29fHBDrQFlOoYVcPEbWTIxCp");

try {
    // Step 1: Search for a street to get its ID
    echo "=== Step 1: Find street ID ===\n";
    $streets = $client->search('nl', 'street', [
        'name' => 'Hoofdstraat',
        'limit' => 1
    ]);

    if (!isset($streets->Results) || empty($streets->Results)) {
        echo "No street found\n";
        exit;
    }

    $streetId = $streets->Results[0]->street_id;
    echo "Found street: {$streets->Results[0]->name} (ID: {$streetId})\n\n";

    // Step 2: Use the street ID to find addresses with house numbers
    echo "=== Step 2: Resolve addresses for this street ===\n";
    $addresses = $client->searchAddress('nl', [
        'street_id' => $streetId,
        'number' => '1',
        'limit' => 5
    ]);

    $count = isset($addresses->Results) ? count($addresses->Results) : 0;
    echo "Found {$count} address(es):\n";
    if (isset($addresses->Results)) {
        foreach ($addresses->Results as $address) {
            $addition = $address->numberAddition ? " {$address->numberAddition}" : '';
            echo "  - {$address->street} {$address->number}{$addition}, ";
            echo "{$address->postalcode} {$address->city}\n";
        }
    }

    // Alternative: Combine multiple IDs for more precise searches
    echo "\n=== Step 3: Search with city and postal code IDs ===\n";

    // Get city ID
    $cities = $client->search('nl', 'city', ['name' => "'s-Gravenhage", 'limit' => 1]);
    if (isset($cities->Results) && !empty($cities->Results)) {
        $cityId = $cities->Results[0]->city_id;

        // Get postal code ID
        $postalCodes = $client->search('nl', 'postalcode', ['name' => '2513AA', 'limit' => 1]);
        if (isset($postalCodes->Results) && !empty($postalCodes->Results)) {
            $postalCodeId = $postalCodes->Results[0]->postalcode_id;

            // Search with all IDs
            $addresses = $client->searchAddress('nl', [
                'city_id' => $cityId,
                'postalcode_id' => $postalCodeId,
                'number' => '1',
                'limit' => 10
            ]);

            $count = isset($addresses->Results) ? count($addresses->Results) : 0;
            echo "Found {$count} address(es) in 's-Gravenhage 2513AA:\n";
            if (isset($addresses->Results)) {
                foreach ($addresses->Results as $address) {
                    echo "  - {$address->street} {$address->number}";
                    if ($address->numberAddition) {
                        echo " {$address->numberAddition}";
                    }
                    echo "\n";
                }
            }
        }
    }
} catch (NoExactMatchException $e) {
    echo "No exact match found: {$e->getMessage()}\n";
} catch (UnsupportedCountryException $e) {
    echo "Unsupported country: {$e->getMessage()}\n";
} catch (ApiException $e) {
    echo "API error: {$e->getMessage()}\n";
}
