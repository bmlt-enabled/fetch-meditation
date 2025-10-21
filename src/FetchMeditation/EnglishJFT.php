<?php

namespace FetchMeditation;

use FetchMeditation\Utilities\HttpUtility;

class EnglishJFT extends JFT
{
    public function fetch(): JFTEntry | string
    {
        $params = [];
        if ($this->settings->timeZone !== null) {
            $params['timeZone'] = $this->settings->timeZone;
        }

        $urls = ['https://jft.na.org', 'https://na.org/jftna/'];
        $lastFetchError = null;
        $lastParseError = null;

        foreach ($urls as $url) {
            try {
                $data = $this->fetchFromUrl($url, $params);

                // Try to parse the data
                try {
                    return $this->parseData($data);
                } catch (\Exception $parseException) {
                    $lastParseError = $parseException;
                    // Continue to next URL if parsing fails
                    continue;
                }
            } catch (\Exception $fetchException) {
                $lastFetchError = $fetchException;
                // Continue to next URL if fetch fails
                continue;
            }
        }

        // If we get here, all URLs failed
        return json_encode([
            'error' => 'all_sources_failed',
            'message' => 'Failed to fetch or parse meditation data from all available sources.',
            'fetch_error' => $lastFetchError ? $lastFetchError->getMessage() : null,
            'parse_error' => $lastParseError ? $lastParseError->getMessage() : null,
            'attempted_urls' => $urls
        ]);
    }

    /**
     * Fetch data from the given URL with parameters
     *
     * @param string $url The URL to fetch from
     * @param array $params The parameters to pass
     * @return string The fetched data
     * @throws \Exception If there's an error fetching the data
     */
    protected function fetchFromUrl(string $url, array $params = []): string
    {
        return HttpUtility::httpGet($url, $params);
    }

    /**
     * Parse the fetched data into a JFTEntry
     *
     * @param string $data The data to parse
     * @return JFTEntry The parsed entry
     * @throws \Exception If parsing fails
     */
    protected function parseData(string $data): JFTEntry
    {
        if (empty(trim($data))) {
            throw new \Exception('Empty or whitespace-only data received from server');
        }

        $doc = new \DOMDocument();
        libxml_use_internal_errors(true);
        $success = $doc->loadHTML('<?xml encoding="UTF-8">' . $data);
        $errors = libxml_get_errors();
        libxml_clear_errors();
        libxml_use_internal_errors(false);

        if (!$success) {
            throw new \Exception('Failed to parse HTML content');
        }

        $tdElements = $doc->getElementsByTagName('td');
        if ($tdElements->length === 0) {
            throw new \Exception('No table data elements found in response - unexpected HTML structure');
        }

        $jftKeys = ['date', 'title', 'page', 'quote', 'source', 'content', 'thought', 'copyright'];
        $requiredKeys = ['date', 'title', 'page', 'quote', 'source', 'content', 'thought'];
        $result = [];

        foreach ($tdElements as $i => $td) {
            if ($i >= count($jftKeys)) {
                break; // More elements than expected, ignore extras
            }

            if ($jftKeys[$i] === 'content') {
                $innerHTML = '';
                foreach ($td->childNodes as $child) {
                    $innerHTML .= $td->ownerDocument->saveHTML($child);
                }
                $content = preg_split('/<br\s*\/?>/', trim($innerHTML), -1, PREG_SPLIT_NO_EMPTY);
                $result['content'] = array_filter(array_map('trim', $content), fn($item) => !empty($item));
            } else {
                $result[$jftKeys[$i]] = trim($td->nodeValue);
            }
        }

        // Validate that all required keys are present
        foreach ($requiredKeys as $key) {
            if (!array_key_exists($key, $result)) {
                throw new \Exception("Missing required field: {$key}. Found keys: " . implode(', ', array_keys($result)));
            }
        }

        // Validate content array is not empty
        if (empty($result['content'])) {
            throw new \Exception('Content field is empty or contains no valid paragraphs');
        }

        // If 'copyright' isn't set, supply a default value
        if (!array_key_exists('copyright', $result)) {
            $result['copyright'] = "Copyright (c) 2007-" . date("Y") . ", NA World Services, Inc. All Rights Reserved";
        } else {
            // If it exists, you may want to sanitize/clean it as you already do
            $result["copyright"] = preg_replace('/\s+|\n/', ' ', $result["copyright"]);
        }

        return new JFTEntry(
            $result['date'],
            $result['title'],
            $result['page'],
            $result['quote'],
            $result['source'],
            $result['content'],
            $result['thought'],
            $result['copyright']
        );
    }
}
