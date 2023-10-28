<?php

namespace FetchMeditation;

use FetchMeditation\Utilities\HttpUtility;

class FrenchJFT extends JFT
{
    public function getLanguage(): JFTLanguage
    {
        return $this->settings->language;
    }

    public function fetch(): JFTEntry
    {
        $data = HttpUtility::httpGet('https://jpa.narcotiquesanonymes.org/');
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

        // Extract the title
        $titleElement = $xpath->query('//div[@class="cartouche"]/h1');
        if ($titleElement->length > 0) {
            $result['title'] = trim($titleElement->item(0)->textContent);
        }

        // Extract the date
        $dateElement = $xpath->query('//p[@class="info-publi"]');
        if ($dateElement->length > 0) {
            $result['date'] = trim($dateElement->item(0)->textContent);
        }

        // Extract the quote and source
        $quoteDiv = $xpath->query('//div[@class="chapo"]')->item(0);
        $quotePTags = $quoteDiv->getElementsByTagName('p');
        if ($quotePTags->length >= 2) {
            $result['quote'] = trim($quotePTags->item(0)->textContent);
            $result['source'] = trim($quotePTags->item(1)->textContent);
        }

        // Extract the thought
        $thoughtElement = $xpath->query('//h3[@class="h3 spip"]');
        if ($thoughtElement->length > 0) {
            $result['thought'] = trim($thoughtElement->item(0)->textContent);
        }

        // Extract the content
        $textDiv = $xpath->query('//div[@class="texte"]');
        if ($textDiv->length > 0) {
            $result['content'] = [];
            $pTags = $textDiv->item(0)->getElementsByTagName('p');
            foreach ($pTags as $pTag) {
                $result['content'][] = trim($pTag->textContent);
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
