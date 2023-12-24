<?php

namespace FetchMeditation\Utilities;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class HttpUtility
{
    const HEADERS = [
        'headers' => [
            'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:105.0) Gecko/20100101 Firefox/105.0',
        ],
        'timeout' => 30
    ];

    public static function httpGet(string $url): string
    {
        if (defined('WPINC')) {
            return self::wordpressGet($url);
        } else {
            return self::guzzleGet($url);
        }
    }

    /**
     * @throws \Exception
     */
    public static function wordpressGet(string $url): string
    {
        $response = wp_remote_get($url, self::HEADERS);

        if (is_wp_error($response)) {
            throw new \Exception('Error fetching data from server: ' . $response->get_error_message());
        }

        $httpcode = wp_remote_retrieve_response_code($response);
        if (!in_array($httpcode, [200, 302, 304])) {
            throw new \Exception('Received non-acceptable status code: ' . $httpcode);
        }

        $data = wp_remote_retrieve_body($response);
        if (empty($data)) {
            throw new \Exception('Error: Received empty data from server.');
        }
        return $data;
    }

    /**
     * @throws GuzzleException
     * @throws \Exception
     */
    public static function guzzleGet(string $url): string
    {
        $client = new Client();
        $options = self::HEADERS;

        $response = $client->get($url, $options);

        if (in_array($response->getStatusCode(), [200, 302, 304])) {
            return $response->getBody()->getContents();
        } else {
            throw new \Exception('Received non-acceptable status code: ' . $response->getStatusCode());
        }
    }
}
