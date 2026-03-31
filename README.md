# ApiCheck PHP Client

A PHP client for the ApiCheck API. Validate addresses, search locations, and verify contact data with ease.

**Version:** 2.0.0

## Features

- **Lookup API** - Validate postal addresses (NL, LU)
- **Search API** - Search cities, streets, postal codes, and addresses across 18 European countries
- **Verify API** - Verify email addresses and phone numbers

## Requirements

- PHP 8.1 or higher
- Register an account at [ApiCheck Dashboard](https://app.apicheck.nl/authentication/register)
- Create an API key

## Installation

Install via Composer:

```bash
composer require api-check/php-client:^2.0
```

Or add to your `composer.json`:

```json
{
  "require": {
    "api-check/php-client": "^2.0"
  }
}
```

## Quick Start

```php
use ApiCheck\Api\ApiClient;

require "./vendor/autoload.php";

$client = new ApiClient();
$client->setApiKey("YOUR_API_KEY");

// Optionally set a referer if your API key has allowed hosts configured
$client->setReferer("https://your-domain.com");
```

## Lookup API

Look up addresses by postal code and house number. Currently supported for NL and LU.

[Lookup API documentation](https://apicheck.nl/documentation/lookup-api/)

### Basic Lookup

```php
$address = $client->lookup('nl', [
    'postalcode' => '2513AA',
    'number' => 1
]);

print("{$address->street} {$address->number}\n");
print("{$address->postalcode} {$address->city}\n");
print("{$address->Country->name}");
```

### Advanced Options

```php
$address = $client->lookup('nl', [
    'postalcode' => '2513AA',
    'number' => 1,
    'fields' => ['street', 'city', 'latitude', 'longitude'],  // Only return specific fields
    'aliasses' => true,    // Include subaddress relationships
    'shortening' => true   // Include streetShort field
]);
```

### Get Number Additions

Retrieve available number additions (like "A", "B", "1e") for a postal code:

```php
$additions = $client->getNumberAdditions('nl', '2513AA', '1');
// Returns: ["1", "1A", "1B", ...]
```

## Search API

Search for cities, streets, postal codes, and addresses across 18 European countries.

[Search API documentation](https://apicheck.nl/documentation/normalised-search-api/)

### Basic Search

```php
// Search cities
$results = $client->search('be', 'city', ['name' => 'Namur']);

// Search streets
$results = $client->search('nl', 'street', ['name' => 'Hoofd']);

// Search postal codes
$results = $client->search('fr', 'postalcode', ['name' => '75001']);
```

### Global Search

Search across all scopes at once:

```php
$results = $client->globalSearch('nl', 'Hoofdf', [
    'limit' => 10
]);
```

### Belgium-Specific Searches

Search localities (deelgemeenten) and municipalities (gemeenten):

```php
// Search localities
$localities = $client->searchLocality('be', 'Gontrode');

// Search municipalities
$municipalities = $client->searchMunicipality('be', 'Gent');
```

### Address Resolution

Resolve a full address using IDs from previous searches:

```php
$address = $client->searchAddress('be', [
    'street_id' => 12345,
    'number' => '10',
    'postalcode_id' => 67890
]);
```

### Get Supported Countries

Retrieve the live list of supported countries:

```php
$countries = $client->getSupportedSearchCountries();
```

## Verify API

Verify email addresses and phone numbers.

### Email Verification

```php
$result = $client->verifyEmail('user@example.com');

// Returns:
// - disposable_email: bool
// - greylisted: bool
// - status: "valid" | "invalid" | "unknown"

if ($result->status === 'valid' && !$result->disposable_email) {
    print("Email is valid and not disposable");
}
```

### Phone Number Verification

```php
$result = $client->verifyPhone('+31612345678');

// Returns:
// - valid: bool
// - country_code: string (e.g., "NL")
// - international_formatted: string
// - number_type: string (e.g., "mobile")
```

## Exception Handling

The client uses specific exceptions for different error scenarios:

```php
use ApiCheck\Api\ApiClient;
use ApiCheck\Api\Exceptions\NotFoundException;
use ApiCheck\Api\Exceptions\ValidationException;
use ApiCheck\Api\Exceptions\UnsupportedCountryException;
use ApiCheck\Api\Exceptions\UnauthorizedException;
use ApiCheck\Api\Exceptions\ApiKeyInvalidException;
use ApiCheck\Api\Exceptions\NoExactMatchException;

try {
    $address = $client->lookup('nl', ['postalcode' => '2513AA', 'number' => 1]);
} catch (NotFoundException $e) {
    // No results found
} catch (ValidationException $e) {
    // Invalid or missing fields
} catch (UnsupportedCountryException $e) {
    // Country not supported for this operation
} catch (UnauthorizedException $e) {
    // Invalid or missing API key
} catch (NoExactMatchException $e) {
    // No exact match found (Search API)
} catch (ApiException $e) {
    // General API error
}
```

### Available Exceptions

- `ApiException` - Base exception for all API errors
- `AccessDeniedException` - Access denied
- `ApiKeyExhaustedException` - API key quota exceeded
- `ApiKeyHeaderException` - API key header missing
- `ApiKeyInvalidException` - Invalid API key
- `BadRequestException` - Bad request (400)
- `HostNotAllowedException` - Host not allowed for this API key
- `InternalServerErrorException` - Server error (500)
- `NoExactMatchException` - No exact match found
- `NotFoundException` - Resource not found (404)
- `PageNotFoundException` - Page not found
- `UnauthorizedException` - Unauthorized (401)
- `UnprocessableEntityException` - Unprocessable entity (422)
- `UnsupportedCountryException` - Country not supported
- `ValidationException` - Validation failed

## Examples

More examples can be found in the [examples/](examples/) directory:

- [Lookup examples](examples/lookup/) - Basic lookup, number additions, options
- [Search examples](examples/search/) - Basic search, global search, Belgium-specific, address resolution
- [Verify examples](examples/verify/) - Email and phone verification

## Contributing

Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

Please make sure to update tests as appropriate.

```bash
composer test
```

## License

[MIT](https://choosealicense.com/licenses/mit/)

## Support

Contact: [www.apicheck.nl](https://www.apicheck.nl) — support@apicheck.nl