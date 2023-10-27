<?php

namespace FetchMeditation\Utilities;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class HttpUtility
{
    /**
     * @throws GuzzleException
     * @throws \Exception
     */
    public static function httpGet(string $url): string
    {
        $client = new Client();
        $options = [
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:105.0) Gecko/20100101 Firefox/105.0',
            ],
        ];

        $response = $client->get($url, $options);

        if (in_array($response->getStatusCode(), [200, 302, 304])) {
            return $response->getBody()->getContents();
        } else {
            throw new \Exception('Received non-acceptable status code: ' . $response->getStatusCode());
        }
    }
}
