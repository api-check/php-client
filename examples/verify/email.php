<?php

/**
 * Example: Email address verification
 *
 * The Verify API can check if an email address is valid, detect
 * disposable email services, and identify greylisted addresses.
 */

use ApiCheck\Api\ApiClient;
use ApiCheck\Api\Exceptions\ApiException;

require __DIR__ . "/../../vendor/autoload.php";

// Initialize the client
$client = new ApiClient();
$client->setApiKey("8weX4nJq29fHBDrQFlOoYVcPEbWTIxCp");

try {
    // Example 1: Verify a valid email
    echo "=== Example 1: Valid email ===\n";
    $result = $client->verifyEmail('user@example.com');

    echo "Email: user@example.com\n";
    echo "Status: {$result->status}\n";
    echo "Disposable: " . ($result->disposable_email ? 'Yes' : 'No') . "\n";
    echo "Greylisted: " . ($result->greylisted ? 'Yes' : 'No') . "\n";

    // Example 2: Detect disposable email
    echo "\n=== Example 2: Disposable email ===\n";
    $result = $client->verifyEmail('user@tempmail.com');

    echo "Status: {$result->status}\n";
    $disposable = isset($result->disposable_email) && $result->disposable_email ? 'Yes' : 'No';
    echo "Disposable: {$disposable}\n";

    // Example 3: Greylisted email
    echo "\n=== Example 3: Greylisted email ===\n";
    try {
        $result = $client->verifyEmail('user@unknown-domain-12345.com');

        echo "Status: {$result->status}\n";
        $greylisted = isset($result->greylisted) && $result->greylisted ? 'Yes' : 'No';
        echo "Greylisted: {$greylisted}\n";
    } catch (ApiException $e) {
        echo "Note: Unknown domains may cause errors or return 'unknown' status\n";
    }

    // Example 4: Batch verification
    echo "\n=== Example 4: Batch verification ===\n";
    $emails = [
        'info@example.com',
        'test@guerrillamail.com',  // Known disposable email
        'admin@10minutemail.com',  // Known disposable email
    ];

    foreach ($emails as $email) {
        try {
            $result = $client->verifyEmail($email);
            $statusIcon = isset($result->status) && $result->status === 'valid' ? '[OK]' : '[FAIL]';
            $disposableIcon = isset($result->disposable_email) && $result->disposable_email ? '[DISPOSABLE]' : '';
            $status = $result->status ?? 'unknown';
            echo "{$statusIcon} {$email} - {$status} {$disposableIcon}\n";
        } catch (ApiException $e) {
            echo "[FAIL] {$email} - Error\n";
        }
    }
} catch (ApiException $e) {
    echo "API error: {$e->getMessage()}\n";
}
