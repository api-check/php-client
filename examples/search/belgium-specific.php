<?php

/**
 * Example: Belgium-specific searches (localities and municipalities)
 *
 * Belgium has a unique administrative structure with localities
 * (deelgemeenten) and municipalities (gemeenten). This example shows
 * how to search for these administrative divisions.
 */

use ApiCheck\Api\ApiClient;
use ApiCheck\Api\Exceptions\ApiException;
use ApiCheck\Api\Exceptions\UnsupportedCountryException;

require __DIR__ . "/../../vendor/autoload.php";

// Initialize the client
$client = new ApiClient();
$client->setApiKey("8weX4nJq29fHBDrQFlOoYVcPEbWTIxCp");

try {
    // Example 1: Search for localities (deelgemeenten)
    echo "=== Searching for localities ===\n";
    $localities = $client->searchLocality('be', 'Saint', ['limit' => 5]);

    $count = isset($localities->Results) ? count($localities->Results) : 0;
    echo "Found {$count} localit(y/ies):\n";
    if (isset($localities->Results)) {
        foreach ($localities->Results as $locality) {
            $municipality = $locality->Municipality->name ?? 'Unknown';
            echo "  - {$locality->name} (in {$municipality})\n";
        }
    }

    // Example 2: Search for municipalities (gemeenten)
    echo "\n=== Searching for municipalities ===\n";
    $municipalities = $client->searchMunicipality('be', 'Brussel', ['limit' => 5]);

    $count = isset($municipalities->Results) ? count($municipalities->Results) : 0;
    echo "Found {$count} municipalit(y/ies):\n";
    if (isset($municipalities->Results)) {
        foreach ($municipalities->Results as $municipality) {
            echo "  - {$municipality->name}\n";
        }
    }

    // Example 3: Search within a specific municipality
    echo "\n=== Search for localities within Namur ===\n";
    $namur = $client->searchMunicipality('be', 'Namur');
    if (isset($namur->Results) && !empty($namur->Results)) {
        $municipalityId = $namur->Results[0]->municipality_id;

        $localities = $client->searchLocality('be', '', [
            'municipality_id' => $municipalityId,
            'limit' => 10
        ]);

        echo "Localities in Namur:\n";
        if (isset($localities->Results)) {
            foreach ($localities->Results as $locality) {
                echo "  - {$locality->name}\n";
            }
        }
    }
} catch (UnsupportedCountryException $e) {
    echo "Unsupported country: {$e->getMessage()}\n";
} catch (ApiException $e) {
    echo "API error: {$e->getMessage()}\n";
}
