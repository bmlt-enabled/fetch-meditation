<?php

namespace FetchMeditation;

use FetchMeditation\Utilities\HttpUtility;

class EnglishJFT extends JFT
{
    public function fetch(): JFTEntry
    {
        $data = $this->getData();
        $entry = new JFTEntry(
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

    public function getLanguage(): JFTLanguage
    {
        return $this->settings->language;
    }

    private function getData(): array
    {
        libxml_use_internal_errors(true);
        $data = HttpUtility::httpGet('https://www.jftna.org/jft/');
        libxml_clear_errors();
        libxml_use_internal_errors(false);
        $doc = new \DOMDocument();
        $doc->loadHTML('<?xml encoding="UTF-8">' .  $data);
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
        $result["copyright"] = preg_replace('/\s+/', ' ', str_replace("\n", "", $result["copyright"]));
        return $result;
    }
}
