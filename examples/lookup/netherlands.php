<?php

use ApiCheck\Api\ApiClient;
use ApiCheck\Api\Exceptions\NotFoundException;
use ApiCheck\Api\Exceptions\ValidationException;
use ApiCheck\Api\Exceptions\UnsupportedCountryException;

require "./vendor/autoload.php";

$apicheckClient = new ApiClient();
$apicheckClient->setApiKey("YOUR_API_KEY");

try {
    // Lookup example for The Nethlerands
    $address = $apicheckClient->lookup('nl', ['postalcode' => '2513AA', 'number' => 1]);
    print("🥳 Yay! We have a result: \n $address->street $address->number \n $address->postalcode $address->city \n {$address->Country->name}");

    // Some of the exceptions that can happen.
} catch (NotFoundException $e) {
    print("No results found: $e");
} catch (ValidationException $e) {
    print("Field validation error: $e");
} catch (UnsupportedCountryException $e) {
    print("Country-code not supported: $e");
}