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

        try {
            $data = $this->fetchFromUrl('https://jft.na.org', $params);
        } catch (\Exception $e) {
            try {
                $data = $this->fetchFromUrl('https://na.org/jftna/', $params);
            } catch (\Exception $fallbackException) {
                return "Error fetching data from both na.org/jftna and jftna.org/jft. "
                    . "Primary error: {$e->getMessage()}";
            }
        }

        return $this->parseData($data);
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
     */
    protected function parseData(string $data): JFTEntry
    {
        $doc = new \DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="UTF-8">' . $data);
        libxml_clear_errors();
        libxml_use_internal_errors(false);
        $jftKeys = ['date', 'title', 'page', 'quote', 'source', 'content', 'thought', 'copyright'];
        $result = [];
        foreach ($doc->getElementsByTagName('td') as $i => $td) {
            if ($jftKeys[$i] === 'content') {
                $innerHTML = '';
                foreach ($td->childNodes as $child) {
                    $innerHTML .= $td->ownerDocument->saveHTML($child);
                }
                $result['content'] = preg_split('/<br\s*\/?>/', trim($innerHTML), -1, PREG_SPLIT_NO_EMPTY);
            } else {
                $result[$jftKeys[$i]] = trim($td->nodeValue);
            }
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
