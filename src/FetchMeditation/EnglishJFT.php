<?php

namespace FetchMeditation;

use FetchMeditation\Utilities\HttpUtility;

class EnglishJFT extends JFT
{
    public function fetch(): JFTEntry | string
    {

        try {
            $data = HttpUtility::httpGet('https://jft.na.org');
        } catch (\Exception $e) {
            try {
                $data = HttpUtility::httpGet('https://na.org/jftna/');
            } catch (\Exception $fallbackException) {
                return "Error fetching data from both na.org/jftna and jftna.org/jft. "
                    . "Primary error: {$e->getMessage()}";
            }
        }

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
