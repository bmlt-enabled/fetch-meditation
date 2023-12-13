<?php

namespace FetchMeditation;

use FetchMeditation\Utilities\HttpUtility;

class RussianJFT extends JFT
{
    public function getLanguage(): JFTLanguage
    {
        return $this->settings->language;
    }

    public function fetch(): JFTEntry
    {
        $data = HttpUtility::httpGet(JFTLanguage::Russian->url());
        $doc = new \DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="UTF-8">' . $data);
        libxml_clear_errors();
        libxml_use_internal_errors(false);
        $jftKeys = ['date', 'title', 'quote', 'source', 'content', 'thought', 'page'];
        $result = [
            'date' => '',
            'quote' => '',
            'source' => '',
            'thought' => '',
            'content' => [],
            'title' => '',
            'page' => '',
            'copyright' => '',
        ];
        $tables = $doc->getElementsByTagName('table');
        if ($tables->length > 0) {
            $firstTable = $tables->item(0);
            foreach ($firstTable->getElementsByTagName('td') as $i => $td) {
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
