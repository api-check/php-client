<?php

/**
 * Example: Retrieve available number additions for an address
 *
 * Number additions (huisnummertoevoegingen) are used to distinguish
 * between different sub-addresses like apartments, floors, or units.
 *
 * For example: "Main street 12", "Main street 12a", "Main street 12-II"
 */

use ApiCheck\Api\ApiClient;
use ApiCheck\Api\Exceptions\ApiException;
use ApiCheck\Api\Exceptions\UnsupportedCountryException;

require __DIR__ . "/../../vendor/autoload.php";

// Initialize the client
$client = new ApiClient();
$client->setApiKey("8weX4nJq29fHBDrQFlOoYVcPEbWTIxCp");

try {
    // Get all possible number additions for a postal code + number
    $result = $client->getNumberAdditions('nl', '2513AA', '1');

    $count = isset($result->Results) ? count($result->Results) : 0;
    echo "Found {$count} number addition(s) for 2513AA nr. 1:\n\n";

    if (isset($result->Results) && !empty($result->Results)) {
        foreach ($result->Results as $item) {
            $addition = $item->numberAddition ?? '(empty)';
            echo "  - {$addition}\n";
        }
    } else {
        echo "  No additional number additions found.\n";
    }

    // Example output:
    // - (empty string, for the main address)
    // - a
    // - b
    // - I
    // - II
    // - etc.

} catch (UnsupportedCountryException $e) {
    echo "Unsupported country: {$e->getMessage()}\n";
} catch (ApiException $e) {
    echo "API error: {$e->getMessage()}\n";
}
