# ApiCheck PHP Client - Examples

This directory contains examples demonstrating how to use the ApiCheck PHP client v2.0.

## Prerequisites

Before running any example:
1. Install dependencies: `composer install`
2. Replace `YOUR_API_KEY` with your actual ApiCheck API key
3. Run from the project root or adjust the `require` path accordingly

## Examples by API

### Lookup API ([examples/lookup/](./lookup/))

The Lookup API validates addresses by postal code and house number.

| Example                                               | Description                                          |
| ----------------------------------------------------- | ---------------------------------------------------- |
| [netherlands.php](./lookup/netherlands.php)           | Basic Dutch address lookup                           |
| [luxembourg.php](./lookup/luxembourg.php)             | Basic Luxembourgish address lookup                   |
| [number-additions.php](./lookup/number-additions.php) | Retrieve available number additions for an address   |
| [with-options.php](./lookup/with-options.php)         | Advanced lookup with fields, shortening, and aliases |

### Search API ([examples/search/](./search/))

The Search API finds cities, streets, postal codes, and addresses across 18 European countries.

| Example                                                     | Description                                  |
| ----------------------------------------------------------- | -------------------------------------------- |
| [basic-search.php](./search/basic-search.php)               | Search for cities, streets, and postal codes |
| [global-search.php](./search/global-search.php)             | Global search across all address types       |
| [belgium-specific.php](./search/belgium-specific.php)       | Search Belgian localities and municipalities |
| [address-resolution.php](./search/address-resolution.php)   | Resolve complete addresses using IDs         |
| [supported-countries.php](./search/supported-countries.php) | Get list of supported countries              |

### Verify API ([examples/verify/](./verify/))

The Verify API validates email addresses and phone numbers.

| Example                         | Description                                           |
| ------------------------------- | ----------------------------------------------------- |
| [email.php](./verify/email.php) | Email verification with disposable/greylist detection |
| [phone.php](./verify/phone.php) | Phone number validation and formatting                |

## Running the Examples

From the project root:

```bash
# Run a specific example
php examples/lookup/netherlands.php

# Run all lookup examples
php examples/lookup/*.php
```

## Common Patterns

### Error Handling

All examples include proper error handling:

```php
use ApiCheck\Api\Exceptions\ApiException;
use ApiCheck\Api\Exceptions\NotFoundException;
use ApiCheck\Api\Exceptions\ValidationException;
use ApiCheck\Api\Exceptions\UnsupportedCountryException;

try {
    $result = $client->lookup('nl', [...]);
} catch (NotFoundException $e) {
    // Address not found
} catch (ValidationException $e) {
    // Invalid input parameters
} catch (UnsupportedCountryException $e) {
    // Country not supported for this operation
} catch (ApiException $e) {
    // General API error
}
```

### Client Initialization

```php
$client = new ApiClient();
$client->setApiKey('YOUR_API_KEY');

// Optional: Set referer header for API keys with allowed hosts
$client->setReferer('https://your-website.com');
```

## API Reference

For complete API documentation, visit [https://apicheck.nl/docs](https://apicheck.nl/docs)

## Support

Need help? Contact us at [support@apicheck.nl](mailto:support@apicheck.nl)
