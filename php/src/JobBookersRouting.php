<?php

namespace JobBookers\Routing;

/**
 * Job Bookers Routing API client.
 *
 * @example
 * $client = new JobBookersRouting(['api_key' => getenv('JBR_API_KEY')]);
 * $result = $client->route('NR21 8AB', 'PE37 7JL');
 * echo $result['duration_mins']; // 23
 */
class JobBookersRouting
{
    private const BASE_URL = 'https://api.jobbookers.co.uk/v1';

    private string $apiKey;
    private int $timeout;

    /**
     * @param array $options {
     *     @type string $api_key  Your Job Bookers Routing API key. Required.
     *     @type int    $timeout  Request timeout in seconds. Default 10.
     * }
     * @throws \InvalidArgumentException If api_key is not provided.
     */
    public function __construct(array $options = [])
    {
        if (empty($options['api_key'])) {
            throw new \InvalidArgumentException('api_key is required');
        }
        $this->apiKey  = $options['api_key'];
        $this->timeout = $options['timeout'] ?? 10;
    }

    /**
     * Get drive time and distance between two UK postcodes.
     *
     * @param string $fromPostcode Origin postcode. e.g. 'NR21 8AB'
     * @param string $toPostcode   Destination postcode. e.g. 'PE37 7JL'
     * @return array{duration_mins: int, distance_miles: float, from_postcode: string, to_postcode: string}
     * @throws AuthenticationException
     * @throws InvalidPostcodeException
     * @throws NotFoundException
     * @throws RateLimitException
     * @throws ServerException
     * @throws JobBookersException
     */
    public function route(string $fromPostcode, string $toPostcode): array
    {
        return $this->get('/route', ['from' => $fromPostcode, 'to' => $toPostcode]);
    }

    /**
     * Get latitude and longitude for a UK postcode.
     *
     * @param string $postcode UK postcode. e.g. 'NR21 8AB'
     * @return array{postcode: string, lat: float, lon: float}
     * @throws AuthenticationException
     * @throws InvalidPostcodeException
     * @throws NotFoundException
     * @throws RateLimitException
     * @throws ServerException
     * @throws JobBookersException
     */
    public function geocode(string $postcode): array
    {
        return $this->get('/geocode', ['postcode' => $postcode]);
    }

    /**
     * Get the street name for a UK postcode.
     *
     * @param string $postcode UK postcode. e.g. 'NR21 8AB'
     * @return array{postcode: string, street: string}
     * @throws AuthenticationException
     * @throws InvalidPostcodeException
     * @throws NotFoundException
     * @throws RateLimitException
     * @throws ServerException
     * @throws JobBookersException
     */
    public function street(string $postcode): array
    {
        return $this->get('/street', ['postcode' => $postcode]);
    }

    /**
     * Make a GET request to the API.
     *
     * @param string $endpoint
     * @param array  $params
     * @return array
     * @throws JobBookersException
     */
    private function get(string $endpoint, array $params): array
    {
        $url = self::BASE_URL . $endpoint . '?' . http_build_query($params);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $this->apiKey,
                'Accept: application/json',
                'User-Agent: jobbookers-routing-php/1.0.0',
            ],
            CURLOPT_TIMEOUT        => $this->timeout,
            CURLOPT_FAILONERROR    => false,
        ]);

        $response   = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError  = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            throw new ServerException('Connection error: ' . $curlError, 'connection_error');
        }

        $body = json_decode($response, true) ?? [];

        if ($statusCode >= 200 && $statusCode < 300) {
            return $body;
        }

        $error   = $body['error'] ?? [];
        $code    = $error['code']    ?? 'unknown';
        $message = $error['message'] ?? 'HTTP ' . $statusCode;

        switch ($statusCode) {
            case 401:
                throw new AuthenticationException($message, $code, $statusCode);
            case 400:
                throw new InvalidPostcodeException($message, $code, $statusCode);
            case 404:
                throw new NotFoundException($message, $code, $statusCode);
            case 429:
                throw new RateLimitException($message, $code, $statusCode);
        }

        if ($statusCode >= 500) {
            throw new ServerException($message, $code, $statusCode);
        }

        throw new JobBookersException($message, $code, $statusCode);
    }
}
