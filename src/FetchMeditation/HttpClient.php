<?php

namespace FetchMeditation;

class HttpClient
{
    public function get(string $url, bool $decodeJson = false): string|array
    {
        $ch = curl_init($url);

        // Set cURL options.
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $headers[] = 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:105.0) Gecko/20100101 Firefox/105.0';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);

        if ($response === false) {
            // Handle cURL error.
            $error = curl_error($ch);
            curl_close($ch);
            return ['error' => $error];
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 && $httpCode !== 302 && $httpCode !== 304) {
            // Handle non acceptable HTTP responses.
            return ['error' => "HTTP Error: $httpCode"];
        }

        if ($decodeJson) {
            // Assuming the response is JSON.
            $data = json_decode($response, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                // Handle JSON decoding error.
                return ['error' => 'JSON Decoding Error.'];
            }

            return $data;
        }

        return $response;
    }
}
