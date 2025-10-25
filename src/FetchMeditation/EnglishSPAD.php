<?php

namespace FetchMeditation;

use FetchMeditation\Utilities\HttpUtility;

class EnglishSPAD extends SPAD
{
    public function fetch(): SPADEntry | string
    {
        $params = [];
        if ($this->settings->timeZone !== null) {
            $params['timeZone'] = $this->settings->timeZone;
        }

        $urls = ['https://spad.na.org', 'https://na.org/spadna/'];
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
            'fetch_error' => $lastFetchError?->getMessage(),
            'parse_error' => $lastParseError?->getMessage(),
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
     * Parse the fetched data into a SPADEntry
     *
     * @param string $data The data to parse
     * @return SPADEntry The parsed entry
     * @throws \Exception If parsing fails
     */
    protected function parseData(string $data): SPADEntry
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

        $spadKeys = ['date', 'title', 'page', 'quote', 'source', 'content', 'divider', 'thought', 'copyright'];
        $requiredKeys = ['date', 'title', 'page', 'quote', 'source', 'content', 'thought'];
        $result = [];

        foreach ($tdElements as $i => $td) {
            if ($i >= count($spadKeys)) {
                break; // More elements than expected, ignore extras
            }

            $nodeValue = trim($td->nodeValue);
            if ($spadKeys[$i] === 'content') {
                $innerHTML = '';
                foreach ($td->childNodes as $child) {
                    $innerHTML .= $td->ownerDocument->saveHTML($child);
                }
                $content = array_map('trim', preg_split('/<br\s*\/?>/', $innerHTML, -1, PREG_SPLIT_NO_EMPTY));
                $result['content'] = array_filter($content, fn($item) => !empty($item));
            } elseif ($spadKeys[$i] !== 'divider') {
                $result[$spadKeys[$i]] = $nodeValue;
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

        return new SPADEntry(
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
