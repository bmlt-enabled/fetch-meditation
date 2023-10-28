<?php

namespace FetchMeditation;

use FetchMeditation\Utilities\HttpUtility;

class JapaneseJFT extends JFT
{
    public function getLanguage(): JFTLanguage
    {
        return $this->settings->language;
    }

    public function fetch(): JFTEntry
    {
        $data = HttpUtility::httpGet('https://najapan.org/just_for_today/');
        $doc = new \DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="UTF-8">' . $data);
        libxml_clear_errors();
        libxml_use_internal_errors(false);
        $xpath = new \DOMXpath($doc);

        $result = [
            'date' => '',
            'quote' => '',
            'source' => '',
            'thought' => '',
            'content' => [],
            'title' => '',
            'page' => '',
            'copyright' => 'Copyright (c) ' . date("Y") . ', NA World Service, Inc. All Rights Reserved',
        ];

        // Extract the date and title
        $h1Element = $xpath->query('//h2')->item(0);
        if ($h1Element !== null) {
            $dateParts = explode("　", $h1Element->textContent);
            $result['date'] = trim($dateParts[0]);
            $result['title'] = trim(end($dateParts));
        }

        // Extract quote
        $p0Element = $xpath->query('//p')->item(0);
        if ($p0Element !== null) {
            $result['quote'] = trim($p0Element->textContent);
        }

        // Extract the source and page
        $p1Element = $xpath->query('//p')->item(1);
        if ($p1Element !== null) {
            $sourceParts = explode("　", $p1Element->textContent);
            $result['source'] = trim($sourceParts[0]);
            $result['page'] = count($sourceParts) > 0 ? trim(end($sourceParts)) : '';
        }

        $pTags = $doc->getElementsByTagName('p');
        $pTagCount = $pTags->length;

        // Extract the thought
        if ($pTagCount >= 2) {
            $thoughtPTag = $pTags->item($pTagCount - 2); // Get the second to last <p> tag
            $result['thought'] = trim($thoughtPTag->textContent);
        }

        // Extract the copyright
        $centerTags = $doc->getElementsByTagName('center');
        if ($centerTags->length >= 0) {
            $result['copyright'] = trim($centerTags->item(0)->textContent);
        }

        // Extract the content
        $rightAlignedP = $xpath->query('//p[@style="text-align:right"]')->item(0);
        if ($rightAlignedP) {
            $paragraphs = [];
            $nextNode = $rightAlignedP->nextSibling;
            while ($nextNode) {
                if ($nextNode->nodeName === 'p' && $nextNode->getElementsByTagName('b')->length > 0) {
                    break; // Stop when next <p> with <b> tag is found
                }
                $paragraphs[] .= trim($nextNode->textContent);
                $nextNode = $nextNode->nextSibling;
            }
            $result['content'] = array_values(array_filter($paragraphs));
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
