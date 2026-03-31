<?php

/**
 * Example: Advanced lookup with options
 *
 * The Lookup API supports several options to customize the response:
 * - fields: return only specific response fields
 * - aliasses: include subaddress (nevenadres) relationships
 * - shortening: include streetShort field in response
 */

use ApiCheck\Api\ApiClient;
use ApiCheck\Api\Exceptions\ApiException;
use ApiCheck\Api\Exceptions\NotFoundException;
use ApiCheck\Api\Exceptions\ValidationException;

require __DIR__ . "/../../vendor/autoload.php";

// Initialize the client
$client = new ApiClient();
$client->setApiKey("8weX4nJq29fHBDrQFlOoYVcPEbWTIxCp");

try {
    // Example 1: Request only specific fields
    echo "=== Example 1: Only street and city ===\n";
    $address = $client->lookup('nl', [
        'postalcode' => '2513AA',
        'number' => '1',
        'fields' => ['street', 'city', 'Country.name']
    ]);

    echo "Street: {$address->street}\n";
    echo "City: {$address->city}\n";
    echo "Country: " . (isset($address->Country) ? $address->Country->name : 'N/A') . "\n";
    echo "Latitude: " . (isset($address->lat) ? $address->lat : 'not requested') . "\n\n";

    // Example 2: Include shortened street name
    echo "=== Example 2: With street shortening ===\n";
    $address = $client->lookup('nl', [
        'postalcode' => '2513AA',
        'number' => '1',
        'shortening' => true
    ]);

    echo "Street: {$address->street}\n";
    echo "Street (short): " . ($address->streetShort ?? 'N/A') . "\n\n";

    // Example 3: Include subaddress relationships (aliasses)
    echo "=== Example 3: With subaddress relationships ===\n";
    $address = $client->lookup('nl', [
        'postalcode' => '2513AA',
        'number' => '1',
        'aliasses' => true
    ]);

    echo "Street: {$address->street} {$address->number}\n";
    if (isset($address->aliasses) && !empty($address->aliasses)) {
        echo "Related subaddresses:\n";
        foreach ($address->aliasses as $alias) {
            echo "  - {$alias->street} {$alias->number}";
            if (!empty($alias->numberAddition)) {
                echo "{$alias->numberAddition}";
            }
            echo "\n";
        }
    } else {
        echo "No subaddresses found.\n";
    }
} catch (NotFoundException $e) {
    echo "Address not found: {$e->getMessage()}\n";
} catch (ValidationException $e) {
    echo "Validation error: {$e->getMessage()}\n";
} catch (ApiException $e) {
    echo "API error: {$e->getMessage()}\n";
}
