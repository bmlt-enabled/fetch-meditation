<?php

namespace FetchMeditation;

use FetchMeditation\Utilities\HttpUtility;

class EnglishSPAD extends SPAD
{
    public function fetch(): SPADEntry | string
    {

        try {
            $data = HttpUtility::httpGet('https://spad.na.org');
        } catch (\Exception $e) {
            try {
                $data = HttpUtility::httpGet('https://na.org/spadna/');
            } catch (\Exception $fallbackException) {
                return "Error fetching data from both na.org/spadna and spadna.org. "
                    . "Primary error: {$e->getMessage()}";
            }
        }

        $doc = new \DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="UTF-8">' . $data);
        libxml_clear_errors();
        libxml_use_internal_errors(false);
        $spadKeys = ['date', 'title', 'page', 'quote', 'source', 'content', 'divider', 'thought', 'copyright'];
        $result = [];
        foreach ($doc->getElementsByTagName('td') as $i => $td) {
            $nodeValue = trim($td->nodeValue);
            if ($spadKeys[$i] === 'content') {
                $innerHTML = '';
                foreach ($td->childNodes as $child) {
                    $innerHTML .= $td->ownerDocument->saveHTML($child);
                }
                $result['content'] = array_map('trim', preg_split('/<br\s*\/?>/', $innerHTML, -1, PREG_SPLIT_NO_EMPTY));
            } elseif ($spadKeys[$i] !== 'divider') {
                $result[$spadKeys[$i]] = $nodeValue;
            }
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
