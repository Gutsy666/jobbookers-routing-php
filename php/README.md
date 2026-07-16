# Job Bookers Routing — PHP Client

PHP client library for the [Job Bookers Routing API](https://api.jobbookers.co.uk) — UK drive time, distance, and postcode lookup.

## Installation

```bash
composer require jobbookers/routing
```

## Quick Start

```php
<?php
require 'vendor/autoload.php';

use JobBookers\Routing\JobBookersRouting;

$client = new JobBookersRouting(['api_key' => getenv('JBR_API_KEY')]);

// Drive time and distance
$result = $client->route('NR21 8AB', 'PE37 7JL');
echo "{$result['duration_mins']} mins, {$result['distance_miles']} miles";
// 23 mins, 16.2 miles

// Postcode coordinates
$coords = $client->geocode('NR21 8AB');
echo "Lat: {$coords['lat']}, Lon: {$coords['lon']}";
// Lat: 52.83162, Lon: 0.87049

// Street name
$street = $client->street('NR21 8AB');
echo $street['street'];
// Walnut Grove
```

## API Key

Get an API key at [api.jobbookers.co.uk](https://api.jobbookers.co.uk/#pricing).

**Always keep your API key server-side.** Never include it in client-side code or commit it to version control. Use environment variables.

## Methods

### `route(string $fromPostcode, string $toPostcode): array`

Returns drive time and distance between two UK postcodes.

```php
$result = $client->route('NR21 8AB', 'PE37 7JL');
// [
//   'duration_mins'  => 23,
//   'distance_miles' => 16.2,
//   'from_postcode'  => 'NR21 8AB',
//   'to_postcode'    => 'PE37 7JL',
// ]
```

### `geocode(string $postcode): array`

Returns latitude and longitude for a UK postcode.

```php
$result = $client->geocode('NR21 8AB');
// ['postcode' => 'NR21 8AB', 'lat' => 52.83162, 'lon' => 0.87049]
```

### `street(string $postcode): array`

Returns the street name for a UK postcode.

```php
$result = $client->street('NR21 8AB');
// ['postcode' => 'NR21 8AB', 'street' => 'Walnut Grove']
```

## Error Handling

```php
use JobBookers\Routing\JobBookersRouting;
use JobBookers\Routing\AuthenticationException;
use JobBookers\Routing\InvalidPostcodeException;
use JobBookers\Routing\NotFoundException;
use JobBookers\Routing\RateLimitException;
use JobBookers\Routing\ServerException;

try {
    $result = $client->route('NR21 8AB', 'PE37 7JL');
} catch (AuthenticationException $e) {
    echo 'Invalid API key';
} catch (InvalidPostcodeException $e) {
    echo 'Bad postcode: ' . $e->getMessage();
} catch (NotFoundException $e) {
    echo 'Postcode not found: ' . $e->getMessage();
} catch (RateLimitException $e) {
    echo 'Monthly limit reached — upgrade your plan';
} catch (ServerException $e) {
    echo 'API error: ' . $e->getMessage();
}
```

## Requirements

- PHP 7.4+
- ext-curl
- ext-json

## Links

- [API Documentation](https://api.jobbookers.co.uk/docs)
- [Pricing](https://api.jobbookers.co.uk/#pricing)
- [Support](mailto:hello@jobbookers.co.uk)

## Attribution

Routing data © OpenStreetMap contributors, available under the Open Database Licence.
Contains public sector information licensed under the Open Government Licence v3.0.

## Licence

MIT
