<?php

namespace HanzoAlpha\LaravelTripay\Requests;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use InvalidArgumentException;
use League\Config\Exception\InvalidConfigurationException;

class TripayClient
{
    const HTTP_GET = 'GET';

    const HTTP_POST = 'POST';

    protected string $sandboxURL = 'https://tripay.co.id/api-sandbox/';

    protected string $productionURL = 'https://tripay.co.id/api/';

    protected Client $client;

    public function __construct(?string $apiKey = null)
    {
        $apiKey = $apiKey ?? config('tripay.tripay_api_key');

        if (is_null($apiKey)) {
            throw new InvalidConfigurationException('API_KEY belum dikonfigurasi');
        }

        $this->client = new Client([
            'base_uri' => config('tripay.tripay_api_production') ?
                $this->productionURL :
                $this->sandboxURL,
            'headers' => [
                'Authorization' => 'Bearer '.$apiKey,
            ],
        ]);
    }

    /**
     * @throws Exception|\GuzzleHttp\Exception\GuzzleException
     */
    public function sendRequest(string $method, string $endpoint, array $data): string
    {
        if ($method == self::HTTP_GET) {
            return $this->sendGetRequest($endpoint, $data);
        }

        if ($method == self::HTTP_POST) {
            return $this->sendPostRequest($endpoint, $data);
        }

        throw new InvalidArgumentException(sprintf('http method %s tidak didukung.', $method));
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Exception
     */
    protected function sendGetRequest(string $endpoint, array $data): string
    {
        try {
            $result = $this->client->get($endpoint, [
                'query' => $data,
            ]);

            return $result->getBody()->getContents();
        } catch (ClientException $th) {
            throw new Exception($th->getResponse()->getBody()->getContents());
        }
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Exception
     */
    protected function sendPostRequest(string $endpoint, array $data): string
    {
        try {
            $result = $this->client->post($endpoint, [
                'form_params' => $data,
            ]);

            return $result->getBody()->getContents();
        } catch (ClientException $th) {
            throw new Exception($th->getResponse()->getBody()->getContents());
        }
    }
}
