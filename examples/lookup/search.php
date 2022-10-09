<?php

use ApiCheck\Api\ApiClient;

require "./vendor/autoload.php";

$apicheckClient = new ApiClient();
$apicheckClient->setApiKey("YOUR_API_KEY");

try {
    // Lookup example for The Nethlerands
    $results = $apicheckClient->search('be', 'city', ['name' => 'Namur']);
    // Do something with the results
    var_dump($results);

    // Some of the exceptions tha can happen.
} catch (UnsupportedCountryException $e) {
    print("Country-code not supported: $e");
}
