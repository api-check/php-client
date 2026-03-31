<?php

/**
 * Example: Basic address lookup in the Netherlands
 *
 * This example demonstrates how to look up a Dutch address using
 * postal code and house number.
 */

use ApiCheck\Api\ApiClient;
use ApiCheck\Api\Exceptions\ApiException;
use ApiCheck\Api\Exceptions\NotFoundException;
use ApiCheck\Api\Exceptions\ValidationException;
use ApiCheck\Api\Exceptions\UnsupportedCountryException;

require __DIR__ . "/../../vendor/autoload.php";

// Initialize the client
$client = new ApiClient();
$client->setApiKey("8weX4nJq29fHBDrQFlOoYVcPEbWTIxCp");

try {
    // Basic lookup: find an address by postal code and house number
    $address = $client->lookup('nl', [
        'postalcode' => '2513AA',
        'number' => '1'
    ]);

    echo "Address found!\n";
    echo "Street: {$address->street} {$address->number}\n";
    echo "Postal code: {$address->postalcode}\n";
    echo "City: {$address->city}\n";
    echo "Country: {$address->Country->name}\n";
    $lat = $address->Location->Coordinates->latitude ?? 'N/A';
    $lng = $address->Location->Coordinates->longitude ?? 'N/A';
    echo "Latitude: {$lat}, Longitude: {$lng}\n";
} catch (NotFoundException $e) {
    echo "Address not found: {$e->getMessage()}\n";
} catch (ValidationException $e) {
    echo "Validation error: {$e->getMessage()}\n";
} catch (UnsupportedCountryException $e) {
    echo "Unsupported country: {$e->getMessage()}\n";
} catch (ApiException $e) {
    echo "API error: {$e->getMessage()}\n";
}
