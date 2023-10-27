<?php

namespace FetchMeditation;

use FetchMeditation\Utilities\HttpUtility;

class EnglishSPAD extends SPAD
{
    public function fetch()
    {
        $data = $this->getData();
        $entry = new SPADEntry(
            $data['date'],
            $data['title'],
            $data['page'],
            $data['quote'],
            $data['source'],
            $data['content'],
            $data['thought'],
            $data['copyright']
        );
        return $entry;
    }
    private function getData(): array
    {
        libxml_use_internal_errors(true);
        $data = HttpUtility::httpGet('https://spadna.org');
        libxml_clear_errors();
        libxml_use_internal_errors(false);
        $doc = new \DOMDocument();
        $doc->loadHTML($data);
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
        $result["copyright"] = preg_replace('/\s+|\n/', ' ', $result["copyright"]);
        return $result;
    }
}
