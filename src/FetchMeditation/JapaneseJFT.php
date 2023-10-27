<?php

namespace FetchMeditation;

use FetchMeditation\Utilities\HttpUtility;

class JapaneseJFT extends JFT
{
    public function fetch()
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
        $data = HttpUtility::httpGet('https://najapan.org/just_for_today/');
        $doc = new \DOMDocument();
        $doc->loadHTML('<?xml encoding="UTF-8">' . $data);
        libxml_clear_errors();
        libxml_use_internal_errors(false);
        $xpath = new \DOMXpath($doc);

        $content = [
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
            $content['date'] = trim($dateParts[0]);
            $content['title'] = trim(end($dateParts));
        }

        // Extract quote
        $p0Element = $xpath->query('//p')->item(0);
        if ($p0Element !== null) {
            $content['quote'] = trim($p0Element->textContent);
        }

        // Extract the source and page
        $p1Element = $xpath->query('//p')->item(1);
        if ($p1Element !== null) {
            $sourceParts = explode("　", $p1Element->textContent);
            $content['source'] = trim($sourceParts[0]);
            $content['page'] = count($sourceParts) > 0 ? trim(end($sourceParts)) : '';
        }

        $pTags = $doc->getElementsByTagName('p');
        $pTagCount = $pTags->length;

        // Extract the thought
        if ($pTagCount >= 2) {
            $thoughtPTag = $pTags->item($pTagCount - 2); // Get the second to last <p> tag
            $content['thought'] = trim($thoughtPTag->textContent);
        }

        // Extract the copyright
        $centerTags = $doc->getElementsByTagName('center');
        if ($centerTags->length >= 0) {
            $content['copyright'] = trim($centerTags->item(0)->textContent);
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
            $content['content'] = array_values(array_filter($paragraphs));
        }
        return $content;
    }
}
