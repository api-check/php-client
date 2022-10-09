# ApiCheck PHP Client

This is a PHP client for ApiCheck endpoints.
ApiCheck helps you validate customer data.

Currently supported countries: The Netherlands, Belgium, and Luxembourg.

## Requirements ##
- Register an account at [ApiCheck Dashboard](https://app.apicheck.nl/authentication/register) and select the appropriate subscription type.
- Create a new API key.

## Installation

Use composer to install this package

```bash
$ composer require api-check/php-client:^1.0
```

```json
{
  "require": {
    "api-check/php-client": "^1.0"
  }
}
```

# Examples
The first step is to set your API key (optionally also load autoload.php):

## Use the Lookup API
The lookup API is currently only supported for NL and LU. See:
[Lookup API documentation](https://apicheck.nl/documentation/lookup-api/)
```php
use ApiCheck\Api\ApiClient;

require "./vendor/autoload.php";

$apicheckClient = new ApiClient();
$apicheckClient->setApiKey("YOUR_API_KEY");
```

Use the Lookup API
```php
$address = $apicheckClient->lookup('nl', ['postalcode' => '2513AA', 'number' => 1]);
// Do something with the result:
print("🥳 Yay! We have a result: \n $address->street $address->number \n $address->postalcode $address->city \n {$address->Country->name}");
```
## Use the Search API
The search API is currently supported for NL, BE, LU and FR. See:
[Search API documentation](https://apicheck.nl/documentation/normalised-search-api/)
```php
$results = $apicheckClient->search('be', 'city', ['name' => 'Namur']);
```
This will return an array with the search results



## Exceptions
The ApiCheck client uses custom Exceptions to handle failure responses:
```php
use ApiCheck\Api\ApiClient;
use ApiCheck\Api\Exceptions\NotFoundException;
use ApiCheck\Api\Exceptions\ValidationException;
use ApiCheck\Api\Exceptions\UnsupportedCountryException;

try {
    $address = $apicheckClient->lookup('nl', ['postalcode' => '2513AA', 'number' => 1]);
} catch (NotFoundException $e) {
    // No results have been found using the supplied data
} catch (ValidationException $e) {
    // One of the submitted fields is not valid or set
} catch (UnsupportedCountryException $e) {
    // The given country-code is not supported
}
```
More examples can be found in the examples directory.

## Contributing
Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

Please make sure to update tests as appropriate.

## License
[MIT](https://choosealicense.com/licenses/mit/)

## Support
Contact: [www.apicheck.nl](https://www.apicheck.nl) — support@apicheck.nl