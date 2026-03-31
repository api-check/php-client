<?php

/**
 * Example: Phone number verification
 *
 * The Verify API can validate phone numbers, extract country/area codes,
 * determine number type (mobile/landline), and format the number.
 */

use ApiCheck\Api\ApiClient;
use ApiCheck\Api\Exceptions\ApiException;

require __DIR__ . "/../../vendor/autoload.php";

// Initialize the client
$client = new ApiClient();
$client->setApiKey("8weX4nJq29fHBDrQFlOoYVcPEbWTIxCp");

try {
    // Example 1: Verify a Dutch mobile number
    echo "=== Example 1: Dutch mobile number ===\n";
    $result = $client->verifyPhone('+31612345678');

    echo "Valid: " . ($result->valid ? 'Yes' : 'No') . "\n";
    if ($result->valid && isset($result->details)) {
        $details = $result->details;
        echo "Country code: +{$details->country_code}\n";
        echo "Area code: {$details->area_code}\n";
        echo "Number type: {$details->number_type}\n";
        echo "International format: {$details->international_formatted}\n";
        echo "National format: " . ($details->national_formatted ?? 'N/A') . "\n";
    }

    // Example 2: Verify a landline number
    echo "\n=== Example 2: Dutch landline ===\n";
    $result = $client->verifyPhone('+31201234567');

    echo "Valid: " . ($result->valid ? 'Yes' : 'No') . "\n";
    if ($result->valid && isset($result->details)) {
        echo "Number type: {$result->details->number_type}\n";
        echo "International format: {$result->details->international_formatted}\n";
    }

    // Example 3: Invalid number
    echo "\n=== Example 3: Invalid number ===\n";
    $result = $client->verifyPhone('+31123456789');

    echo "Valid: " . ($result->valid ? 'Yes' : 'No') . "\n";
    if (!$result->valid) {
        echo "This number is not valid for the given country code.\n";
    }

    // Example 4: Different country codes
    echo "\n=== Example 4: International numbers ===\n";
    $numbers = [
        '+31612345678',  // Netherlands mobile
        '+3221234567',   // Belgium
        '+491712345678', // Germany
    ];

    foreach ($numbers as $number) {
        try {
            $result = $client->verifyPhone($number);
            if ($result->valid && isset($result->details)) {
                echo "[OK] {$number}\n";
                echo "   Type: {$result->details->number_type}, ";
                echo "Carrier: {$result->details->carrier_name}\n";
            } else {
                echo "[FAIL] {$number} - Invalid\n";
            }
        } catch (ApiException $e) {
            echo "[FAIL] {$number} - Invalid\n";
        }
    }
} catch (ApiException $e) {
    echo "API error: {$e->getMessage()}\n";
}
