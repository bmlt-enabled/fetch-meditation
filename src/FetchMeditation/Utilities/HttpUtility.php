<?php

namespace FetchMeditation\Utilities;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\GuzzleException;

class HttpUtility
{
    const HEADERS = [
        'headers' => [
            'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36'
                . ' (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36',
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
            'Accept-Language' => 'en-US,en;q=0.9,es;q=0.8',
            'Upgrade-Insecure-Requests' => '1',
        ],
        'timeout' => 30
    ];

    const ACCEPTABLE_STATUS_CODES = [200, 302, 304];

    /**
     * Status codes worth a second try. Some sources (fzla.org) sit behind a bot
     * challenge that 403s the first request on a cold connection, then lets the
     * retry through once its cookie is presented back.
     */
    const RETRYABLE_STATUS_CODES = [403, 429, 500, 502, 503, 504];

    const MAX_ATTEMPTS = 3;

    const RETRY_DELAY_MICROSECONDS = 400000;

    public static function httpGet(string $url, array $params = []): string
    {
        if (!empty($params)) {
            $url = self::appendQueryParams($url, $params);
        }

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
        $args = self::HEADERS;
        $lastError = 'Error fetching data from server.';

        for ($attempt = 1; $attempt <= self::MAX_ATTEMPTS; $attempt++) {
            $response = wp_remote_get($url, $args);

            if (is_wp_error($response)) {
                $lastError = 'Error fetching data from server: ' . $response->get_error_message();
                break;
            }

            $httpcode = wp_remote_retrieve_response_code($response);
            if (in_array($httpcode, self::ACCEPTABLE_STATUS_CODES)) {
                $data = wp_remote_retrieve_body($response);
                if (empty($data)) {
                    throw new \Exception('Error: Received empty data from server.');
                }
                return $data;
            }

            $lastError = 'Received non-acceptable status code: ' . $httpcode;
            if (!in_array($httpcode, self::RETRYABLE_STATUS_CODES) || $attempt === self::MAX_ATTEMPTS) {
                break;
            }

            // hand back whatever the challenge handed us, then try again
            $args['cookies'] = wp_remote_retrieve_cookies($response);
            usleep(self::RETRY_DELAY_MICROSECONDS);
        }

        throw new \Exception($lastError);
    }

    /**
     * @throws GuzzleException
     * @throws \Exception
     */
    public static function guzzleGet(string $url): string
    {
        // one client for all attempts so the connection and its cookies are reused
        $jar = new CookieJar();
        $client = new Client(['cookies' => $jar]);
        $options = self::HEADERS + ['cookies' => $jar, 'http_errors' => false];
        $statusCode = 0;

        for ($attempt = 1; $attempt <= self::MAX_ATTEMPTS; $attempt++) {
            $response = $client->get($url, $options);
            $statusCode = $response->getStatusCode();

            if (in_array($statusCode, self::ACCEPTABLE_STATUS_CODES)) {
                return $response->getBody()->getContents();
            }

            if (!in_array($statusCode, self::RETRYABLE_STATUS_CODES) || $attempt === self::MAX_ATTEMPTS) {
                break;
            }

            usleep(self::RETRY_DELAY_MICROSECONDS);
        }

        throw new \Exception('Received non-acceptable status code: ' . $statusCode);
    }

    /**
     * Append query parameters to a URL
     *
     * @param string $url The base URL
     * @param array $params Associative array of parameters to append
     * @return string URL with query parameters appended
     */
    private static function appendQueryParams(string $url, array $params): string
    {
        $queryString = http_build_query($params);
        $separator = parse_url($url, PHP_URL_QUERY) ? '&' : '?';
        return $url . $separator . $queryString;
    }
}
