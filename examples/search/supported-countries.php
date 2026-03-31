<?php

/**
 * Example: Get list of supported countries for Search API
 *
 * The Search API supports 18 European countries. Use this endpoint
 * to retrieve the live list of supported countries.
 */

use ApiCheck\Api\ApiClient;
use ApiCheck\Api\Exceptions\ApiException;

require __DIR__ . "/../../vendor/autoload.php";

// Initialize the client
$client = new ApiClient();
$client->setApiKey("8weX4nJq29fHBDrQFlOoYVcPEbWTIxCp");

try {
    $result = $client->getSupportedSearchCountries();

    echo "=== Countries supported by Search API ===\n\n";

    // Check if result is an array (direct response) or object with Results
    $countries = is_array($result) ? $result : ($result->Results ?? []);

    foreach ($countries as $country) {
        echo "  - {$country->name} ({$country->code})\n";
    }

    $count = count($countries);
    echo "\nTotal: {$count} countries\n";
} catch (ApiException $e) {
    echo "API error: {$e->getMessage()}\n";
}
